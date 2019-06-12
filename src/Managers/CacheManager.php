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

use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Model\Entity\Cache;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

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
        $engine = ConfigManager::byKey('cache::engine');
        if ($engine == 'FilesystemCache') {
            $result['count'] = 0;
            foreach (FileSystemHelper::ls(self::getFolder()) as $folder) {
                foreach (FileSystemHelper::ls(self::getFolder() . '/' . $folder) as $file) {
                    if (strpos($file, 'swap') !== false) {
                        continue;
                    }
                    $result['count']++;
                }
            }
        } else if ($engine == 'RedisCache') {
            $result['count'] = self::$cacheSystem->getRedis()->dbSize();
        }
        if ($details) {
            $re = '/s:\d*:(.*?);s:\d*:"(.*?)";s/';
            $result = array();
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
        $engine = ConfigManager::byKey('cache::engine');
        if ($engine == 'MemcachedCache' && !class_exists('memcached')) {
            $engine = 'FilesystemCache';
            ConfigManager::save('cache::engine', 'FilesystemCache');
        }
        if ($engine == 'RedisCache' && !class_exists('redis')) {
            $engine = 'FilesystemCache';
            ConfigManager::save('cache::engine', 'FilesystemCache');
        }
        switch ($engine) {
            case 'FilesystemCache':
                self::$cacheSystem = new \Doctrine\Common\Cache\FilesystemCache(self::getFolder());
                break;
            case 'PhpFileCache':
                self::$cacheSystem = new \Doctrine\Common\Cache\FilesystemCache(self::getFolder());
                break;
            case 'MemcachedCache':
                $memcached = new \Memcached();
                $memcached->addServer(ConfigManager::byKey('cache::memcacheaddr'), ConfigManager::byKey('cache::memcacheport'));
                self::$cacheSystem = new \Doctrine\Common\Cache\MemcachedCache();
                self::$cacheSystem->setMemcached($memcached);
                break;
            case 'RedisCache':
                $redis = new \Redis();
                $redis->connect(ConfigManager::byKey('cache::redisaddr'), ConfigManager::byKey('cache::redisport'));
                self::$cacheSystem = new \Doctrine\Common\Cache\RedisCache();
                self::$cacheSystem->setRedis($redis);
                break;
            default:
                self::$cacheSystem = new \Doctrine\Common\Cache\FilesystemCache(self::getFolder());
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
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
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
     * TODO: Ouahhh
     * @return array
     */
    public static function search(): array
    {
        return array();
    }

    /**
     * Persist cache system
     */
    public static function persist()
    {
        switch (ConfigManager::byKey('cache::engine')) {
            case 'FilesystemCache':
                $cacheDir = self::getFolder();
                break;
            case 'PhpFileCache':
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

            \com_shell::execute($rmCmd);
            \com_shell::execute($tarCmd);
            \com_shell::execute($chmodCmd);
            \com_shell::execute($chownCmd);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return string
     */
    public static function getArchivePath()
    {
        return NEXTDOM_DATA . '/cache.tar.gz';
    }

    /**
     * Test if cache already exists
     *
     * @return bool True if file cache.tar.gz
     * @throws \Exception
     */
    public static function isPersistOk(): bool
    {
        if (ConfigManager::byKey('cache::engine') != 'FilesystemCache' && ConfigManager::byKey('cache::engine') != 'PhpFileCache') {
            return true;
        }
        $filename = self::getArchivePath();
        if (!file_exists($filename)) {
            return false;
        }
        if (filemtime($filename) < strtotime('-35min')) {
            return false;
        }
        return true;
    }

    /**
     * Restore persisted cache
     */
    public static function restore()
    {
        switch (ConfigManager::byKey('cache::engine')) {
            case 'FilesystemCache':
                $cache_dir = self::getFolder();
                break;
            case 'PhpFileCache':
                $cache_dir = self::getFolder();
                break;
            default:
                return;
        }

        if (!file_exists(self::getArchivePath())) {
            return;
        }

        SystemHelper::vsystem("rm -rf %s", $cache_dir);
        SystemHelper::vsystem("mkdir %s", $cache_dir);
        SystemHelper::vsystem("tar xzf %s -C %s", self::getArchivePath(), $cache_dir);
    }

    /**
     * Remove old and unused item stored in cache
     *
     * @throws \Exception
     */
    public static function clean()
    {
        if (ConfigManager::byKey('cache::engine') != 'FilesystemCache') {
            return;
        }
        $re = '/s:\d*:(.*?);s:\d*:"(.*?)";s/';
        $result = array();
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
        $cleanCache = array(
            'cmdCacheAttr' => 'cmd',
            'cmd' => 'cmd',
            'eqLogicCacheAttr' => 'eqLogic',
            'eqLogicStatusAttr' => 'eqLogic',
            'scenarioCacheAttr' => 'scenario',
            'cronCacheAttr' => 'cron',
            'cron' => 'cron',
        );
        foreach ($result as $key) {
            $matches = null;
            if (strpos($key, '::lastCommunication') !== false) {
                self::delete($key);
                continue;
            }
            if (strpos($key, '::state') !== false) {
                self::delete($key);
                continue;
            }
            if (strpos($key, '::numberTryWithoutSuccess') !== false) {
                self::delete($key);
                continue;
            }
            foreach ($cleanCache as $kClean => $value) {
                if (strpos($key, $kClean) !== false) {
                    $id = str_replace($kClean, '', $key);
                    if (!is_numeric($id)) {
                        continue;
                    }
                    $object = $value::byId($id);
                    if (!is_object($object)) {
                        self::delete($key);
                    }
                    continue;
                }
            }
            preg_match_all('/widgetHtml(\d*)(.*?)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                if (!is_numeric($matches[1][0])) {
                    continue;
                }
                $object = EqLogicManager::byId($matches[1][0]);
                if (!is_object($object)) {
                    self::delete($key);
                }
            }
            preg_match_all('/camera(\d*)(.*?)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                if (!is_numeric($matches[1][0])) {
                    continue;
                }
                $object = EqLogicManager::byId($matches[1][0]);
                if (!is_object($object)) {
                    self::delete($key);
                }
            }
            preg_match_all('/scenarioHtml(.*?)(\d*)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[1][0])) {
                if (!is_numeric($matches[1][0])) {
                    continue;
                }
                $object = ScenarioManager::byId($matches[1][0]);
                if (!is_object($object)) {
                    self::delete($key);
                }
            }
            if (strpos($key, 'widgetHtmldashboard') !== false) {
                $id = str_replace('widgetHtmldashboard', '', $key);
                if (is_numeric($id)) {
                    self::delete($key);
                }
                continue;
            }
            if (strpos($key, 'widgetHtmldplan') !== false) {
                $id = str_replace('widgetHtmldplan', '', $key);
                if (is_numeric($id)) {
                    self::delete($key);
                }
                continue;
            }
            if (strpos($key, 'widgetHtml') !== false) {
                $id = str_replace('widgetHtml', '', $key);
                if (is_numeric($id)) {
                    self::delete($key);
                }
                continue;
            }
            if (strpos($key, 'cmd') !== false) {
                $id = str_replace('cmd', '', $key);
                if (is_numeric($id)) {
                    self::delete($key);
                }
                continue;
            }
            preg_match_all('/dependancy(.*)/', $key, $matches);
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
                ->setDatetime(date('Y-m-d H:i:s'));
        }
        return $cache;
    }
}
