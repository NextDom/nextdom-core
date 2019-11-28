<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\IFileInfo;
use Icewind\SMB\ServerFactory;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;

define('SAMBA_DEFAULT_WORKSPACE', 'WORKGROUP');
define('SAMBA_DEFAULT_CONFIG_NAME', 'backup');
define('SAMBA_SORT_BY_TIME', 'mtime');
define('SAMBA_SORT_BY_SIZE', 'size');
define('SAMBA_SORT_ASC_ORDER', 'asc');

/**
 * Samba usage
 *
 * @package NextDom\Helpers
 */
class Samba
{
    /** @var \Icewind\SMB\IServer Samba client */
    private $client = null;
    /** @var string Share name */
    private $share = null;

    /**
     * Initialize Samba process
     *
     * @param string $host Server name or IP
     * @param string $user Samba user
     * @param string $password Samba user password
     * @param string $share Target share
     *
     * @throws CoreException
     */
    public function __construct(string $host, string $user, string $password, string $share)
    {
        try {
            // Server connection
            $serverFactory = new ServerFactory(null, null, null);
            $auth = new BasicAuth($user, SAMBA_DEFAULT_WORKSPACE, $password);
            $this->client = $serverFactory->createServer($host, $auth);
            $this->share = $share;
        } catch (\Exception $e) {
            CoreException::do_throw('{repo.samba.error.connect}: %s', $e->getMessage());
        }
    }

    /**
     * Create connection from config name
     *
     * @param string $target Type of target (backup for restore)
     *
     * @return Samba Connection to Samba server object
     *
     * @throws \Exception
     */
    public static function createFromConfig(string $target = SAMBA_DEFAULT_CONFIG_NAME)
    {
        $baseConfigKey = 'samba::' . $target;
        $configData = ConfigManager::byKeys([
            $baseConfigKey . '::ip',
            $baseConfigKey . '::share',
            $baseConfigKey . '::username',
            $baseConfigKey . '::password',
        ]);

        // Compatibility with old parameter semantic
        $matches = [];
        if (preg_match("%(//[^/]+/)(.*)%", $configData[$baseConfigKey . '::share'], $matches)) {
            $configData[$baseConfigKey . '::share'] = $matches[2];
        }
        return new Samba(
            $configData[$baseConfigKey . '::ip'],
            $configData[$baseConfigKey . '::username'],
            $configData[$baseConfigKey . '::password'],
            $configData[$baseConfigKey . '::share']);
    }

    /**
     * Replace special characters by -
     *
     * @param string $originalFilename Name of the file before replace
     *
     * @return string Clean filename
     */
    public static function cleanName(string $originalFilename)
    {
        return str_replace(['<', '>', ':', '\'', '"', '/', '\\', '|', '?', '*'], '-', $originalFilename);
    }

    /**
     * Copy file from the server
     *
     * @param string $src Path of the file on the server
     * @param string $dest Copy destination
     *
     * @throws CoreException
     */
    public function get($src, $dest)
    {
        try {
            $this->getShare()->get($src, $dest);
        } catch (\Icewind\SMB\Exception\NotFoundException $e) {
            CoreException::do_throw('{repo.samba.error.not-found}: %s', $src);
        } catch (\Throwable $e) {
            CoreException::do_throw('{repo.samba.error.unknown}: %s', $e->getMessage());
        }
    }

    /**
     * Get current share
     *
     * @param string|null $name Share name
     *
     * @return \Icewind\SMB\IShare|null Current share
     *
     * @throws CoreException
     */
    public function getShare(string $name = null)
    {
        if ($name === null) {
            $name = $this->share;
        }
        try {
            return $this->client->getShare($name);
        } catch (\Exception $e) {
            CoreException::do_throw('{repo.samba.error.connect}: %s', $e->getMessage());
        }
        return null;
    }

    /**
     * Copy file on the server
     *
     * @param string $src Path of the file to copy
     * @param string $dest Server path destination
     *
     * @throws CoreException
     */
    public function put($src, $dest)
    {
        try {
            $this->getShare()->put($src, $dest);
        } catch (\Icewind\SMB\Exception\NotFoundException $e) {
            CoreException::do_throw('{repo.samba.error.not-found}: %s', $src);
        } catch (\Throwable $e) {
            CoreException::do_throw('{repo.samba.error.unknown}: %s', $e->getMessage());
        }
    }

