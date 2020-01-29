<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Com\ComShell;
use NextDom\Enums\CacheEngine;
use NextDom\Enums\CacheKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\NextDomFile;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Model\Entity\Cache;

/**
 * Class CacheManager
 * @package NextDom\Managers
 */
class CacheManager
{
    private static $cacheSystem = null;

    /**
     * Store object in cache
     *
     * @param string $key Key
     * @param mixed $value Value
     * @param int $lifetime Lifetime
     * @param mixed $options Options
     *
     * @return bool
     * @throws \Exception
     */
    public static function set($key, $value, $lifetime = 0, $options = null)
    {
        if ($lifetime < 0) {
            $lifetime = 0;
        }
        $cacheItem = new Cache();
        $cacheItem->setKey($key)
            ->setValue($value)
            ->setLifetime($lifetime);
        if ($options != null) {
            $cacheItem->setOptionsFromJson($options);
        }
        return $cacheItem->save();
    }

    /**
     * Get some stats about the cache system
     *
     * @param bool $details True for more informations
     *
     * @return array|null
     * @throws \Exception
     */
    public static function stats($details = false)
    {
        $result = self::getCache()->getStats();
        $result['count'] = __('Inconnu');
        $engine = ConfigManager::byKey(CacheKey::CACHE_ENGINE);
        if ($engine == CacheEngine::FILESYSTEM) {
            $result['count'] = 0;
            foreach (FileSystemHelper::ls(self::getFolder()) as $folder) {
                foreach (FileSystemHelper::ls(self::getFolder() . '/' . $folder) as $file) {
                    if (strpos($file, 'swap') !== false) {
                        continue;
                    }
                    $result['count']++;
                }
            }
        } elseif ($engine == CacheEngine::REDIS) {
            $result['count'] = self::$cacheSystem->getRedis()->dbSize();
        }
        if ($details) {
            $re = '/s:\d*:(.*?);s:\d*:"(.*?)";s/';
            $result = [];
            foreach (FileSystemHelper::ls(self::getFolder()) as $folder) {
                foreach (FileSystemHelper::ls(self::getFolder() . '/' . $folder) as $file) {
                    $path = self::getFolder() . '/' . $folder . '/' . $file;
                    $str = (string)str_replace("\n", '', file_get_contents($path));
                    preg_match_all($re, $str, $matches);
                    if (!isset($matches[2]) || !isset($matches[2][0]) || trim($matches[2][0]) == '') {
                        continue;
                    }
                    $result[] = $matches[2][0];
                }
            }
            $result['details'] = $result;
        }
        return $result;
    }

    /**
     * Get doctrine cache
     *
     * @param string $targetFolder Target folder
     *
     * @return \Doctrine\Common\Cache\FilesystemCache
     *
     * @throws \Exception
     */
    public static function getDoctrineCache(string $targetFolder = '') {
        if ($targetFolder === '') {
            $targetFolder = self::getFolder();
        }
        return new \Doctrine\Common\Cache\FilesystemCache($targetFolder);
    }

    /**
     * Get cache system
     *
     * @return \Doctrine\Common\Cache\FilesystemCache|\Doctrine\Common\Cache\MemcachedCache|\Doctrine\Common\Cache\RedisCache|null Cache system
     * @throws \Exception
     */
    public static function getCache()
    {
        if (self::$cacheSystem !== null) {
            return self::$cacheSystem;
        }
        $engine = ConfigManager::byKey(CacheKey::CACHE_ENGINE);
        if ($engine == CacheEngine::MEMCACHED && !class_exists('memcached')) {
            $engine = CacheEngine::FILESYSTEM;
            ConfigManager::save(CacheKey::CACHE_ENGINE, CacheEngine::FILESYSTEM);
        }
        if ($engine == CacheEngine::REDIS && !class_exists('redis')) {
            $engine = CacheEngine::FILESYSTEM;
            ConfigManager::save(CacheKey::CACHE_ENGINE, CacheEngine::FILESYSTEM);
        }
        switch ($engine) {
            case CacheEngine::FILESYSTEM:
            case CacheEngine::PHPFILE:
            default:
                self::$cacheSystem = self::getDoctrineCache();
                break;
            case CacheEngine::MEMCACHED:
                $memcached = new \Memcached();
                $memcached->addServer(ConfigManager::byKey('cache::memcacheaddr'), ConfigManager::byKey('cache::memcacheport'));
                self::$cacheSystem = new \Doctrine\Common\Cache\MemcachedCache();
                self::$cacheSystem->setMemcached($memcached);
                break;
            case CacheEngine::REDIS:
                $redis = new \Redis();
                $redis->connect(ConfigManager::byKey('cache::redisaddr'), ConfigManager::byKey('cache::redisport'));
                self::$cacheSystem = new \Doctrine\Common\Cache\RedisCache();
                self::$cacheSystem->setRedis($redis);
                break;
        }
        return self::$cacheSystem;
    }

