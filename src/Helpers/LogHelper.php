<?php
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

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\MessageManager;
use NextDom\Singletons\PHPInformation;
use SplFileObject;

/**
 * Class LogHelper
 * @package NextDom\Helpers
 */
class LogHelper
{
    const DEFAULT_MAX_LINE = 200;

    private static $logger = array();
    private static $config = null;

    /**
     * @param int $_level
     * @return string|null
     */
    public static function convertLogLevel($_level = 100)
    {
        if ($_level > logger::EMERGENCY) {
            return 'none';
        }
        try {
            return strtolower(Logger::getLevelName($_level));
        } catch (\Exception $e) {

        }
        return null;
    }

    /**
     * @param        $logTarget
     * @param        $message
     * @param string $logicalId
     */
    public static function addError($logTarget, $message, $logicalId = '')
    {
        $message = $message . '\n' . PHPInformation::getInstance()->getCallingFunctionName(true);
        self::add($logTarget, 'error', $message, $logicalId);
    }

    /**
     * Add a message to the log and ensure that there are never more than 1000 lines
     *
     * @param $_log
     * @param string $_type type du message à mettre dans les log
     * @param string $_message message à mettre dans les logs
     * @param string $_logicalId
     */
    public static function add($_log, $_type, $_message, $_logicalId = '')
    {
        if (trim($_message) == '') {
            return;
        }
        $logger = self::getLogger($_log);
        $action = 'add' . ucwords(strtolower($_type));
        if (method_exists($logger, $action)) {
            $logger->$action($_message);
            try {
                $level = Logger::toMonologLevel($_type);
                if ($level == Logger::ERROR && self::getConfig('addMessageForErrorLog') == 1) {
                    @MessageManager::add($_log, $_message, '', $_logicalId);
                } elseif ($level > Logger::ALERT) {
                    @MessageManager::add($_log, $_message, '', $_logicalId);
                }
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @param $_log
     * @return mixed
     * @throws \Exception
     */
    public static function getLogger($_log)
    {
        if (isset(self::$logger[$_log])) {
            return self::$logger[$_log];
        }
        $formatter = new LineFormatter(str_replace('\n', "\n", self::getConfig('log::formatter')));
        self::$logger[$_log] = new Logger($_log);
        switch (self::getConfig('log::engine')) {
            case 'SyslogHandler':
                $handler = new SyslogHandler(self::getLogLevel($_log));
                break;
            case 'SyslogUdp':
                $handler = new SyslogUdpHandler(ConfigManager::byKey('log::syslogudphost'), ConfigManager::byKey('log::syslogudpport'), 'user', self::getLogLevel($_log));
                break;
            case 'StreamHandler':
            default:
                $handler = new StreamHandler(self::getPathToLog($_log), self::getLogLevel($_log));
                break;
        }
        $handler->setFormatter($formatter);
        self::$logger[$_log]->pushHandler($handler);
        return self::$logger[$_log];
    }

    /**
     * @param        $_key
     * @param string $_default
     * @return string
     * @throws \Exception
     */
    public static function getConfig($_key, $_default = '')
    {
        if (self::$config === null) {
            self::$config = array_merge(ConfigManager::getLogLevelPlugin(), ConfigManager::byKeys(array('log::engine', 'log::formatter', 'log::level', 'addMessageForErrorLog', 'maxLineLog')));
        }
        if (isset(self::$config[$_key])) {
            return self::$config[$_key];
        }
        return $_default;
    }

    /**
     * @param $_log
     * @return int|string
     * @throws \Exception
     */
    public static function getLogLevel($_log)
    {
        $specific_level = self::getConfig('log::level::' . $_log);
        if (is_array($specific_level)) {
            if (isset($specific_level['default']) && $specific_level['default'] == 1) {
                return self::getConfig('log::level');
            }
            foreach ($specific_level as $key => $value) {
                if (!is_numeric($key)) {
                    continue;
                }
                if ($value == 1) {
                    return $key;
                }
            }
        }
        return self::getConfig('log::level');
    }

    /**
     * @param string $_log
     * @return string
     */
    public static function getPathToLog($_log = 'core')
    {
        return NEXTDOM_LOG . '/' . $_log;
    }

    /**
     * @param        $logTarget
     * @param        $message
     * @param string $logicalId
     */
    public static function addInfo($logTarget, $message, $logicalId = '')
    {
        self::add($logTarget, 'info', $message, $logicalId);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function removeAll()
    {
        foreach (array('', 'scenarioLog/') as $logPath) {
            $logs = FileSystemHelper::ls(self::getPathToLog($logPath), '*');
            foreach ($logs as $log) {
                self::remove($log);
            }
        }
        return true;
    }

    /**
     * Vide le fichier de log
     * @param $_log
     * @return bool|null
     * @throws \Exception
     */
    public static function remove($_log)
    {
        if (strpos($_log, 'nginx.error') !== false || strpos($_log, 'http.error') !== false) {
            self::clear($_log);
            return null;
        }
        if (self::authorizeClearLog($_log)) {
            $path = self::getPathToLog($_log);
            \com_shell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $path . ' > /dev/null 2>&1; rm ' . $path . ' 2>&1 > /dev/null');
            return true;
        }
        return null;
    }

    /**
     * Vide le fichier de log
     * @param $_log
     * @return bool|null
     * @throws \Exception
     */
    public static function clear($_log)
    {
        if (self::authorizeClearLog($_log)) {
            $path = self::getPathToLog($_log);
            \com_shell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $path . '> /dev/null 2>&1;cat /dev/null > ' . $path);
            return true;
        }
        return null;
    }

    /**
     * Autorisation de vide le fichier de log
     * @param $_log
     * @param string $_subPath
     * @return bool
     */
    public static function authorizeClearLog($_log, $_subPath = '')
    {
        $path = self::getPathToLog($_subPath . $_log);
        return !((strpos($_log, '.htaccess') !== false)
            || (!file_exists($path) || !is_file($path)));
    }

    /**
     * @param string $_log
     * @param        $_begin
     * @param        $_nbLines
     * @return array|bool
     */
    public static function get($_log = 'core', $_begin, $_nbLines)
    {
        self::chunk($_log);
        $path = (!file_exists($_log) || !is_file($_log)) ? self::getPathToLog($_log) : $_log;
        if (!file_exists($path)) {
            return false;
        }
        $page = array();
        $log = new SplFileObject($path);
        if ($log) {
            $log->seek($_begin); //Seek to the begening of lines
            $linesRead = 0;
            while ($log->valid() && $linesRead != $_nbLines) {
                $line = trim($log->current()); //get current line
                if ($line != '') {
                    array_unshift($page, $line);
                }
                $log->next(); //go to next line
                $linesRead++;
            }
        }
        return $page;
    }

    /**
     * @param string $_log
     */
    public static function chunk($_log = '')
    {
        $paths = array();
        if ($_log != '') {
            $paths = array(self::getPathToLog($_log));
        } else {
            $relativeLogPaths = array('', 'scenarioLog/');
            foreach ($relativeLogPaths as $relativeLogPath) {
                $logPath = self::getPathToLog($relativeLogPath);
                $logs = FileSystemHelper::ls($logPath, '*');
                foreach ($logs as $log) {
                    $paths[] = $logPath . $log;
                }
            }
        }
        foreach ($paths as $path) {
            if (is_file($path)) {
                self::chunkLog($path);
            }
        }
    }

    /**
     * @param $_path
     * @throws \Exception
     */
    public static function chunkLog($_path)
    {
        if (strpos($_path, '.htaccess') !== false) {
            return;
        }
        $maxLineLog = self::getConfig('maxLineLog');
        if ($maxLineLog < self::DEFAULT_MAX_LINE) {
            $maxLineLog = self::DEFAULT_MAX_LINE;
        }
        \com_shell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $_path . ' > /dev/null 2>&1;echo "$(tail -n ' . $maxLineLog . ' ' . $_path . ')" > ' . $_path);
        @chown($_path, SystemHelper::getCommand('www-uid'));
        @chgrp($_path, SystemHelper::getCommand('www-gid'));
        if (filesize($_path) > (1024 * 1024 * 10)) {
            \com_shell::execute(SystemHelper::getCmdSudo() . 'truncate -s 0 ' . $_path);
        }
        if (filesize($_path) > (1024 * 1024 * 10)) {
            \com_shell::execute(SystemHelper::getCmdSudo() . 'cat /dev/null > ' . $_path);
        }
        if (filesize($_path) > (1024 * 1024 * 10)) {
            \com_shell::execute(SystemHelper::getCmdSudo() . ' rm -f ' . $_path);
        }
    }

    /**
     * @param null $_filtre
     * @return array
     */
    public static function liste($_filtre = null)
    {
        $return = array();
        foreach (FileSystemHelper::ls(self::getPathToLog(''), '*') as $log) {
            if ($_filtre !== null && strpos($log, $_filtre) === false) {
                continue;
            }
            if (!is_dir(self::getPathToLog($log))) {
                $return[] = $log;
            }
        }
        return $return;
    }

    /**
     * Fixe le niveau de rapport d'erreurs PHP
     * @param int $log_level
     * @throws \Exception
     * @since 2.1.4
     * @author KwiZeR <kwizer@kw12er.com>
     */
    public static function define_error_reporting($log_level)
    {
        switch ($log_level) {
            case logger::DEBUG:
            case logger::INFO:
            case logger::NOTICE:
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                break;
            case logger::WARNING:
                error_reporting(E_ERROR | E_WARNING | E_PARSE);
                break;
            case logger::ERROR:
                error_reporting(E_ERROR | E_PARSE);
                break;
            case logger::CRITICAL:
                error_reporting(E_ERROR | E_PARSE);
                break;
            case logger::ALERT:
                error_reporting(E_ERROR | E_PARSE);
                break;
            case logger::EMERGENCY:
                error_reporting(E_ERROR | E_PARSE);
                break;
            default:
                throw new \Exception('log::level invalide ("' . $log_level . '")');
                break;
        }
    }

    /**
     * @param $e
     * @return mixed
     * @throws \Exception
     */
    public static function exception($e)
    {
        if (self::getConfig('log::level') > 100) {
            return $e->getMessage();
        } else {
            return print_r($e, true);
        }
    }
}