    /**
     * Delete a file from the server
     *
     * @param string $path Path of the file
     *
     * @return IFileInfo[]|null Content of the folder
     *
     * @throws CoreException
     */
    public function del($path)
    {
        try {
            $this->getShare()->del($path);
        } catch (\Icewind\SMB\Exception\NotFoundException $e) {
            CoreException::do_throw('{repo.samba.error.not-found}: %s', $path);
        } catch (\Throwable $e) {
            CoreException::do_throw('{repo.samba.error.unknown}: %s', $e->getMessage());
        }
        return null;
    }

    /**
     * Get files list in folder
     *
     * @param string $path Folder path
     * @param string $sort Sort method (mtime, size or name)
     * @param string $order Order (asc or desc)
     *
     * @return IFileInfo[] Folder content
     *
     * @throws CoreException
     */
    public function getFiles($path = '/', $sort = SAMBA_SORT_BY_TIME, $order = SAMBA_SORT_ASC_ORDER)
    {
        $entries = $this->getEntries($path, $sort, $order);
        $filteredEntries = array_filter($entries, [$this, 'filterByFile']);
        return $filteredEntries;
    }

    /**
     * Get folder content
     *
     * @param string $path Folder path
     * @param string $sort Sort method (mtime, size or name)
     * @param string $order Order (asc or desc)
     *
     * @return IFileInfo[]|null Folder content
     *
     * @throws CoreException
     */
    public function getEntries($path = '/', $sort = SAMBA_SORT_BY_TIME, $order = SAMBA_SORT_ASC_ORDER)
    {
        $entries = $this->dir($path);

        if ($entries !== null) {
            $sortMethodName = 'compareByName';
            switch ($sort) {
                case SAMBA_SORT_BY_TIME:
                    $sortMethodName = 'compareByTime';
                    break;
                case SAMBA_SORT_BY_SIZE:
                    $sortMethodName = 'compareBySize';
                    break;
            }
            usort($entries, [$this, $sortMethodName]);
            if ($order !== SAMBA_SORT_ASC_ORDER) {
                $entries = array_reverse($entries);
            }
        }
        return $entries;
    }

    /**
     * List folder content on the server
     *
     * @param string $path Path of the folder
     *
     * @return IFileInfo[]|null Content of the folder
     *
     * @throws CoreException
     */
    public function dir($path)
    {
        try {
            return $this->getShare()->dir($path);
        } catch (\Icewind\SMB\Exception\NotFoundException $e) {
            CoreException::do_throw('{repo.samba.error.not-found}: %s', $path);
        } catch (\Throwable $e) {
            CoreException::do_throw('{repo.samba.error.unknown}: %s', $e->getMessage());
        }
        return null;
    }

    /**
     * Test if item is a file.
     *
     * @param IFileInfo $currentItem Item to test
     *
     * @return True if item if file
     */
    private function filterByFile($currentItem)
    {
        return $currentItem->isDirectory() === false;
    }

    /**
     * Compare by name for sort list
     *
     * @param IFileInfo $firstFile Data of the first file
     * @param IFileInfo $secondFile Data of the second file
     *
     * @return int Sort information
     */
    private function compareByName($firstFile, $secondFile)
    {
        return strcmp($firstFile->getName(), $secondFile->getName());
    }

    /**
     * Compare by size for sort list
     *
     * @param IFileInfo $firstFile Data of the first file
     * @param IFileInfo $secondFile Data of the second file
     *
     * @return int Sort information
     */
    private function compareBySize($firstFile, $secondFile)
    {
        if ($firstFile->getSize() === $secondFile->getSize()) {
            return 0;
        }
        return ($firstFile->getSize() < $secondFile->getSize()) ? -1 : 1;
    }

    /**
     * Compare by time for sort list
     *
     * @param IFileInfo $firstFile Data of the first file
     * @param IFileInfo $secondFile Data of the second file
     *
     * @return int Sort information
     */
    private function compareByTime($firstFile, $secondFile)
    {
        if ($firstFile->getMTime() === $secondFile->getMTime()) {
            return 0;
        }
        return ($firstFile->getMTime() < $secondFile->getMTime()) ? -1 : 1;
    }
}
