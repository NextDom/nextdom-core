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
use NextDom\Com\ComShell;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\MessageManager;
use SplFileObject;

define('DEFAULT_MAX_LINES_IN_LOG', 200);

/**
 * Class LogHelper
 * @package NextDom\Helpers
 */
class LogHelper
{
    /**
     * @var array Logger cache
     */
    private static $logger = [];
    /**
     * @var array Config cache
     */
    private static $config = null;

    /**
     * Log an error
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool True if log added
     *
     * @throws \Exception
     */
    public static function addError($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'error', $message, $logicalId);
    }

    /**
     * Add a message to the log and ensure that there are never more than 1000 lines
     *
     * @param string $targetLog Target log file
     * @param string $logType Type of log
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool True if log added
     *
     * @throws \Exception
     */
    public static function add($targetLog, $logType, $message, $logicalId = '')
    {
        if (trim($message) === '') {
            return false;
        }
        $logger = self::getLogger($targetLog);
        $action = 'add' . ucwords(strtolower($logType));
        if (method_exists($logger, $action)) {
            $logger->$action($message);
            try {
                $logLevel = Logger::toMonologLevel($logType);
                if ($logLevel > Logger::ALERT || ($logLevel === Logger::ERROR && self::getConfig('addMessageForErrorLog') == 1)) {
                    @MessageManager::add($targetLog, $message, '', $logicalId);
                }
            } catch (\Exception $e) {
                error_log('LOG ERROR : ' . $e->getMessage());
            }
        }
        return true;
    }

    /**
     * Get logger depends of target
     *
     * @param string $targetLog Target log
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function getLogger($targetLog)
    {
        if (isset(self::$logger[$targetLog])) {
            return self::$logger[$targetLog];
        }
        $formatter = new LineFormatter(str_replace('\n', "\n", self::getConfig('log::formatter')));
        self::$logger[$targetLog] = new Logger($targetLog);
        switch (self::getConfig('log::engine')) {
            case 'SyslogHandler':
                $handler = new SyslogHandler(self::getLogLevel($targetLog));
                break;
            case 'SyslogUdp':
                $handler = new SyslogUdpHandler(ConfigManager::byKey('log::syslogudphost'), ConfigManager::byKey('log::syslogudpport'), 'user', self::getLogLevel($targetLog));
                break;
            case 'StreamHandler':
            default:
                $handler = new StreamHandler(self::getPathToLog($targetLog), self::getLogLevel($targetLog));
                break;
        }
        $handler->setFormatter($formatter);
        self::$logger[$targetLog]->pushHandler($handler);
        return self::$logger[$targetLog];
    }

    /**
     * Get config data by key
     *
     * @param string $configKey
     * @param string $defaultValue
     * @return string
     * @throws \Exception
     */
    public static function getConfig($configKey, $defaultValue = '')
    {
        // Load config data
        if (self::$config === null) {
            self::$config = array_merge(ConfigManager::getLogLevelPlugin(), ConfigManager::byKeys(['log::engine', 'log::formatter', 'log::level', 'addMessageForErrorLog', 'maxLineLog']));
        }
        if (isset(self::$config[$configKey])) {
            return self::$config[$configKey];
        }
        return $defaultValue;
    }

    /**
     * Get log level from config
     *
     * @param $targetLog
     * @return int|string
     * @throws \Exception
     */
    public static function getLogLevel($targetLog)
    {
        $specificTargetLevel = self::getConfig('log::level::' . $targetLog);
        // Get log level when user configure different level
        if (is_array($specificTargetLevel)) {
            if (isset($specificTargetLevel['default']) && $specificTargetLevel['default'] == 1) {
                return self::getConfig('log::level');
            }
            // Test all levels and return the selected
            foreach ($specificTargetLevel as $level => $selected) {
                if (!is_numeric($level)) {
                    continue;
                }
                if ($selected == 1) {
                    return $level;
                }
            }
        }
        return self::getConfig('log::level');
    }

    /**
     * Get target path log (usually /var/log/nextdom)
     *
     * @param string $targetLog
     *
     * @return string Log path
     */
    public static function getPathToLog($targetLog = 'core'): string
    {
        return NEXTDOM_LOG . '/' . $targetLog;
    }

    /**
     * Log an information
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function addInfo($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'info', $message, $logicalId);
    }

    /**
     * Log a debug
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function addDebug($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'debug', $message, $logicalId);
    }

    /**
     * Log a critical message
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function addCritical($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'critical', $message, $logicalId);
    }

    /**
     * Log an update message
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function addUpdate($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'update', $message, $logicalId);
    }

    /**
     * Log an alert message
     *
     * @param string $targetLog Target log file
     * @param string $message Message to log
     * @param string $logicalId Logical id linked to this log (optional)
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function addAlert($targetLog, $message, $logicalId = '')
    {
        return self::add($targetLog, 'alert', $message, $logicalId);
    }

    /**
     * Remove all log by deleting files
     *
     * @return bool True on success
     *
     * @throws \Exception
     */
    public static function removeAll()
    {
        // find in log and sub folder scenrioLog
        foreach (['', 'scenarioLog/'] as $logPath) {
            $logs = FileSystemHelper::ls(self::getPathToLog($logPath), '*');
            foreach ($logs as $log) {
                self::remove($log);
            }
        }
        return true;
    }

    /**
     * Delete log file
     *
     * @param string $targetLog Name of the log file
     *
     * @return bool True on success
     *
     * @throws \Exception
     */
    public static function remove($targetLog)
    {
        // Skip Apache and Nginx log files
        if (strpos($targetLog, 'nginx.error') !== false || strpos($targetLog, 'http.error') !== false) {
            self::clear($targetLog);
            return false;
        }
        if (self::authorizeClearLog($targetLog)) {
            $path = self::getPathToLog($targetLog);
            ComShell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $path . ' > /dev/null 2>&1; rm ' . $path . ' 2>&1 > /dev/null');
            return false;
        }
        return null;
    }

    /**
     * Clear log file
     *
     * @param string $targetLog Name of the log file
     *
     * @return bool True on success
     *
     * @throws \Exception
     */
    public static function clear($targetLog): bool
    {
        if (self::authorizeClearLog($targetLog)) {
            $path = self::getPathToLog($targetLog);
            ComShell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $path . '> /dev/null 2>&1; cat /dev/null > ' . $path);
            return true;
        }
        return false;
    }

    /**
     * Check if target log file is good
     *
     * @param string $targetLog Name of the log file
     * @param string $subFolder Subfolder name
     *
     * @return bool
     */
    public static function authorizeClearLog($targetLog, $subFolder = '')
    {
        $path = self::getPathToLog($subFolder . $targetLog);
        return !((strpos($targetLog, '.htaccess') !== false)
            || (!file_exists($path) || !is_file($path)));
    }

    /**
     * Get log file content
     *
     * @param string $targetLog Target log
     * @param int $start Start row
     * @param int $nbLines Number of lines to get
     *
     * @return array|bool Content of the log or false on error
     *
     * @throws \Exception
     */
    public static function get($targetLog = 'core', $start = 0, $nbLines = DEFAULT_MAX_LINES_IN_LOG)
    {
        self::chunk($targetLog);
        $path = (!file_exists($targetLog) || !is_file($targetLog)) ? self::getPathToLog($targetLog) : $targetLog;
        if (!file_exists($path)) {
            return false;
        }
        $page = [];
        $log = new SplFileObject($path);
        if ($log) {
            $log->seek($start); //Seek to the beginning of lines
            $linesRead = 0;
            while ($log->valid() && $linesRead != $nbLines) {
                $line = trim($log->current()); //get current line
                if ($line != '') {
                    if (function_exists('mb_convert_encoding')) {
                        array_unshift($page, mb_convert_encoding($line, 'UTF-8'));
                    } else {
                        array_unshift($page, $line);
                    }
                }
                $log->next(); //go to next line
                $linesRead++;
            }
        }
        return $page;
    }

    /**
     * Reduce all logs files
     *
     * @param string $targetLog Target log or empty for all
     *
     * @throws \Exception
     */
    public static function chunk($targetLog = '')
    {
        $paths = [];
        if ($targetLog != '') {
            $paths[] = self::getPathToLog($targetLog);
        } else {
            $relativeLogPaths = ['', 'scenarioLog/'];
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
     * Reduce log file to DEFAULT_MAX_LINES_IN_LOG
     *
     * @param string $logFilePath Path of the file
     *
     * @throws \Exception
     */
    public static function chunkLog($logFilePath)
    {
        if (strpos($logFilePath, '.htaccess') !== false) {
            return;
        }
        $maxLineLog = self::getConfig('maxLineLog');
        if ($maxLineLog < DEFAULT_MAX_LINES_IN_LOG) {
            $maxLineLog = DEFAULT_MAX_LINES_IN_LOG;
        }
        try {
            ComShell::execute(SystemHelper::getCmdSudo() . 'chmod 664 ' . $logFilePath . ' > /dev/null 2>&1;echo "$(tail -n ' . $maxLineLog . ' ' . $logFilePath . ')" > ' . $logFilePath);
        } catch (\Exception $e) {

        }
        @chown($logFilePath, SystemHelper::getCommand('www-uid'));
        @chgrp($logFilePath, SystemHelper::getCommand('www-gid'));
        if (filesize($logFilePath) > (1024 * 1024 * 10)) {
            ComShell::execute(SystemHelper::getCmdSudo() . 'truncate -s 0 ' . $logFilePath);
        }
        if (filesize($logFilePath) > (1024 * 1024 * 10)) {
            ComShell::execute(SystemHelper::getCmdSudo() . 'cat /dev/null > ' . $logFilePath);
        }
        if (filesize($logFilePath) > (1024 * 1024 * 10)) {
            ComShell::execute(SystemHelper::getCmdSudo() . ' rm -f ' . $logFilePath);
        }
    }

    /**
     * Get list of log files
     *
     * @deprecated Use getLogFileList
     *
     * @param string $filter Pattern matchs
     *
     * @return array List of files
     */
    public static function liste($filter = null)
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
        return self::getLogFileList($filter);
    }

    /**
     * Get list of log files
     *
     * @param string $filter Pattern matchs
     *
     * @return array List of files
     */
    public static function getLogFileList($filter = null): array
    {
        $result = [];
        foreach (FileSystemHelper::ls(self::getPathToLog(''), '*') as $log) {
            if ($filter !== null && strpos($log, $filter) === false) {
                continue;
            }
            if (!is_dir(self::getPathToLog($log))) {
                $result[] = $log;
            }
        }
        return $result;
    }

    /**
     * Get list of all log files
     *
     * @param string $folder Log folder
     *
     * @return array List of files
     */
    public static function getAllLogFileList($folder = ''): array
    {
        $result = [];
        foreach (FileSystemHelper::ls(self::getPathToLog($folder), '*') as $log) {
            $item = ['name' => $log, 'content' => []];
            if (is_dir(self::getPathToLog($log))) {
                $item['content'] = self::getAllLogFileList($log);
            }
            $result[] = $item;
        }
        return $result;
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
                throw new CoreException('log::level invalide ("' . $log_level . '")');
                break;
        }
    }

    /**
     * Get exception message
     *
     * @param \Exception $e Exception
     *
     * @return mixed Message
     *
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

    /**
     * Get log level name from log level value
     *
     * @param int $logLevelValue Log level value that correspond to specific level
     *
     * @return string|null Name of the log level or null
     */
    public static function convertLogLevel($logLevelValue = 100)
    {
        if ($logLevelValue > logger::EMERGENCY) {
            return 'none';
        }
        try {
            return strtolower(Logger::getLevelName($logLevelValue));
        } catch (\Exception $e) {

        }
        return null;
    }
}
