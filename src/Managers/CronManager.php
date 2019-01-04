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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;

class CronManager {

    const CLASS_NAME = 'NextDom\\Model\\Entity\\Cron';
    const DB_CLASS_NAME = '`cron`';

    /**
     * Return an array of all cron objects
     *
     * @return array List of all cron objets
     */
    public static function all($ordered = false) {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME;
        if ($ordered) {
            $sql .= ' ORDER BY deamon DESC';
        }
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get cron object by his id
     *
     * @param int $cronId
     *
     * @return object
     */
    public static function byId($cronId) {
        error_log('coucou');
        $value = array(
            'id' => $cronId,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Return cron object by class and function
     *
     * @param string $className Name of the class
     * @param string $functionName Name of the method
     * @param string $options Filter options
     *
     * @return \cron Cron object
     */
    public static function byClassAndFunction($className, $functionName, $options = '') {
        $value = array(
            'class' => $className,
            'function' => $functionName,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE class = :class
                AND function = :function';
        if ($options != '') {
            $options = json_encode($options, JSON_UNESCAPED_UNICODE);
            $value['option'] = $options;
            $sql .= ' AND `option` = :option';
        }
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Return cron object by class and function
     *
     * @param string $className Name of the class
     * @param string $functionName Name of the method
     * @param string $options Filter options
     *
     * @return array[\cron] List of cron objects
     */
    public static function searchClassAndFunction($className, $functionName, $options = '') {
        $value = array(
            'class' => $className,
            'function' => $functionName,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE class = :class
                AND function = :function';
        if ($options != '') {
            $value['option'] = '%' . $options . '%';
            $sql .= ' AND `option` LIKE :option';
        }
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Clean cron that will never run
     */
    public static function clean() {
        $crons = self::all();
        foreach ($crons as $cronExpression) {
            $cronExpression = new \Cron\CronExpression($cronExpression->getSchedule(), new \Cron\FieldFactory);
            try {
                if (!$cronExpression->isDue()) {
                    $cronExpression->getNextRunDate();
                }
            } catch (CoreException $ex) {
                $cronExpression->remove();
            }
        }
    }

    /**
     * Return number of running cron
     *
     * @return int Number of running cron
     */
    public static function nbCronRun() {
        return count(SystemHelper::ps('jeeCron.php', array('grep', 'sudo', 'shell=/bin/bash - ', '/bin/bash -c ', posix_getppid(), getmypid())));
    }

    /**
     * Return number of process on system
     *
     * TODO: Move to other place ?
     *
     * @return int Number of process on system
     */
    public static function nbProcess() {
        return count(SystemHelper::ps('.'));
    }

    /**
     * Return array of load average
     *
     * TODO: Inutile, autant appeler directement la fonction
     *
     * @return array Load average
     */
    public static function loadAvg() {
        return sys_getloadavg();
    }

    /**
     * Write jeeCron PID of current process
     */
    public static function setPidFile() {
        $path = NextDomHelper::getTmpFolder() . '/jeeCron.pid';
        $fp = fopen($path, 'w');
        fwrite($fp, getmypid());
        fclose($fp);
    }

    /**
     * Return the current pid of jeecron or empty if not running
     *
     * @return int Current jeeCron PID
     */
    public static function getPidFile() {
        $path = NextDomHelper::getTmpFolder() . '/jeeCron.pid';
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
    }

    /**
     * Get status of jeeCron
     *
     * @return boolean True if jeeCron is running
     */
    public static function jeeCronRun() {
        $pid = self::getPidFile();
        if ($pid == '' || !is_numeric($pid)) {
            return false;
        }
        return posix_getsid($pid);
    }

    /**
     * Convert date to cron format.
     *
     * @param string $dateToConvert Date to convert
     *
     * @return string Date in cron format
     */
    public static function convertDateToCron($dateToConvert) {
        return date('i', $dateToConvert) . ' ' . date('H', $dateToConvert) . ' ' . date('d', $dateToConvert) . ' ' . date('m', $dateToConvert) . ' * ' . date('Y', $dateToConvert);
    }
}
