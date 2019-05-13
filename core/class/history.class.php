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

/* * ***************************Includes********************************* */

use NextDom\Managers\HistoryManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class history extends \NextDom\Model\Entity\History {
    public static function copyHistoryToCmd($_source_id, $_target_id) {
        HistoryManager::copyHistoryToCmd($_source_id, $_target_id);
    }

    public static function byCmdIdDatetime($_cmd_id, $_startTime, $_endTime = null, $_oldValue = null) {
        return HistoryManager::byCmdIdDatetime($_cmd_id, $_startTime, $_endTime, $_oldValue);
    }

    /**
     * Archive les données de history dans historyArch
     */
    public static function archive() {
        HistoryManager::archive();
    }

    /**
     *
     * @param $_cmd_id
     * @param null $_startTime
     * @param null $_endTime
     * @return \history[] des valeurs de l'équipement
     * @throws Exception
     */
    public static function all($_cmd_id, $_startTime = null, $_endTime = null) {
        return HistoryManager::all($_cmd_id, $_startTime, $_endTime);
    }

    public static function removes($_cmd_id, $_startTime = null, $_endTime = null) {
        return HistoryManager::removes($_cmd_id, $_startTime, $_endTime);
    }

    public static function getPlurality($_cmd_id, $_startTime = null, $_endTime = null, $_period = 'day', $_offset = 0) {
        return HistoryManager::getPlurality($_cmd_id, $_startTime, $_endTime, $_period, $_offset);
    }

    public static function getStatistique($_cmd_id, $_startTime, $_endTime) {
        return HistoryManager::getStatistique($_cmd_id, $_startTime, $_endTime);
    }

    public static function getTendance($_cmd_id, $_startTime, $_endTime) {
        return HistoryManager::getTendance($_cmd_id, $_startTime, $_endTime);
    }

    public static function stateDuration($_cmd_id, $_value = null) {
        return self::stateDuration($_cmd_id, $_value);
    }

    public static function lastStateDuration($_cmd_id, $_value = null) {
        return HistoryManager::lastStateDuration($_cmd_id, $_value);
    }
    /**
     * Fonction renvoie la durée depuis le dernier changement d'état
     * à la valeur passée en paramètre
     */
    public static function lastChangeStateDuration($_cmd_id, $_value) {
        return HistoryManager::lastChangeStateDuration($_cmd_id, $_value);
    }

    /**
     *
     * @param int $_cmd_id
     * @param string/float $_value
     * @param string $_startTime
     * @param string $_endTime
     * @return array
     * @throws Exception
     */
    public static function stateChanges($_cmd_id, $_value = null, $_startTime = null, $_endTime = null) {
        return HistoryManager::stateChanges($_cmd_id, $_value, $_startTime, $_endTime);
    }

    public static function emptyHistory($_cmd_id, $_date = '') {
        return HistoryManager::emptyHistory($_cmd_id, $_date);
    }

    public static function getHistoryFromCalcul($_strcalcul, $_dateStart = null, $_dateEnd = null, $_noCalcul = false) {
        return HistoryManager::getHistoryFromCalcul($_strcalcul, $_dateStart, $_dateEnd, $_noCalcul);
    }

    public static function getTemporalAvg($_cmd_id, $_startTime, $_endTime){
        return HistoryManager::getTemporalAvg($_cmd_id, $_startTime, $_endTime);
    }

}