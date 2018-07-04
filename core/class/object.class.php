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
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\ObjectManager;

class object {
    /*     * *************************Attributs****************************** */

    private $id;
    private $name;
    private $father_id = null;
    private $isVisible = 1;
    private $position;
    private $configuration;
    private $display;

    /*     * ***********************Méthodes statiques*************************** */

    public static function byId($_id) {
        return ObjectManager::byId($_id);
    }

    public static function byName($_name) {
        return ObjectManager::byName($_name);
    }

    public static function all($_onlyVisible = false) {
        return ObjectManager::all($_onlyVisible);
    }

    public static function rootObject($_all = false, $_onlyVisible = false) {
        return ObjectManager::rootObject($_all, $_onlyVisible);
    }

    public static function buildTree($_object = null, $_visible = true) {
        return ObjectManager::buildTree($_object, $_visible);
    }

    public static function fullData($_restrict = array()) {
        return ObjectManager::fullData($_restrict);
    }

    public static function searchConfiguration($_search) {
        return ObjectManager::searchConfiguration($_search);
    }

    public static function deadCmd() {
        return ObjectManager::deadCmd();
    }

    public static function checkSummaryUpdate($_cmd_id) {
        return ObjectManager::checkSummaryUpdate($_cmd_id);
    }

    public static function getGlobalSummary($_key) {
        return ObjectManager::getGlobalSummary($_key);
    }

    public static function getGlobalHtmlSummary($_key) {
        return ObjectManager::getGlobalHtmlSummary($_key);
    }

    public static function createSummaryToVirtual($_key = '') {
        return ObjectManager::createSummaryToVirtual($_key);
    }

    /*     * *********************Méthodes d'instance************************* */

    public function checkTreeConsistency($_fathers = array()) {
        $father = $this->getFather();
        if (!is_object($father)) {
            return;
        }
        if (in_array($this->getFather_id(), $_fathers)) {
            throw new Exception(__('Problème dans l\'arbre des objets', __FILE__));
        }
        $_fathers[] = $this->getId();

        $father->checkTreeConsistency($_fathers);
    }

    public function preSave() {
        if (is_numeric($this->getFather_id()) && $this->getFather_id() == $this->getId()) {
            throw new Exception(__('L\'objet ne peut pas être son propre père', __FILE__));
        }
        $this->checkTreeConsistency();
        $this->setConfiguration('parentNumber', $this->parentNumber());
        if ($this->getConfiguration('tagColor') == '') {
            $this->setConfiguration('tagColor', '#000000');
        }
        if ($this->getConfiguration('tagTextColor') == '') {
            $this->setConfiguration('tagTextColor', '#FFFFFF');
        }
        if ($this->getConfiguration('desktop::summaryTextColor') == '') {
            $this->setConfiguration('desktop::summaryTextColor', '');
        }
        if ($this->getConfiguration('mobile::summaryTextColor') == '') {
            $this->setConfiguration('mobile::summaryTextColor', '');
        }
    }

    public function save() {
        return DB::save($this);
    }

    public function getChild($_visible = true) {
        $values = array(
            'id' => $this->id,
        );
        $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
                FROM object
                WHERE father_id=:id';
        if ($_visible) {
            $sql .= ' AND isVisible=1 ';
        }
        $sql .= ' ORDER BY position';
        return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
    }

    public function getChilds() {
        $return = array();
        foreach ($this->getChild() as $child) {
            $return[] = $child;
            $return = array_merge($return, $child->getChilds());
        }
        return $return;
    }

    public function getEqLogic($_onlyEnable = true, $_onlyVisible = false, $_eqType_name = null, $_logicalId = null, $_searchOnchild = false) {
        $eqLogics = eqLogic::byObjectId($this->getId(), $_onlyEnable, $_onlyVisible, $_eqType_name, $_logicalId);
        if (is_array($eqLogics)) {
            foreach ($eqLogics as &$eqLogic) {
                $eqLogic->setObject($this);
            }
        }
        if ($_searchOnchild) {
            $child_object = jeeObject::buildTree($this);
            if (count($child_object) > 0) {
                foreach ($child_object as $object) {
                    $eqLogics = array_merge($eqLogics, $object->getEqLogic($_onlyEnable, $_onlyVisible, $_eqType_name, $_logicalId));
                }
            }
        }
        return $eqLogics;
    }

