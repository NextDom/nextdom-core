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

use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
use NextDom\Enums\DateFormat;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\History;
use NextDom\Model\Entity\HistoryArch;

/**
 * Class HistoryManager
 * @package NextDom\Managers
 */
class HistoryManager
{
    const CLASS_NAME = 'history';
    const DB_CLASS_NAME = '`history`';

    /**
     * @param $_source_id
     * @param $_target_id
     * @throws \Exception
     */
    public static function copyHistoryToCmd($_source_id, $_target_id)
    {
        $sourceCmd = CmdManager::byId(str_replace('#', '', $_source_id));
        if (!is_object($sourceCmd)) {
            throw new CoreException(__('La commande source n\'existe pas :') . ' ' . $_source_id);
        }
        if ($sourceCmd->getIsHistorized() != 1) {
            throw new CoreException(__('La commande source n\'est pas historisée'));
        }
        if ($sourceCmd->isType(CmdType::INFO)) {
            throw new CoreException(__('La commande source n\'est pas de type info'));
        }
        $targetCmd = CmdManager::byId(str_replace('#', '', $_target_id));
        if (!is_object($targetCmd)) {
            throw new CoreException(__('La commande cible n\'existe pas :') . ' ' . $_target_id);
        }
        if ($targetCmd->isType(CmdType::INFO)) {
            throw new CoreException(__('La commande cible n\'est pas de type info'));
        }
        if ($targetCmd->getSubType() != $sourceCmd->getSubType()) {
            throw new CoreException(__('Le sous-type de la commande cible n\'est pas le même que celui de la commande source'));
        }
        if ($targetCmd->getIsHistorized() != 1) {
            $targetCmd->setIsHistorized(1);
            $targetCmd->save();
        }
        $values = [
            'source_id' => $sourceCmd->getId(),
        ];
        $sql = 'REPLACE INTO ' . self::DB_CLASS_NAME . ' (`cmd_id`,`datetime`,`value`)
                SELECT ' . $targetCmd->getId() . ',`datetime`,`value` FROM ' . self::DB_CLASS_NAME . ' WHERE cmd_id=:source_id';
        DBHelper::exec($sql, $values);

        $sql = 'REPLACE INTO `historyArch` (`cmd_id`,`datetime`,`value`)
                SELECT ' . $targetCmd->getId() . ',`datetime`,`value` FROM `historyArch` WHERE cmd_id=:source_id';
        DBHelper::exec($sql, $values);
    }

