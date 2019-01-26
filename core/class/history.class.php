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

class history {
    /*     * *************************Attributs****************************** */

    protected $cmd_id;
    protected $value;
    protected $datetime;
    protected $_tableName = 'history';

    /*     * ***********************Methode static*************************** */

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

    /*     * *********************Methode d'instance************************* */

    public function save($_cmd = null, $_direct = false) {
        global $NEXTDOM_INTERNAL_CONFIG;
        if ($_cmd === null) {
            $cmd = $this->getCmd();
            if (!is_object($cmd)) {
                self::emptyHistory($this->getCmd_id());
                return;
            }
        } else {
            $cmd = $_cmd;
        }
        if ($this->getDatetime() == '') {
            $this->setDatetime(date('Y-m-d H:i:s'));
        }
        if ($cmd->getConfiguration('historizeRound') !== '' && is_numeric($cmd->getConfiguration('historizeRound')) && $cmd->getConfiguration('historizeRound') >= 0 && $this->getValue() !== null) {
            $this->setValue(round($this->getValue(), $cmd->getConfiguration('historizeRound')));
        }
        if ($NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['canBeSmooth'] && $cmd->getConfiguration('historizeMode', 'avg') != 'none' && $this->getValue() !== null && $_direct === false) {
            if ($this->getTableName() == 'history') {
                $time = strtotime($this->getDatetime());
                $time -= $time % 300;
                $this->setDatetime(date('Y-m-d H:i:s', $time));
                if ($this->getValue() === 0) {
                    $values = array(
                        'cmd_id' => $this->getCmd_id(),
                        'datetime' => date('Y-m-d H:i:00', strtotime($this->getDatetime()) + 300),
                        'value' => $this->getValue(),
                    );
                    $sql = 'REPLACE INTO history
                    SET cmd_id=:cmd_id,
                    `datetime`=:datetime,
                    value=:value';
                    DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
                    return;
                }
                $values = array(
                    'cmd_id' => $this->getCmd_id(),
                    'datetime' => $this->getDatetime(),
                );
                $sql = 'SELECT `value`
                FROM history
                WHERE cmd_id=:cmd_id
                AND `datetime`=:datetime';
                $result = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
                if ($result !== false) {
                    switch ($cmd->getConfiguration('historizeMode', 'avg')) {
                        case 'avg':
                            $this->setValue(($result['value'] + $this->getValue()) / 2);
                            break;
                        case 'min':
                            $this->setValue(min($result['value'], $this->getValue()));
                            break;
                        case 'max':
                            $this->setValue(max($result['value'], $this->getValue()));
                            break;
                    }
                    if ($result['value'] === $this->getValue()) {
                        return;
                    }
                }
            } else {
                $this->setDatetime(date('Y-m-d H:00:00', strtotime($this->getDatetime())));
            }
        }
        $values = array(
            'cmd_id' => $this->getCmd_id(),
            'datetime' => $this->getDatetime(),
            'value' => $this->getValue(),
        );
        if ($values['value'] === '') {
            $values['value'] = null;
        }
        $sql = 'REPLACE INTO ' . $this->getTableName() . '
        SET cmd_id=:cmd_id,
        `datetime`=:datetime,
        value=:value';
        DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
    }

    public function remove() {
        DB::remove($this);
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getCmd_id() {
        return $this->cmd_id;
    }

    public function getCmd() {
        return cmd::byId($this->cmd_id);
    }

    public function getValue() {
        return $this->value;
    }

    public function getDatetime() {
        return $this->datetime;
    }

    public function getTableName() {
        return $this->_tableName;
    }

    public function setTableName($_tableName) {
        $this->_tableName = $_tableName;
        return $this;
    }

    public function setCmd_id($cmd_id) {
        $this->cmd_id = $cmd_id;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function setDatetime($datetime) {
        $this->datetime = $datetime;
        return $this;
    }

}