    public function getEqLogicBySummary($_summary = '', $_onlyEnable = true, $_onlyVisible = false, $_eqType_name = null, $_logicalId = null) {
        $def = config::byKey('object:summary');
        if ($_summary == '' || !isset($def[$_summary])) {
            return null;
        }
        $summaries = $this->getConfiguration('summary');
        if (!isset($summaries[$_summary])) {
            return array();
        }
        $eqLogics = eqLogic::byObjectId($this->getId(), $_onlyEnable, $_onlyVisible, $_eqType_name, $_logicalId);
        $eqLogics_id = array();
        foreach ($summaries[$_summary] as $infos) {
            $cmd = cmd::byId(str_replace('#', '', $infos['cmd']));
            if (is_object($cmd)) {
                $eqLogics_id[$cmd->getEqLogic_id()] = $cmd->getEqLogic_id();
            }
        }
        $return = array();
        if (is_array($eqLogics)) {
            foreach ($eqLogics as $eqLogic) {
                if (isset($eqLogics_id[$eqLogic->getId()])) {
                    $eqLogic->setObject($this);
                    $return[] = $eqLogic;
                }
            }
        }
        return $return;
    }

    public function getScenario($_onlyEnable = true, $_onlyVisible = false) {
        return scenario::byObjectId($this->getId(), $_onlyEnable, $_onlyVisible);
    }

    public function preRemove() {
        dataStore::removeByTypeLinkId('object', $this->getId());
    }

    public function remove() {
        return DB::remove($this);
    }

    public function getFather() {
        return self::byId($this->getFather_id());
    }

    public function parentNumber() {
        $father = $this->getFather();
        if (!is_object($father)) {
            return 0;
        }
        $fatherNumber = 0;
        while ($fatherNumber < 50) {
            $fatherNumber++;
            $father = $father->getFather();
            if (!is_object($father)) {
                return $fatherNumber;
            }
        }
        return 0;
    }

    public function getHumanName($_tag = false, $_prettify = false) {
        if ($_tag) {
            if ($_prettify) {
                if ($this->getDisplay('tagColor') != '') {
                    return '<span class="label" style="text-shadow : none;background-color:' . $this->getDisplay('tagColor') . ' !important;color:' . $this->getDisplay('tagTextColor', 'white') . ' !important">' . $this->getDisplay('icon') . ' ' . $this->getName() . '</span>';
                } else {
                    return '<span class="label label-primary" style="text-shadow : none;">' . $this->getDisplay('icon') . ' ' . $this->getName() . '</span>';
                }
            } else {
                return $this->getDisplay('icon') . ' ' . $this->getName();
            }
        } else {
            return $this->getName();
        }
    }

    public function getSummary($_key = '', $_raw = false) {
        $def = config::byKey('object:summary');
        if ($_key == '' || !isset($def[$_key])) {
            return null;
        }
        $summaries = $this->getConfiguration('summary');
        if (!isset($summaries[$_key])) {
            return null;
        }
        $values = array();
        foreach ($summaries[$_key] as $infos) {
            if (isset($infos['enable']) && $infos['enable'] == 0) {
                continue;
            }
            $value = nextdom::evaluateExpression(cmd::cmdToValue($infos['cmd']));
            if (isset($infos['invert']) && $infos['invert'] == 1) {
                $value = !$value;
            }
            if (isset($def[$_key]['count']) && $def[$_key]['count'] == 'binary' && $value > 1) {
                $value = 1;
            }
            $values[] = $value;
        }
        if (count($values) == 0) {
            return null;
        }
        if ($_raw) {
            return $values;
        }
        if ($def[$_key]['calcul'] == 'text') {
            return trim(implode(',', $values), ',');
        }
        return round(nextdom::calculStat($def[$_key]['calcul'], $values), 1);
    }

