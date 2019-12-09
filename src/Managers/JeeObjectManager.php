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

namespace NextDom\Managers;

use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
use NextDom\Enums\Common;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\JeeObject;
use NextDom\Model\Entity\Update;
use NextDom\Model\Entity\User;

/**
 * Class ObjectManager
 * @package NextDom\Managers
 */
class JeeObjectManager
{
    const DB_CLASS_NAME = '`object`';
    const CLASS_NAME = JeeObject::class;

    /**
     * Get an object by with his name.
     *
     * @param string $name
     * @return JeeObject|null
     * @throws \Exception
     */
    public static function byName($name)
    {
        $values = [
            'name' => $name,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE name=:name';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Build tree of all objects
     *
     * @param mixed $nodeObject Current root object
     * @param bool $visible Filter only visible objects
     *
     * @return JeeObject[]
     *
     * @throws \Exception
     */
    public static function buildTree($nodeObject = null, $visible = true)
    {
        $result = [];
        if (!is_object($nodeObject)) {
            $objectsList = self::getRootObjects(true, $visible);
        } else {
            $objectsList = $nodeObject->getChild($visible);
        }
        if (is_array($objectsList) && count($objectsList) > 0) {
            foreach ($objectsList as $jeeObject) {
                $result[] = $jeeObject;
                $result = array_merge($result, self::buildTree($jeeObject, $visible));
            }
        }
        return $result;
    }

    /**
     * Get root objects.
     *
     * @param bool $all False return only the first, True return all roots objects
     * @param bool $onlyVisible Filter only visible objects
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function getRootObjects($all = false, $onlyVisible = false)
    {
        $fetchType = DBHelper::FETCH_TYPE_ALL;
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE father_id IS NULL';
        if ($onlyVisible) {
            $sql .= ' AND isVisible = 1';
        }
        $sql .= ' ORDER BY position';
        if ($all === false) {
            $sql .= ' LIMIT 1';
            $fetchType = DBHelper::FETCH_TYPE_ROW;
        }
        return DBHelper::Prepare($sql, [], $fetchType, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function getDefaultUserRoom(User $user)
    {
        $rootRoomId = $user->getOptions('defaultDashboardObject');
        if (empty($rootRoomId)) {
            $defaultRoom = self::getRootObjects();
        } else {
            $defaultRoom = self::byId($rootRoomId);
        }
        return $defaultRoom;
    }

    /**
     * Get an object by with his id.
     *
     * @param int $id Identifiant de l'objet
     * @return JeeObject|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        if ($id == '' || $id == -1) {
            return null;
        }
        $values = ['id' => $id];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @TODO: ???
     *
     * Data restrictions :
     *  - $restrict['object'][OBJECT_ID] : Skip objects
     *  -
     *
     * @param array $restrict Data restrictions
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function fullData($restrict = [])
    {
        $result = [];
        foreach (self::all(true) as $jeeObject) {
            if (!isset($restrict[NextDomObj::OBJECT]) || !is_array($restrict[NextDomObj::OBJECT]) || isset($restrict[NextDomObj::OBJECT][$jeeObject->getId()])) {
                $object_return = Utils::o2a($jeeObject);
                $object_return['eqLogics'] = [];
                $objectGetEqLogic = $jeeObject->getEqLogic(true, true);
                foreach ($objectGetEqLogic as $eqLogic) {
                    if (!isset($restrict[NextDomObj::EQLOGIC]) || !is_array($restrict[NextDomObj::EQLOGIC]) || isset($restrict[NextDomObj::EQLOGIC][$eqLogic->getId()])) {
                        $eqLogic_return = Utils::o2a($eqLogic);
                        $eqLogic_return['cmds'] = [];
                        $eqLogicGetCmd = $eqLogic->getCmd();
                        foreach ($eqLogicGetCmd as $cmd) {
                            if (!isset($restrict[NextDomObj::CMD]) || !is_array($restrict[NextDomObj::CMD]) || isset($restrict[NextDomObj::CMD][$cmd->getId()])) {
                                $cmd_return = Utils::o2a($cmd);
                                if ($cmd->isType(CmdType::INFO)) {
                                    $cmd_return['state'] = $cmd->execCmd();
                                }
                                $eqLogic_return['cmds'][] = $cmd_return;
                            }
                        }
                        $object_return['eqLogics'][] = $eqLogic_return;
                    }
                }
                $result[] = $object_return;
            }
        }
        return $result;
    }

    /**
     * Get all objects.
     *
     * @param bool $onlyVisible Filter only visible objects
     *
     * @return JeeObject[]|null
     *
     * @throws \Exception
     */
    public static function all($onlyVisible = false)
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . ' ';
        if ($onlyVisible) {
            $sql .= ' WHERE isVisible = 1';
        }
        $sql .= ' ORDER BY position,name,father_id';
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     * @TODO: ???
     *
     * @return array
     * @throws \Exception
     */
    public static function deadCmd()
    {
        $result = [];
        foreach (self::all() as $jeeObject) {
            foreach ($jeeObject->getConfiguration(Common::SUMMARY, []) as $key => $summary) {
                foreach ($summary as $cmdInfo) {
                    if (!CmdManager::byId(str_replace('#', '', $cmdInfo['cmd']))) {
                        $result[] = ['detail' => 'Résumé ' . $jeeObject->getName(), 'help' => ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY)[$key]['name'], 'who' => $cmdInfo['cmd']];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @TODO ???
     *
     * @param string $cmdId
     * @throws \Exception
     */
    public static function checkSummaryUpdate(string $cmdId)
    {
        $jeeObjects = self::searchConfiguration('#' . $cmdId . '#');
        if (count($jeeObjects) == 0) {
            return;
        }
        $toRefreshCmd = [];
        $global = [];
        foreach ($jeeObjects as $jeeObject) {
            $summaries = $jeeObject->getConfiguration(Common::SUMMARY);
            if (!is_array($summaries)) {
                continue;
            }
            $event = [Common::OBJECT_ID => $jeeObject->getId(), Common::KEYS => []];
            foreach ($summaries as $key => $summary) {
                foreach ($summary as $cmd_info) {
                    preg_match_all("/#([0-9]*)#/", $cmd_info['cmd'], $matches);
                    foreach ($matches[1] as $cmd_id) {
                        if ($cmd_id == $cmdId) {
                            $value = $jeeObject->getSummary($key);
                            $event['keys'][$key] = ['value' => $value];
                            $toRefreshCmd[] = ['key' => $key, 'object' => $jeeObject, 'value' => $value];
                            if ($jeeObject->getConfiguration('summary::global::' . $key, 0) == 1) {
                                $global[$key] = 1;
                            }
                        }
                    }
                }
            }
            $events[] = $event;
        }
        if (count($toRefreshCmd) > 0) {
            foreach ($toRefreshCmd as $value) {
                try {
                    $value['object']->setCache('summaryHtmldesktop', '');
                    $value['object']->setCache('summaryHtmlmobile', '');
                    if ($value['object']->getConfiguration('summary_virtual_id') == '') {
                        continue;
                    }
                    $virtual = EqLogicManager::byId($value['object']->getConfiguration('summary_virtual_id'));
                    if (!is_object($virtual)) {
                        $jeeObject->getConfiguration('summary_virtual_id', '');
                        $jeeObject->save();
                        continue;
                    }
                    $cmd = $virtual->getCmd(CmdType::INFO, $value['key']);
                    if (!is_object($cmd)) {
                        continue;
                    }
                    $cmd->event($value['value']);
                } catch (\Exception $e) {
                }
            }
        }
        $events = [];
        if (count($global) > 0) {
            CacheManager::set('globalSummaryHtmldesktop', '');
            CacheManager::set('globalSummaryHtmlmobile', '');
            $event = [Common::OBJECT_ID => 'global', Common::KEYS => []];
            foreach ($global as $key => $value) {
                try {
                    $result = JeeObjectManager::getGlobalSummary($key);
                    if ($result === null) {
                        continue;
                    }
                    $event['keys'][$key] = ['value' => $result];
                    $virtual = EqLogicManager::byLogicalId('summaryglobal', 'virtual');
                    if (!is_object($virtual)) {
                        continue;
                    }
                    $cmd = $virtual->getCmd(CmdType::INFO, $key);
                    if (!is_object($cmd)) {
                        continue;
                    }
                    $cmd->event($result);
                } catch (\Exception $e) {
                }
            }
            $events[] = $event;
        }
        if (count($events) > 0) {
            EventManager::adds('jeeObject::summary::update', $events);
        }
    }

    /**
     * Search object configuration @TODO: ??
     *
     * @param string $search
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function searchConfiguration(string $search)
    {
        $values = [
            'configuration' => '%' . $search . '%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `configuration` LIKE :configuration';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @TODO: ???
     *
     * @param string $key
     * @return float|null|string
     * @throws \Exception
     */
    public static function getGlobalSummary(string $key)
    {
        if ($key == '') {
            return null;
        }
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        $jeeObjects = self::all();
        $value = [];
        foreach ($jeeObjects as $jeeObject) {
            if ($jeeObject->getConfiguration('summary::global::' . $key, 0) == 0) {
                continue;
            }
            $result = $jeeObject->getSummary($key, true);
            if ($result === null || !is_array($result)) {
                continue;
            }
            $value = array_merge($value, $result);
        }
        if (count($value) == 0) {
            return null;
        }
        if ($def[$key]['calcul'] == 'text') {
            return trim(implode(',', $value), ',');
        }
        return round(NextDomHelper::calculStat($def[$key]['calcul'], $value), 1);
    }

    /**
     * @TODO ???
     *
     * @param string $version
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getGlobalHtmlSummary($version = 'desktop')
    {
        $cache = CacheManager::byKey('globalSummaryHtml' . $version);
        if ($cache->getValue() != '') {
            return $cache->getValue();
        }
        $jeeObjects = self::all();
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        $values = [];
        $return = '<span class="objectSummaryglobal" data-version="' . $version . '">';
        foreach ($def as $key => $value) {
            foreach ($jeeObjects as $jeeObject) {
                if ($jeeObject->getConfiguration('summary::global::' . $key, 0) == 0) {
                    continue;
                }
                if (!isset($values[$key])) {
                    $values[$key] = [];
                }
                $result = $jeeObject->getSummary($key, true);
                if ($result === null || !is_array($result)) {
                    continue;
                }
                $values[$key] = array_merge($values[$key], $result);
            }
        }
        $margin = ($version == 'desktop') ? 4 : 2;

        foreach ($values as $key => $value) {
            if (count($value) == 0) {
                continue;
            }
            $style = '';
            $allowDisplayZero = $def[$key]['allowDisplayZero'];
            if ($def[$key]['calcul'] == 'text') {
                $result = trim(implode(',', $value), ',');
                $allowDisplayZero = 1;
            } else {
                $result = round(NextDomHelper::calculStat($def[$key]['calcul'], $value), 1);

            }
            if ($allowDisplayZero == 0 && $result == 0) {
                $style = 'display:none;';
            }
            $return .= '<span class="objectSummaryParent cursor" data-summary="' . $key . '" data-object_id="" style="margin-right:' . $margin . 'px;' . $style . '" data-displayZeroValue="' . $allowDisplayZero . '">';
            $return .= $def[$key]['icon'] . ' <sup><span class="objectSummary' . $key . '">' . $result . '</span> ' . $def[$key]['unit'] . '</sup>';
            $return .= '</span>';
        }
        $return = trim($return) . '</span>';
        CacheManager::set('globalSummaryHtml' . $version, $return);
        return $return;
    }

    /**
     * @TODO ???
     *
     * @param string $key
     * @throws \Throwable
     */
    public static function createSummaryToVirtual($key = '')
    {
        if ($key == '') {
            return;
        }
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        if (!isset($def[$key])) {
            return;
        }
        try {
            $plugin = PluginManager::byId('virtual');
            if (!is_object($plugin)) {
                $update = UpdateManager::byLogicalId('virtual');
                if (!is_object($update)) {
                    $update = new Update();
                }
                $update->setLogicalId('virtual');
                $update->setSource('market');
                $update->setConfiguration('version', 'stable');
                $update->save();
                $update->doUpdate();
                sleep(2);
                $plugin = PluginManager::byId('virtual');
            }
        } catch (\Exception $e) {
            $update = UpdateManager::byLogicalId('virtual');
            if (!is_object($update)) {
                $update = new Update();
            }
            $update->setLogicalId('virtual');
            $update->setSource('market');
            $update->setConfiguration('version', 'stable');
            $update->save();
            $update->doUpdate();
            sleep(2);
            $plugin = PluginManager::byId('virtual');
        }
        if (!$plugin->isActive()) {
            $plugin->setIsEnable(1);
        }
        if (!is_object($plugin)) {
            throw new CoreException(__('Le plugin virtuel doit être installé'));
        }
        if (!$plugin->isActive()) {
            throw new CoreException(__('Le plugin virtuel doit être actif'));
        }

        $virtual = EqLogicManager::byLogicalId('summaryglobal', 'virtual');
        if (!is_object($virtual)) {
            /** @noinspection PhpUndefinedClassInspection */
            $virtual = new \virtual();
            $virtual->setName(__('Résumé Global'));
            $virtual->setIsVisible(0);
            $virtual->setIsEnable(1);
        }
        $virtual->setIsEnable(1);
        $virtual->setLogicalId('summaryglobal');
        $virtual->setEqType_name('virtual');
        $virtual->save();
        $cmd = $virtual->getCmd(CmdType::INFO, $key);
        if (!is_object($cmd)) {
            /** @noinspection PhpUndefinedClassInspection */
            $cmd = new \virtualCmd();
            $cmd->setName($def[$key]['name']);
            $cmd->setIsHistorized(1);
        }
        $cmd->setEqLogic_id($virtual->getId());
        $cmd->setLogicalId($key);
        $cmd->setType(CmdType::INFO);
        if ($def[$key]['calcul'] == 'text') {
            $cmd->setSubtype(CmdSubType::STRING);
        } else {
            $cmd->setSubtype(CmdSubType::NUMERIC);
        }
        $cmd->setUnite($def[$key]['unit']);
        $cmd->save();

        foreach (self::all() as $jeeObject) {
            $summaries = $jeeObject->getConfiguration(Common::SUMMARY);
            if (!is_array($summaries)) {
                continue;
            }
            if (!isset($summaries[$key]) || !is_array($summaries[$key]) || count($summaries[$key]) == 0) {
                continue;
            }
            $virtual = EqLogicManager::byLogicalId(Common::SUMMARY . $jeeObject->getId(), 'virtual');
            if (!is_object($virtual)) {
                /** @noinspection PhpUndefinedClassInspection */
                $virtual = new \virtual();
                $virtual->setName(__('Résumé'));
                $virtual->setIsVisible(0);
                $virtual->setIsEnable(1);
            }
            $virtual->setIsEnable(1);
            $virtual->setLogicalId(Common::SUMMARY . $jeeObject->getId());
            $virtual->setEqType_name('virtual');
            $virtual->setObject_id($jeeObject->getId());
            $virtual->save();
            $jeeObject->setConfiguration('summary_virtual_id', $virtual->getId());
            $jeeObject->save();
            $cmd = $virtual->getCmd(CmdType::INFO, $key);
            if (!is_object($cmd)) {
                /** @noinspection PhpUndefinedClassInspection */
                $cmd = new \virtualCmd();
                $cmd->setName($def[$key]['name']);
                $cmd->setIsHistorized(1);
            }
            $cmd->setEqLogic_id($virtual->getId());
            $cmd->setLogicalId($key);
            $cmd->setType(CmdType::INFO);
            if ($def[$key]['calcul'] == 'text') {
                $cmd->setSubtype(CmdSubType::STRING);
            } else {
                $cmd->setSubtype(CmdSubType::NUMERIC);
            }
            $cmd->setUnite($def[$key]['unit']);
            $cmd->save();
        }
    }
}