    /**
     * @param $_cmd_id
     * @param $_startTime
     * @param null $_endTime
     * @param null $_oldValue
     * @return array|mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function byCmdIdDatetime($_cmd_id, $_startTime, $_endTime = null, $_oldValue = null)
    {
        if ($_endTime == null) {
            $_endTime = $_startTime;
        }
        $values = [
            'cmd_id' => $_cmd_id,
            'startTime' => $_startTime,
            'endTime' => $_endTime,
        ];
        if ($_oldValue != null) {
            $values['oldValue'] = $_oldValue;
        }
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM history
        WHERE cmd_id=:cmd_id
        AND `datetime`>=:startTime
        AND `datetime`<=:endTime';
        if ($_oldValue != null) {
            $sql .= ' AND `value`=:oldValue';
        }
        $result = DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
        if (!is_object($result)) {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
            FROM historyArch
            WHERE cmd_id=:cmd_id
            AND `datetime`>=:startTime
            AND `datetime`<=:endTime';
            if ($_oldValue != null) {
                $sql .= ' AND `value`=:oldValue';
            }
            $result = DBHelper::getOneObject($sql, $values, HistoryArch::class);
            if (is_object($result)) {
                $result->setTableName('historyArch');
            }
        } else {
            $result->setTableName('history');
        }
        return $result;
    }

    /**
     * Archive les données de history dans historyArch
     */
    public static function archive()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $sql = 'DELETE FROM history WHERE `datetime` <= "2000-01-01 01:00:00" OR  `datetime` >= "2020-01-01 01:00:00"';
        DBHelper::exec($sql);
        $sql = 'DELETE FROM historyArch WHERE `datetime` <= "2000-01-01 01:00:00" OR  `datetime` >= "2020-01-01 01:00:00"';
        DBHelper::exec($sql);
        $sql = 'DELETE FROM history WHERE `value` IS NULL';
        DBHelper::exec($sql);
        $sql = 'DELETE FROM historyArch WHERE `value` IS NULL';
        DBHelper::exec($sql);
        if (ConfigManager::byKey('historyArchivePackage') >= ConfigManager::byKey('historyArchiveTime')) {
            ConfigManager::save('historyArchivePackage', ConfigManager::byKey('historyArchiveTime') - 1);
        }
        $archiveDatetime = (ConfigManager::byKey('historyArchiveTime') < 1) ? date(DateFormat::FULL, strtotime('- 1 hours')) : date(DateFormat::FULL, strtotime('- ' . ConfigManager::byKey('historyArchiveTime', 'core', 1) . ' hours'));
        if (ConfigManager::byKey('historyArchivePackage') < 1) {
            $archivePackage = '00:' . ConfigManager::byKey('historyArchivePackage') * 60 . ':00';
        } else {
            $archivePackage = ConfigManager::byKey('historyArchivePackage') . ':00:00';
            if (strlen($archivePackage) < 8) {
                $archivePackage = '0' . $archivePackage;
            }
        }
        if ($archiveDatetime === false) {
            $archiveDatetime = date(DateFormat::FULL, strtotime('- 1 hours'));
        }
        $values = [
            'archiveDatetime' => $archiveDatetime,
        ];
        $sql = 'SELECT DISTINCT(cmd_id)
        FROM history
        WHERE `datetime`<:archiveDatetime';
        $list_sensors = DBHelper::getAll($sql, $values);
        foreach ($list_sensors as $sensors) {
            $cmd = CmdManager::byId($sensors['cmd_id']);
            if (!is_object($cmd) || !$cmd->isType(CmdType::INFO) || $cmd->getIsHistorized() != 1) {
                continue;
            }
            if ($cmd->getConfiguration('historyPurge', '') != '') {
                $purgeTime = date(DateFormat::FULL, strtotime($cmd->getConfiguration('historyPurge', '')));
                if ($purgeTime !== false) {
                    $values = [
                        'cmd_id' => $cmd->getId(),
                        'datetime' => $purgeTime,
                    ];
                    $sql = 'DELETE FROM historyArch WHERE cmd_id=:cmd_id AND `datetime` < :datetime';
                    DBHelper::exec($sql, $values);
                }
            }
            if (!$NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['canBeSmooth'] || $cmd->getConfiguration('historizeMode', 'avg') == 'none') {
                $values = [
                    'cmd_id' => $cmd->getId(),
                ];
                $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM history
                    WHERE cmd_id=:cmd_id ORDER BY `datetime` ASC';
                $history = DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);

                $countHistory = count($history);

                for ($i = 1; $i < $countHistory; $i++) {
                    if ($history[$i]->getValue() != $history[$i - 1]->getValue()) {
                        $history[$i]->setTableName('historyArch');
                        $history[$i]->save();
                        $history[$i]->setTableName('history');
                    }
                    $history[$i]->remove();
                }
                $history[0]->setTableName('historyArch');
                $history[0]->save();
                $history[0]->setTableName('history');
                $history[0]->remove();
                $values = [
                    'cmd_id' => $cmd->getId(),
                ];
                $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM historyArch
                    WHERE cmd_id=:cmd_id ORDER BY datetime ASC';
                $history = DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
                $countHistory = count($history);
                for ($i = 1; $i < $countHistory; $i++) {
                    if ($history[$i]->getValue() == $history[$i - 1]->getValue()) {
                        $history[$i]->setTableName('historyArch');
                        $history[$i]->remove();
                    }
                }
                continue;
            }
            $values = [
                'cmd_id' => $sensors['cmd_id'],
                'archiveDatetime' => $archiveDatetime,
            ];
            $sql = 'SELECT MIN(`datetime`) as oldest
                    FROM history
                    WHERE `datetime`<:archiveDatetime
                    AND cmd_id=:cmd_id';
            $oldest = DBHelper::getOne($sql, $values);

            $mode = $cmd->getConfiguration('historizeMode', 'avg');

