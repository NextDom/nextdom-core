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

use NextDom\Managers\CronManager;

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

class cron extends NextDom\Model\Entity\Cron {
    /**
     * Return an array of all cron object
     * @return array
     */
    public static function all($_order = false) {
        return CronManager::all($_order);
    }

    /**
     * Get cron object associate to id
     * @param int $_id
     * @return object
     */
    public static function byId($_id) {
        return CronManager::byId($_id);
    }

    /**
     * Return cron object corresponding to parameters
     * @param string $_class
     * @param string $_function
     * @param string $_option
     * @return object
     */
    public static function byClassAndFunction($_class, $_function, $_option = '') {
        return CronManager::byClassAndFunction($_class, $_function, $_option);
    }
    /**
     *
     * @param string $_class
     * @param string $_function
     * @param mixed $_option
     * @return mixed
     */
    public static function searchClassAndFunction($_class, $_function, $_option = '') {
        return CronManager::searchClassAndFunction($_class, $_function, $_option);
    }

    public static function clean() {
        CronManager::clean();
    }

    /**
     * Return number of cron running
     * @return int
     */
    public static function nbCronRun() {
        return CronManager::nbCronRun();
    }

    /**
     * Return number of process on system
     * @return int
     */
    public static function nbProcess() {
        return CronManager::nbProcess();
    }

    /**
     * Return array of load average
     * @return array
     */
    public static function loadAvg() {
        return CronManager::loadAvg();
    }

    /**
     * Set jeecron pid of current process
     */
    public static function setPidFile() {
        CronManager::setPidFile();
    }

    /**
     * Return the current pid of jeecron or empty if not running
     * @return int
     */
    public static function getPidFile() {
        return CronManager::getPidFile();
    }

    /**
     * Return state of jeecron master
     * @return boolean
     */
    public static function jeeCronRun() {
        return CronManager::jeeCronRun();
    }

    public static function convertDateToCron($_date) {
        return CronManager::convertDateToCron($_date);
    }
}