    /**
     * Get the folder where the cache is stored
     *
     * @return string Cache folder
     * @throws \Exception
     */
    public static function getFolder(): string
    {
        $return = NextDomHelper::getTmpFolder('cache');
        if (!file_exists($return)) {
            mkdir($return, 0775);
        }
        return $return;
    }

    /**
     * Test if object exists
     *
     * @param string $key Key
     *
     * @return bool True if object exists
     *
     * @deprecated Use exists
     * @throws \Exception
     */
    public static function exist($key)
    {
        @trigger_error('This method is deprecated', E_USER_DEPRECATED);
        return self::exists($key);
    }

    /**
     * Test if object exists
     *
     * @param string $key Key
     *
     * @return bool True if object exists
     * @throws \Exception
     */
    public static function exists($key)
    {
        return is_object(self::getCache()->fetch($key));
    }

    /**
     * Clear cache
     */
    public static function flush()
    {
        self::getCache()->deleteAll();
        shell_exec('rm -rf ' . self::getFolder() . ' 2>&1 > /dev/null');
    }

    /**
     * @TODO: Ouahhh
     * @return array
     */
    public static function search(): array
    {
        return [];
    }

    /**
     * Persist cache system
     */
    public static function persist()
    {
        switch (ConfigManager::byKey(CacheKey::CACHE_ENGINE)) {
            case CacheEngine::FILESYSTEM:
                $cacheDir = self::getFolder();
                break;
            case CacheEngine::PHPFILE:
                $cacheDir = self::getFolder();
                break;
            default:
                return;
        }
        try {
            $cacheFile = self::getArchivePath();
            $rmCmd = sprintf("rm -rf %s", $cacheFile);
            $tarCmd = sprintf("cd %s; tar cfz %s *  2>&1 > /dev/null", $cacheDir, $cacheFile);
            $chmodCmd = sprintf("chmod 664 %s", $cacheFile);
            $chownCmd = sprintf("chown %s:%s %s", SystemHelper::getWWWUid(), SystemHelper::getWWWGid(), $cacheFile);

            ComShell::execute($rmCmd);
            ComShell::execute($tarCmd);
            ComShell::execute($chmodCmd);
            ComShell::execute($chownCmd);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return string
     */
    public static function getArchivePath()
    {
        return NEXTDOM_DATA . '/' . NextDomFile::CACHE_TAR_GZ;
    }

    /**
     * Test if cache already exists
     *
     * @return bool True if file cache.tar.gz
     * @throws \Exception
     */
    public static function isPersistOk(): bool
    {
        $cacheEngine = ConfigManager::byKey(CacheKey::CACHE_ENGINE);
        if ($cacheEngine == CacheEngine::PHPFILE || $cacheEngine == CacheEngine::FILESYSTEM) {
            $filename = self::getArchivePath();
            if (!file_exists($filename) || filemtime($filename) < strtotime('-35min')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Restore persisted cache
     */
    public static function restore()
    {
        $cacheDir = '';
        $cacheEngine = ConfigManager::byKey(CacheKey::CACHE_ENGINE);
        if ($cacheEngine == CacheEngine::PHPFILE || $cacheEngine == CacheEngine::FILESYSTEM) {
            $cacheDir = self::getFolder();
        }
        if (!file_exists(self::getArchivePath())) {
            return false;
        }

        SystemHelper::vsystem("rm -rf %s", $cacheDir);
        SystemHelper::vsystem("mkdir %s", $cacheDir);
        SystemHelper::vsystem("tar xzf %s -C %s", self::getArchivePath(), $cacheDir);
        return true;
    }

    /**
     * Remove old and unused item stored in cache
     *
     * @throws \Exception
     */
    public static function clean()
    {
        if (ConfigManager::byKey(CacheKey::CACHE_ENGINE) != 'FilesystemCache') {
            return;
        }
        $re = '/s:\d*:(.*?);s:\d*:"(.*?)";s/';
        $result = [];
        foreach (FileSystemHelper::ls(self::getFolder()) as $folder) {
            foreach (FileSystemHelper::ls(self::getFolder() . '/' . $folder) as $file) {
                $path = self::getFolder() . '/' . $folder . '/' . $file;
                if (strpos($file, 'swap') !== false) {
                    unlink($path);
                    continue;
                }
                $str = (string)str_replace("\n", '', file_get_contents($path));
                preg_match_all($re, $str, $matches);
                if (!isset($matches[2]) || !isset($matches[2][0]) || trim($matches[2][0]) == '') {
                    continue;
                }
                $result[] = $matches[2][0];
            }
        }
        $cleanCache = [
            'cmdCacheAttr' => NextDomObj::CMD,
            'cmd' => NextDomObj::CMD,
            'eqLogicCacheAttr' => NextDomObj::EQLOGIC,
            'eqLogicStatusAttr' => NextDomObj::EQLOGIC,
            'scenarioCacheAttr' => NextDomObj::SCENARIO,
            'cronCacheAttr' => NextDomObj::CRON,
            'cron' => NextDomObj::CRON,
        ];
        foreach ($result as $key) {
            $matches = null;
            if (preg_match_all('/(::lastCommunication|::state|::numberTryWithoutSuccess)/', $key) == 1) {
                self::delete($key);
                continue;
            }
            foreach ($cleanCache as $kClean => $value) {
                if (strpos($key, $kClean) !== false) {
                    $id = str_replace($kClean, '', $key);
                    if (!is_numeric($id)) {
                        continue;
                    }
                    $resultObject = $value::byId($id);
                    if (!is_object($resultObject)) {
                        self::delete($key);
                    }
                }
            }
            preg_match_all('/(?:widgetHtml|camera)(\d+)(.*?)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                $resultObject = EqLogicManager::byId($matches[1][0]);
                if (!is_object($resultObject)) {
                    self::delete($key);
                }
            }
            preg_match_all('/scenarioHtml(.*?)(\d*)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                $resultObject = ScenarioManager::byId($matches[1][0]);
                if (!is_object($resultObject)) {
                    self::delete($key);
                }
            }
            $withoutPrefix = preg_replace('/widgetHtmldashboard|widgetHtmldplan|widgetHtml|cmd/', '', $key);
            if (is_numeric($withoutPrefix)) {
                self::delete($key);
            }
            preg_match_all('/(?:dependancy|dependency)(.*)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                try {
                    $plugin = PluginManager::byId($matches[1][0]);
                    if (!is_object($plugin)) {
                        self::delete($key);
                    }
                } catch (\Exception $e) {
                    self::delete($key);
                }
            }
        }
    }

    /**
     * Delete stored object in cache
     *
     * @param $key
     * @throws \Exception
     */
    public static function delete($key)
    {
        $cacheItem = self::byKey($key);
        if (is_object($cacheItem)) {
            $cacheItem->remove();
        }
    }

    /**
     * Get stored object by key
     *
     * @param string $key Key
     * @return mixed Stored object or null if not exists
     * @throws \Exception
     */
    public static function byKey($key)
    {
        $cache = self::getCache()->fetch($key);
        if (!is_object($cache)) {
            $cache = new Cache();
            $cache->setKey($key)
                ->setDatetime(date(DateFormat::FULL));
        }
        return $cache;
    }
}