            while ($oldest['oldest'] !== null) {
                $values = [
                    'cmd_id' => $sensors['cmd_id'],
                    'oldest' => $oldest['oldest'],
                    'archivePackage' => '-' . $archivePackage,
                ];

                $sql = 'SELECT ' . $mode . '(CAST(value AS DECIMAL(12,2))) as value,
                        FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(`datetime`))) as datetime
                        FROM history
                        WHERE addtime(`datetime`,:archivePackage)<:oldest
                        AND cmd_id=:cmd_id';
                $avg = DBHelper::getOne($sql, $values);

                $history = new History();
                $history->setCmd_id($sensors['cmd_id']);
                $history->setValue($avg['value']);
                $history->setDatetime($avg['datetime']);
                $history->setTableName('historyArch');
                $history->save();

                $values = [
                    'cmd_id' => $sensors['cmd_id'],
                    'oldest' => $oldest['oldest'],
                    'archivePackage' => '-' . $archivePackage,
                ];
                $sql = 'DELETE FROM history
                        WHERE addtime(`datetime`,:archivePackage)<:oldest
                        AND cmd_id=:cmd_id';
                DBHelper::exec($sql, $values);

                $values = [
                    'cmd_id' => $sensors['cmd_id'],
                    'archiveDatetime' => $archiveDatetime,
                ];
                $sql = 'SELECT MIN(`datetime`) as oldest
                        FROM history
                        WHERE `datetime`<:archiveDatetime
                        AND cmd_id=:cmd_id';
                $oldest = DBHelper::getOne($sql, $values);
            }
        }
    }

    /**
     * @param $_cmd_id
     * @param null $_startTime
     * @param null $_endTime
     * @return bool
     * @throws CoreException
     */
    public static function removes($_cmd_id, $_startTime = null, $_endTime = null)
    {
        $values = [
            'cmd_id' => $_cmd_id,
        ];
        if ($_startTime !== null) {
            $values['startTime'] = $_startTime;
        }
        if ($_endTime !== null) {
            $values['endTime'] = $_endTime;
        }

        $sql = 'DELETE FROM history
            WHERE cmd_id=:cmd_id ';
        if ($_startTime !== null) {
            $sql .= ' AND datetime>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND datetime<=:endTime';
        }
        $sql .= ' ORDER BY `datetime` ASC';
        DBHelper::exec($sql, $values);

        $sql = 'DELETE FROM historyArch
            WHERE cmd_id=:cmd_id ';
        if ($_startTime !== null) {
            $sql .= ' AND `datetime`>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND `datetime`<=:endTime';
        }
        $sql .= ' ORDER BY `datetime` ASC';
        DBHelper::exec($sql, $values);
        return true;
    }

    /**
     * @param $_cmd_id
     * @param null $_startTime
     * @param null $_endTime
     * @param string $_period
     * @param int $_offset
     * @return array|mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function getPlurality($_cmd_id, $_startTime = null, $_endTime = null, $_period = 'day', $_offset = 0)
    {
        $values = [
            'cmd_id' => $_cmd_id,
        ];
        if ($_startTime !== null) {
            $values['startTime'] = $_startTime;
        }
        if ($_endTime !== null) {
            $values['endTime'] = $_endTime;
        }
        switch ($_period) {
            case 'day':
                if ($_offset == 0) {
                    $select = 'SELECT cmd_id,MAX(CAST(value AS DECIMAL(12,2))) as `value`,DATE_FORMAT(`datetime`,"%Y-%m-%d 00:00:00") as `datetime`';
                } elseif ($_offset > 0) {
                    $select = 'SELECT cmd_id,MAX(CAST(value AS DECIMAL(12,2))) as `value`,DATE_FORMAT(DATE_ADD(`datetime`, INTERVAL ' . $_offset . ' HOUR),"%Y-%m-%d 00:00:00") as `datetime`';
                } else {
                    $select = 'SELECT cmd_id,MAX(CAST(value AS DECIMAL(12,2))) as `value`,DATE_FORMAT(DATE_SUB(`datetime`, INTERVAL ' . abs($_offset) . ' HOUR),"%Y-%m-%d 00:00:00") as `datetime`';
                }
                break;
            case 'month':
                $select = 'SELECT cmd_id,MAX(CAST(value AS DECIMAL(12,2))) as `value`,DATE_FORMAT(`datetime`,"%Y-%m-01 00:00:00") as `datetime`';
                break;
            case 'year':
                $select = 'SELECT cmd_id,MAX(CAST(value AS DECIMAL(12,2))) as `value`,DATE_FORMAT(`datetime`,"%Y-01-01 00:00:00") as `datetime`';
                break;
            default:
                $select = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME);
                break;
        }
        switch ($_period) {
            case 'day':
                if ($_offset == 0) {
                    $groupBy = ' GROUP BY date(`datetime`)';
                } elseif ($_offset > 0) {
                    $groupBy = ' GROUP BY date(DATE_ADD(`datetime`, INTERVAL ' . $_offset . ' HOUR))';
                } else {
                    $groupBy = ' GROUP BY date(DATE_SUB(`datetime`, INTERVAL ' . abs($_offset) . ' HOUR))';
                }
                break;
            case 'month':
                $groupBy = ' GROUP BY month(DATE_ADD(`datetime`, INTERVAL ' . $_offset . ' DAY))';
                break;
            case 'year':
                $groupBy = ' GROUP BY YEAR(DATE_ADD(`datetime`, INTERVAL ' . $_offset . ' MONTH))';
                break;
            default:
                $groupBy = '';
                break;
        }
        $sql = $select . '
            FROM (
        ' . $select . '
        FROM history
        WHERE cmd_id=:cmd_id ';
        if ($_startTime !== null) {
            $sql .= ' AND datetime>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND datetime<=:endTime';
        }
        $sql .= $groupBy;
        $sql .= ' UNION ALL
        ' . $select . '
        FROM historyArch
        WHERE cmd_id=:cmd_id';
        if ($_startTime !== null) {
            $sql .= ' AND `datetime`>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND `datetime`<=:endTime';
        }
        $sql .= $groupBy;
        $sql .= ' ) as dt ';
        $sql .= $groupBy;
        $sql .= ' ORDER BY `datetime` ASC ';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_cmd_id
     * @param $_startTime
     * @param $_endTime
     * @return float|int|null
     * @throws \Exception
     */
    public static function getTemporalAvg($_cmd_id, $_startTime, $_endTime)
    {
        $histories = self::all($_cmd_id, $_startTime, $_endTime);
        $result = null;
        $start = null;
        $cTime = null;
        $cValue = null;
        $sum = 0;
        foreach ($histories as $history) {
            if ($start == null) {
                $cValue = $history->getValue();
                $cTime = strtotime($history->getDatetime());
                $start = $cTime;
                continue;
            }
            $sum += $cValue * (strtotime($history->getDatetime()) - $cTime);
            $cValue = $history->getValue();
            $cTime = strtotime($history->getDatetime());
        }
        $result = $sum / ($cTime - $start);
        return $result;
    }

    /**
     *
     * @param $_cmd_id
     * @param null $_startTime
     * @param null $_endTime
     * @return History[] des valeurs de l'équipement
     * @throws \Exception
     */
    public static function all($_cmd_id, $_startTime = null, $_endTime = null)
    {
        $values = [
            'cmd_id' => $_cmd_id,
        ];
        if ($_startTime !== null) {
            $values['startTime'] = $_startTime;
        }
        if ($_endTime !== null) {
            $values['endTime'] = $_endTime;
        }

        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
            FROM history
            WHERE cmd_id=:cmd_id ';
        if ($_startTime !== null) {
            $sql .= ' AND datetime>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND datetime<=:endTime';
        }
        $sql .= ' ORDER BY `datetime` ASC';
        $result1 = DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);

        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
            FROM historyArch
            WHERE cmd_id=:cmd_id ';
        if ($_startTime !== null) {
            $sql .= ' AND `datetime`>=:startTime';
        }
        if ($_endTime !== null) {
            $sql .= ' AND `datetime`<=:endTime';
        }
        $sql .= ' ORDER BY `datetime` ASC';
        $result2 = DBHelper::getAllObjects($sql, $values, HistoryArch::class);

        return array_merge($result2, $result1);
    }

    /**
     * @param $_cmd_id
     * @param $_startTime
     * @param $_endTime
     * @return array
     * @throws CoreException
     */
    public static function getStatistics($_cmd_id, $_startTime, $_endTime)
    {
        $values = [
            'cmd_id' => $_cmd_id,
            'startTime' => $_startTime,
            'endTime' => $_endTime,
        ];
        $sql = 'SELECT AVG(CAST(value AS DECIMAL(12,2))) as avg, MIN(CAST(value AS DECIMAL(12,2))) as min, MAX(CAST(value AS DECIMAL(12,2))) as max, SUM(CAST(value AS DECIMAL(12,2))) as sum, COUNT(CAST(value AS DECIMAL(12,2))) as count, STD(CAST(value AS DECIMAL(12,2))) as std, VARIANCE(CAST(value AS DECIMAL(12,2))) as variance
        FROM (
            SELECT *
            FROM history
            WHERE cmd_id=:cmd_id
            AND `datetime`>=:startTime
            AND `datetime`<=:endTime
            UNION ALL
            SELECT *
            FROM historyArch
            WHERE cmd_id=:cmd_id
            AND `datetime`>=:startTime
            AND `datetime`<=:endTime
        ) as dt';
        $result = DBHelper::getOne($sql, $values);
        if (!is_array($result)) {
            $result = [];
        }
        $values = [
            'cmd_id' => $_cmd_id,
            'startTime' => $_startTime,
            'endTime' => $_endTime,
        ];
        $sql = 'SELECT value as `last`
        FROM (
            SELECT *
            FROM history
            WHERE cmd_id=:cmd_id
            AND `datetime`>=:startTime
            AND `datetime`<=:endTime
            UNION ALL
            SELECT *
            FROM historyArch
            WHERE cmd_id=:cmd_id
            AND `datetime`>=:startTime
            AND `datetime`<=:endTime
        ) as dt
        ORDER BY `datetime` DESC
        LIMIT 1';
        $result2 = DBHelper::getOne($sql, $values);
        if (!is_array($result2)) {
            $result2 = [];
        }
        $return = array_merge($result, $result2);
        foreach ($return as $key => &$value) {
            if ($value === '') {
                $value = 0;
            }
        }
        return $return;
    }

    /**
     * @param $_cmd_id
     * @param $_startTime
     * @param $_endTime
     * @return float|int
     * @throws \Exception
     */
    public static function getTendance($_cmd_id, $_startTime, $_endTime)
    {
        $values = [];
        foreach (self::all($_cmd_id, $_startTime, $_endTime) as $history) {
            $values[] = $history->getValue();
        }
        if (count($values) == 0) {
            $x_mean = 0;
        } else {
            $x_mean = array_sum(array_keys($values)) / count($values);
        }
        if (count($values) == 0) {
            $y_mean = 0;
        } else {
            $y_mean = array_sum($values) / count($values);
        }
        $base = 0.0;
        $divisor = 0.0;
        foreach ($values as $i => $value) {
            $base += ($i - $x_mean) * ($value - $y_mean);
            $divisor += ($i - $x_mean) * ($i - $x_mean);
        }
        if ($divisor == 0) {
            return 0;
        }
        return ($base / $divisor);
    }

    /**
     * @param $_cmd_id
     * @param null $_value
     * @return false|int
     * @throws CoreException
     */
    public static function stateDuration($_cmd_id, $_value = null)
    {
        $cmd = CmdManager::byId($_cmd_id);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande introuvable : ') . $_cmd_id);
        }
        if ($cmd->getIsHistorized() != 1) {
            return -2;
        }
        $histories = array_reverse(self::all($_cmd_id));
        $c = count($histories);
        if ($c == 0) {
            return -1;
        }
        $currentValue = $histories[0]->getValue();
        for ($i = 0; $i < $c - 1; $i++) {
            $nextValue = $histories[$i + 1]->getValue();
            if ($currentValue != $nextValue) {
                break;
            }
        }
        $dateTo = date(DateFormat::FULL);
        $duration = strtotime($dateTo) - strtotime($histories[$i]->getDatetime());
        return $duration;
    }

    /**
     * @param $_cmd_id
     * @param null $_value
     * @return false|int
     * @throws \Exception
     */
    public static function lastStateDuration($_cmd_id, $_value = null)
    {
        $cmd = CmdManager::byId($_cmd_id);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande introuvable : ') . $_cmd_id);
        }
        if ($cmd->getIsHistorized() != 1) {
            return -2;
        }
        $_value = str_replace(',', '.', $_value);
        $_value = trim($_value);
        $_decimal = strlen(substr(strrchr($_value, '.'), 1));
        $histories = array_reverse(self::all($_cmd_id));
        $c = count($histories);
        if ($c == 0) {
            return -1;
        }
        $currentValue = $histories[0]->getValue();
        if (is_numeric($_value)) {
            $currentValue = round($currentValue, $_decimal);
        }
        $duration = 0;
        $dateTo = date(DateFormat::FULL);
        if ($_value === null || $_value == $currentValue) {
            $_value = $currentValue;
            $duration = strtotime($dateTo) - strtotime($histories[0]->getDatetime());
        }
        $started = 0;
        for ($i = 0; $i < $c; $i++) {
            $history = $histories[$i];
            $value = $history->getValue();
            if (is_numeric($_value)) {
                $value = round($value, $_decimal);
            }
            $date = $history->getDatetime();
            //same state as current:
            if ($_value == $currentValue && $_value != $value) {
                return $duration;
            }
            //different state as current:
            if ($_value != $currentValue && $i > 0) {
                $prevValue = $histories[$i - 1]->getValue();
                if (is_numeric($_value)) {
                    $prevValue = round($prevValue, $_decimal);
                }
                if ($_value == $value && $_value != $prevValue) {
                    $started = 1;
                    $duration = 0;
                }
                if ($_value != $value && $started == 1) {
                    return $duration;
                }
            }
            if ($i > 0) {
                $duration += strtotime($histories[$i - 1]->getDatetime()) - strtotime($date);
            }
        }
        return -1;
    }

    /**
     * Fonction renvoie la durée depuis le dernier changement d'état
     * à la valeur passée en paramètre
     * @param $_cmd_id
     * @param $_value
     * @return false|int
     * @throws CoreException
     */
    public static function lastChangeStateDuration($_cmd_id, $_value)
    {
        $cmd = CmdManager::byId($_cmd_id);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande introuvable : ') . $_cmd_id);
        }
        if ($cmd->getIsHistorized() != 1) {
            return -2;
        }
        $_value = str_replace(',', '.', $_value);
        $_value = trim($_value);
        $_decimal = strlen(substr(strrchr($_value, '.'), 1));
        $histories = array_reverse(self::all($_cmd_id));
        $c = count($histories);
        if ($c == 0) {
            return -1;
        }
        $currentValue = $histories[0]->getValue();
        if (is_numeric($_value)) {
            $currentValue = round($currentValue, $_decimal);
        }
        $dateTo = date(DateFormat::FULL);
        $duration = strtotime($dateTo) - strtotime($histories[0]->getDatetime());
        if ($_value === null) {
            $_value = $currentValue;
        }
        for ($i = 0; $i < $c - 1; $i++) {
            $history = $histories[$i];
            $value = $history->getValue();
            if (is_numeric($_value)) {
                $value = round($value, $_decimal);
            }
            $date = $history->getDatetime();
            //same state as current:
            if ($_value == $currentValue) {
                $nextValue = $histories[$i + 1]->getValue();
                if (is_numeric($_value)) {
                    $nextValue = round($nextValue, $_decimal);
                }
                if ($_value != $nextValue && isset($histories[$i - 1])) {
                    $duration += strtotime($histories[$i - 1]->getDatetime()) - strtotime($date);
                    return $duration;
                }
                if ($_value != $nextValue) {
                    return $duration;
                }
            }
            //different state as current:
            if ($_value != $currentValue && $i > 0) {
                $nextValue = $histories[$i + 1]->getValue();
                if (is_numeric($_value)) {
                    $nextValue = round($nextValue, $_decimal);
                }
                if ($_value == $value && $_value != $nextValue && isset($histories[$i - 1])) {
                    $duration += strtotime($histories[$i - 1]->getDatetime()) - strtotime($date);
                    return $duration;
                }
            }
            if ($i > 0) {
                $duration += strtotime($histories[$i - 1]->getDatetime()) - strtotime($date);
            }
        }
        return -1;
    }

    /**
     *
     * @param int $_cmd_id
     * @param null $_value
     * @param string $_startTime
     * @param string $_endTime
     * @return array
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function stateChanges($_cmd_id, $_value = null, $_startTime = null, $_endTime = null)
    {
        $cmd = CmdManager::byId($_cmd_id);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande introuvable : ') . $_cmd_id);
        }
        $_dateTime = '';
        if ($_startTime !== null) {
            $_dateTime = ' AND `datetime`>="' . $_startTime . '"';
        }

        if ($_endTime === null) {
            $_dateTime .= ' AND `datetime`<="' . date(DateFormat::FULL) . '"';
        } else {
            $_dateTime .= ' AND `datetime`<="' . $_endTime . '"';
        }

        if ($_value === null) {
            $_value = $cmd->execCmd();
        }
        if (!$cmd->isSubType(CmdSubType::STRING)) {
            $_value = str_replace(',', '.', $_value);
            $_decimal = strlen(substr(strrchr($_value, "."), 1));
            $_condition = ' ROUND(CAST(value AS DECIMAL(12,2)),' . $_decimal . ') = ' . $_value;
        } else {
            $_condition = ' value = ' . $_value;
        }

        $values = [
            'cmd_id' => $_cmd_id,
        ];
        $sql = 'SELECT count(*) as changes
                FROM (SELECT t1.*
                FROM (
                    SELECT *
                    FROM history
                    WHERE cmd_id=:cmd_id' . $_dateTime . '
                    UNION ALL
                    SELECT *
                    FROM historyArch
                    WHERE cmd_id=:cmd_id' . $_dateTime . '
                ) as t1
                WHERE cmd_id=:cmd_id' . $_dateTime . '
            ) as t1
            where ' . $_condition;
        $result = DBHelper::getOne($sql, $values);
        return $result['changes'];
    }

    /**
     * @param $_cmd_id
     * @param string $_date
     * @return array|mixed|null
     * @throws CoreException
     */
    /**
     * @param $_cmd_id
     * @param string $_date
     * @return array|mixed|null
     * @throws CoreException
     */
    /**
     * @param $_cmd_id
     * @param string $_date
     * @return array|mixed|null
     * @throws CoreException
     */
    public static function emptyHistory($_cmd_id, $_date = '')
    {
        $values = [
            'cmd_id' => $_cmd_id,
        ];
        if ($_date != '') {
            $values['date'] = $_date;
        }
        $sql = 'DELETE FROM history WHERE cmd_id=:cmd_id';
        if ($_date != '') {
            $sql .= ' AND `datetime` <= :date';
        }
        DBHelper::exec($sql, $values);
        $sql = 'DELETE FROM historyArch WHERE cmd_id=:cmd_id';
        if ($_date != '') {
            $sql .= ' AND `datetime` <= :date';
        }
        return DBHelper::getOne($sql, $values);
    }

    /**
     * @param $_strcalcul
     * @param null $_dateStart
     * @param null $_dateEnd
     * @param bool $_noCalcul
     * @return array
     * @throws \Exception
     */
    public static function getHistoryFromCalcul($_strcalcul, $_dateStart = null, $_dateEnd = null, $_noCalcul = false)
    {
        $now = strtotime('now');
        $value = [];
        $cmd_histories = [];
        preg_match_all("/#([0-9]*)#/", $_strcalcul, $matches);
        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $cmd_id) {
                if (is_numeric($cmd_id)) {
                    $cmd = CmdManager::byId($cmd_id);
                    if (is_object($cmd) && $cmd->getIsHistorized() == 1) {
                        $prevDatetime = null;
                        $prevValue = 0;
                        $histories_cmd = $cmd->getHistory($_dateStart, $_dateEnd);
                        $histories_cmd_count = count($histories_cmd);
                        for ($i = 0; $i < $histories_cmd_count; $i++) {
                            if (!isset($cmd_histories[$histories_cmd[$i]->getDatetime()])) {
                                $cmd_histories[$histories_cmd[$i]->getDatetime()] = [];
                            }
                            $cmd_histories[$histories_cmd[$i]->getDatetime()]['#' . $cmd_id . '#'] = $histories_cmd[$i]->getValue();

                        }
                    }
                }
            }
            ksort($cmd_histories);
            $prevData = null;
            foreach ($cmd_histories as $datetime => &$cmd_history) {
                if (count($matches[1]) != count($cmd_history)) {
                    if ($prevData == null) {
                        $prevData = $cmd_history;
                        continue;
                    }
                    foreach ($matches[1] as $cmd_id) {
                        if (!isset($cmd_history['#' . $cmd_id . '#']) && isset($prevData['#' . $cmd_id . '#'])) {
                            $cmd_history['#' . $cmd_id . '#'] = $prevData['#' . $cmd_id . '#'];
                        }
                    }
                }
                $prevData = $cmd_history;
                if (count($matches[1]) != count($cmd_history)) {
                    continue;
                }
                $datetime = floatval(strtotime($datetime));
                $calcul = Utils::templateReplace($cmd_history, $_strcalcul);
                if ($_noCalcul) {
                    $value[$datetime] = $calcul;
                    continue;
                }
                try {
                    $result = floatval(NextDomHelper::evaluateExpression($calcul));
                    $value[$datetime] = $result;
                } catch (\Exception $e) {

                }
            }
        } else {
            $value = $_strcalcul;
        }
        if (is_array($value)) {
            ksort($value);
        }
        return $value;
    }
}
