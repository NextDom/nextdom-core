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

use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Helpers\NextDomHelper;

class JeeObjectManager
{
    const DB_CLASS_NAME = '`object`';
    const CLASS_NAME = 'jeeObject';

    /**
     * Get an object by with his id.
     *
     * @param mixed $id Identifiant de l'objet
     * @return array|mixed|null|void
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        if ($id == '') {
            return;
        }
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get an object by with his name.
     *
     * @param $name
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byName($name)
    {
        $values = array(
            'name' => $name,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE name=:name';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get all objects.
     *
     * @param bool $onlyVisible Filter only visible objects
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function all($onlyVisible = false)
    {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . ' ';
        if ($onlyVisible) {
            $sql .= ' WHERE isVisible = 1';
        }
        $sql .= ' ORDER BY position,name,father_id';
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
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
    public static function rootObject($all = false, $onlyVisible = false)
    {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE father_id IS NULL';
        if ($onlyVisible) {
            $sql .= ' AND isVisible = 1';
        }
        $sql .= ' ORDER BY position';
        if ($all === false) {
            $sql .= ' LIMIT 1';
            return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
        }
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Build tree of all objects
     *
     * @param mixed $nodeObject Current root object
     * @param bool $visible Filter only visible objects
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function buildTree($nodeObject = null, $visible = true)
    {
        $result = array();
        if (!is_object($nodeObject)) {
            $objectsList = self::rootObject(true, $visible);
        } else {
            $objectsList = $nodeObject->getChild($visible);
        }
        if (is_array($objectsList) && count($objectsList) > 0) {
            foreach ($objectsList as $object) {
                $result[] = $object;
                $result = array_merge($result, self::buildTree($object, $visible));
            }
        }
        return $result;
    }

    /**
     * TODO: ???
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
    public static function fullData($restrict = array())
    {
        $result = array();
        foreach (self::all(true) as $object) {
            if (!isset($restrict['object']) || !is_array($restrict['object']) || isset($restrict['object'][$object->getId()])) {
                $object_return = \utils::o2a($object);
                $object_return['eqLogics'] = array();
                $objectGetEqLogic = $object->getEqLogic(true, true);
                foreach ($objectGetEqLogic as $eqLogic) {
                    if (!isset($restrict['eqLogic']) || !is_array($restrict['eqLogic']) || isset($restrict['eqLogic'][$eqLogic->getId()])) {
                        $eqLogic_return = \utils::o2a($eqLogic);
                        $eqLogic_return['cmds'] = [];
                        $eqLogicGetCmd = $eqLogic->getCmd();
                        foreach ($eqLogicGetCmd as $cmd) {
                            if (!isset($restrict['cmd']) || !is_array($restrict['cmd']) || isset($restrict['cmd'][$cmd->getId()])) {
                                $cmd_return = \utils::o2a($cmd);
                                if ($cmd->getType() == 'info') {
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
     * Search object configuration TODO: ??
     *
     * @param string $search
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function searchConfiguration(string $search)
    {
        $values = array(
            'configuration' => '%' . $search . '%',
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `configuration` LIKE :configuration';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * TODO: ???
     *
     * @return array
     * @throws \Exception
     */
    public static function deadCmd()
    {
        $result = array();
        foreach (self::all() as $object) {
            foreach ($object->getConfiguration('summary', []) as $key => $summary) {
                foreach ($summary as $cmdInfo) {
                    if (!CmdManager::byId(str_replace('#', '', $cmdInfo['cmd']))) {
                        $result[] = array('detail' => 'Résumé ' . $object->getName(), 'help' => \config::byKey('object:summary')[$key]['name'], 'who' => $cmdInfo['cmd']);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * TODO ???
     *
     * @param string $cmdId
     * @throws \Exception
     */
    public static function checkSummaryUpdate(string $cmdId)
    {
        $objects = self::searchConfiguration('#' . $cmdId . '#');
        if (count($objects) == 0) {
            return;
        }
        $toRefreshCmd = array();
        $global = array();
        foreach ($objects as $object) {
            $summaries = $object->getConfiguration('summary');
            if (!is_array($summaries)) {
                continue;
            }
            $event = array('object_id' => $object->getId(), 'keys' => array());
            foreach ($summaries as $key => $summary) {
                foreach ($summary as $cmd_info) {
                    preg_match_all("/#([0-9]*)#/", $cmd_info['cmd'], $matches);
                    foreach ($matches[1] as $cmd_id) {
                        if ($cmd_id == $cmdId) {
                            $value = $object->getSummary($key);
                            $event['keys'][$key] = array('value' => $value);
                            $toRefreshCmd[] = array('key' => $key, 'object' => $object, 'value' => $value);
                            if ($object->getConfiguration('summary::global::' . $key, 0) == 1) {
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
                    if ($object->getConfiguration('summary_virtual_id') == '') {
                        continue;
                    }
                    $virtual = EqLogicManager::byId($value['object']->getConfiguration('summary_virtual_id'));
                    if (!is_object($virtual)) {
                        $object->getConfiguration('summary_virtual_id', '');
                        $object->save();
                        continue;
                    }
                    $cmd = $virtual->getCmd('info', $value['key']);
                    if (!is_object($cmd)) {
                        continue;
                    }
                    $cmd->event($value['value']);
                } catch (\Exception $e) {

                }
            }
        }
        if (count($global) > 0) {
            $event = array('object_id' => 'global', 'keys' => array());
            foreach ($global as $key => $value) {
                try {
                    $result = self::getGlobalSummary($key);
                    if ($result === null) {
                        continue;
                    }
                    $event['keys'][$key] = array('value' => $result);
                    $virtual = EqLogicManager::byLogicalId('summaryglobal', 'virtual');
                    if (!is_object($virtual)) {
                        continue;
                    }
                    $cmd = $virtual->getCmd('info', $key);
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
            EventManager::adds('self::summary::update', $events);
        }
    }

    /**
     * TODO: ???
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
        $def = \config::byKey('object:summary');
        $objects = self::all();
        $value = array();
        foreach ($objects as $object) {
            if ($object->getConfiguration('summary::global::' . $key, 0) == 0) {
                continue;
            }
            $result = $object->getSummary($key, true);
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
     * TODO ???
     *
     * @param string $version
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getGlobalHtmlSummary($version = 'desktop')
    {
        $objects = self::all();
        $def = \config::byKey('object:summary');
        $values = array();
        $return = '<span class="objectSummaryglobal" data-version="' . $version . '">';
        foreach ($def as $key => $value) {
            foreach ($objects as $object) {
                if ($object->getConfiguration('summary::global::' . $key, 0) == 0) {
                    continue;
                }
                if (!isset($values[$key])) {
                    $values[$key] = array();
                }
                $result = $object->getSummary($key, true);
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
        return trim($return) . '</span>';
    }

    /**
     * TODO ???
     * 
     * @param string $key
     * @throws \Exception
     */
    public static function createSummaryToVirtual($key = '')
    {
        if ($key == '') {
            return;
        }
        $def = \config::byKey('object:summary');
        if (!isset($def[$key])) {
            return;
        }
        try {
            $plugin = PluginManager::byId('virtual');
            if (!is_object($plugin)) {
                $update = UpdateManager::byLogicalId('virtual');
                if (!is_object($update)) {
                    $update = new \update();
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
                $update = new \update();
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
            throw new \Exception(__('Le plugin virtuel doit être installé'));
        }
        if (!$plugin->isActive()) {
            throw new \Exception(__('Le plugin virtuel doit être actif'));
        }

        $virtual = EqLogicManager::byLogicalId('summaryglobal', 'virtual');
        if (!is_object($virtual)) {
            $virtual = new \virtual();
            $virtual->setName(__('Résumé Global'));
            $virtual->setIsVisible(0);
            $virtual->setIsEnable(1);
        }
        $virtual->setIsEnable(1);
        $virtual->setLogicalId('summaryglobal');
        $virtual->setEqType_name('virtual');
        $virtual->save();
        $cmd = $virtual->getCmd('info', $key);
        if (!is_object($cmd)) {
            $cmd = new \virtualCmd();
            $cmd->setName($def[$key]['name']);
            $cmd->setIsHistorized(1);
        }
        $cmd->setEqLogic_id($virtual->getId());
        $cmd->setLogicalId($key);
        $cmd->setType('info');
        if ($def[$key]['calcul'] == 'text') {
            $cmd->setSubtype('string');
        } else {
            $cmd->setSubtype('numeric');
        }
        $cmd->setUnite($def[$key]['unit']);
        $cmd->save();

        foreach (self::all() as $object) {
            $summaries = $object->getConfiguration('summary');
            if (!is_array($summaries)) {
                continue;
            }
            if (!isset($summaries[$key]) || !is_array($summaries[$key]) || count($summaries[$key]) == 0) {
                continue;
            }
            $virtual = EqLogicManager::byLogicalId('summary' . $object->getId(), 'virtual');
            if (!is_object($virtual)) {
                $virtual = new \virtual();
                $virtual->setName(__('Résumé'));
                $virtual->setIsVisible(0);
                $virtual->setIsEnable(1);
            }
            $virtual->setIsEnable(1);
            $virtual->setLogicalId('summary' . $object->getId());
            $virtual->setEqType_name('virtual');
            $virtual->setObject_id($object->getId());
            $virtual->save();
            $object->setConfiguration('summary_virtual_id', $virtual->getId());
            $object->save();
            $cmd = $virtual->getCmd('info', $key);
            if (!is_object($cmd)) {
                $cmd = new \virtualCmd();
                $cmd->setName($def[$key]['name']);
                $cmd->setIsHistorized(1);
            }
            $cmd->setEqLogic_id($virtual->getId());
            $cmd->setLogicalId($key);
            $cmd->setType('info');
            if ($def[$key]['calcul'] == 'text') {
                $cmd->setSubtype('string');
            } else {
                $cmd->setSubtype('numeric');
            }
            $cmd->setUnite($def[$key]['unit']);
            $cmd->save();
        }
    }
}