    public function getHtmlSummary($_version = 'desktop') {
        $return = '<span class="objectSummary' . $this->getId() . '" data-version="' . $_version . '">';
        foreach (config::byKey('object:summary') as $key => $value) {
            if ($this->getConfiguration('summary::hide::' . $_version . '::' . $key, 0) == 1) {
                continue;
            }
            $result = $this->getSummary($key);
            if ($result !== null) {
                $style = '';
                if ($_version == 'desktop') {
                    $style = 'color:' . $this->getDisplay($_version . '::summaryTextColor', '#000000') . ';';
                }
                $allowDisplayZero = $value['allowDisplayZero'];
                if ($value['calcul'] == 'text') {
                    $allowDisplayZero = 1;
                }
                if ($allowDisplayZero == 0 && $result == 0) {
                    $style = 'display:none;';
                }
                $return .= '<span style="margin-right:5px;' . $style . '" class="objectSummaryParent cursor" data-summary="' . $key . '" data-object_id="' . $this->getId() . '" data-displayZeroValue="' . $allowDisplayZero . '">' . $value['icon'] . ' <sup><span class="objectSummary' . $key . '">' . $result . '</span> ' . $value['unit'] . '</span></sup>';
            }
        }
        return trim($return) . '</span>';
    }

    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = null) {
        if ($_drill === null) {
            $_drill = config::byKey('graphlink::object::drill');
        }
        if (isset($_data['node']['object' . $this->getId()])) {
            return;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = findCodeIcon($this->getDisplay('icon'));
        $_data['node']['object' . $this->getId()] = array(
            'id' => 'object' . $this->getId(),
            'name' => $this->getName(),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'fontsize' => '4em',
            'texty' => -35,
            'textx' => 0,
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=object&id=' . $this->getId(),
        );
        $use = $this->getUse();
        addGraphLink($this, 'object', $this->getEqLogic(), 'eqLogic', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        addGraphLink($this, 'object', $use['cmd'], 'cmd', $_data, $_level, $_drill);
        addGraphLink($this, 'object', $use['scenario'], 'scenario', $_data, $_level, $_drill);
        addGraphLink($this, 'object', $use['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        addGraphLink($this, 'object', $use['dataStore'], 'dataStore', $_data, $_level, $_drill);
        addGraphLink($this, 'object', $this->getChild(), 'object', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        addGraphLink($this, 'object', $this->getScenario(), 'scenario', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        return $_data;
    }

    public function getUse() {
        $json = nextdom::fromHumanReadable(json_encode(utils::o2a($this)));
        return nextdom::getTypeUse($json);
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getFather_id($_default = null) {
        if ($this->father_id == '' || !is_numeric($this->father_id)) {
            return $_default;
        }
        return $this->father_id;
    }

    public function getIsVisible($_default = null) {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $_default;
        }
        return $this->isVisible;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $name = str_replace(array('&', '#', ']', '[', '%'), '', $name);
        $this->name = $name;
        return $this;
    }

    public function setFather_id($father_id = null) {
        $this->father_id = ($father_id == '') ? null : $father_id;
        return $this;
    }

    public function setIsVisible($isVisible) {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function getPosition($_default = null) {
        if ($this->position == '' || !is_numeric($this->position)) {
            return $_default;
        }
        return $this->position;
    }

    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '') {
        return utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value) {
        $this->configuration = utils::setJsonAttr($this->configuration, $_key, $_value);
        return $this;
    }

    public function getDisplay($_key = '', $_default = '') {
        return utils::getJsonAttr($this->display, $_key, $_default);
    }

    public function setDisplay($_key, $_value) {
        $this->display = utils::setJsonAttr($this->display, $_key, $_value);
        return $this;
    }

    public function getCache($_key = '', $_default = '') {
        return utils::getJsonAttr(cache::byKey('objectCacheAttr' . $this->getId())->getValue(), $_key, $_default);
    }

    public function setCache($_key, $_value = null) {
        cache::set('objectCacheAttr' . $this->getId(), utils::setJsonAttr(cache::byKey('objectCacheAttr' . $this->getId())->getValue(), $_key, $_value));
    }

}
