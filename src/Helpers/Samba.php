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
use Icewind\SMB\ServerFactory;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;

/**
 * Class Samba
 * @package NextDom\Helpers
 */
class Samba
{
    private $client = null;
    private $share = null;

    /**
     * Samba constructor.
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $share
     * @throws CoreException
     */
    public function __construct(string $host, string $user, string $password, string $share)
    {
        try {
            $serverFactory = new ServerFactory();
            $auth = new BasicAuth($user, "WORKGROUP", $password);
            $this->client = $serverFactory->createServer($host, $auth);
            $this->share = $share;
        } catch (\Exception $e) {
            CoreException::do_throw("{samba.error.connect}: %s", $e->getMessage());
        }
    }

    /**
     * @param string $target
     * @return Samba
     * @throws \Exception
     */
    public static function createFromConfig(string $target = "backup")
    {
        $host = ConfigManager::byKey('samba::' . $target . '::ip');
        $share = ConfigManager::byKey('samba::' . $target . '::share');
        $username = ConfigManager::byKey('samba::' . $target . '::username');
        $password = ConfigManager::byKey('samba::' . $target . '::password');

        // 1. compatibility with old parameter semantic
        $matches = [];
        if (preg_match("%(//[^/]+/)(.*)%", $share, $matches)) {
            $share = $matches[2];
        }
        return new Samba($host, $username, $password, $share);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    public static function cleanName(string $filename)
    {
        return str_replace(array("<", ">", ":", "\"", "/", "\\", "|", "?", "*"), "-", $filename);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     * @throws CoreException
     */
    public function __call($name, $args)
    {
        try {
            return call_user_func_array(array($this->getShare(), $name), $args);
        } catch (\Icewind\SMB\Exception\NotFoundException $e) {
            CoreException::do_throw("{samba.error.not-found}: '%s'", $args[0]);
        } catch (\Icewind\SMB\Exception\AlreadyExistsException $e) {
            CoreException::do_throw("{samba.error.already-exists}: '%s'", $args[0]);
        } catch (\Exception $e) {
            CoreException::do_throw("{samba.error.unknown}: %s", $e->getMessage());
        }
    }

    /**
     * @param string|null $name
     * @return \Icewind\SMB\IShare
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
            CoreException::do_throw("{samba.error.connect}: %s", $e->getMessage());
        }
    }

    /**
     * @param string $dir
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getFiles($dir = "/", $sort = 'mtime', $order = 'asc')
    {
        $entries = $this->getEntries($dir, $sort, $order);
        $files = array_filter($entries, function ($c_item) {
            return (false === $c_item->isDirectory());
        });
        return $files;
    }

    /**
     * @param string $dir
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getEntries($dir = "/", $sort = 'mtime', $order = 'asc')
    {
        $entries = $this->dir($dir);

        switch ($sort) {
            case "mtime":
                $functor = function ($x, $y) {
                    return $x->getMTime() < $x->getMTime();
                };
                break;
            case "size":
                $functor = function ($x, $y) {
                    return $x->getSize() < $x->getSize();
                };
                break;

            default:
                $functor = function ($x, $y) {
                    return $x->getName() < $x->getName();
                };
        }
        usort($entries, $functor);
        if ("asc" !== $order) {
            $entries = array_reverse($entries);
        }
        return $entries;
    }
}
