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

use NextDom\Managers\CmdManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ScenarioManager;

require_once NEXTDOM_ROOT.'/core/php/core.inc.php';

class jeeObject {
    private $id;
    private $name;
    private $father_id = null;
    private $isVisible = 1;
    private $position;
    private $configuration;
    private $display;
    private $image;
    private $_child = array();

    public static function byId($_id) {
        return JeeObjectManager::byId($_id);
    }

    public static function byName($_name) {
        return JeeObjectManager::byName($_name);
    }

    public static function all($_onlyVisible = false) {
        return JeeObjectManager::all($_onlyVisible);
    }

    public static function rootObject($_all = false, $_onlyVisible = false) {
        return JeeObjectManager::rootObject($_all, $_onlyVisible);
    }

    public static function buildTree($_object = null, $_visible = true) {
        return JeeObjectManager::buildTree($_object, $_visible);
    }

    public static function fullData($_restrict = array()) {
        return JeeObjectManager::fullData($_restrict);
    }

    public static function searchConfiguration($_search) {
        return JeeObjectManager::searchConfiguration($_search);
    }

    public static function deadCmd() {
        return JeeObjectManager::deadCmd();
    }

    public static function checkSummaryUpdate($_cmd_id) {
        return JeeObjectManager::checkSummaryUpdate($_cmd_id);
    }

    public static function getGlobalSummary($_key) {
        return JeeObjectManager::getGlobalSummary($_key);
    }

    public static function getGlobalHtmlSummary($_key) {
        return JeeObjectManager::getGlobalHtmlSummary($_key);
    }

    public static function createSummaryToVirtual($_key = '') {
        return JeeObjectManager::createSummaryToVirtual($_key);
    }

    /**
     * Get table name for stored object in database
     * TODO: A supprimer
     * @return string
     */
    public function getTableName() {
        return 'object';
    }

    /**
     * Check that the object tree does not have a loop.
     *
     * @param array $ancestors List of all objects ancestors
     *
     * @throws Exception
     */
    public function checkTreeConsistency($ancestors = array()) {
        $father = $this->getFather();
        // If object as a father
        if (is_object($father)) {
            // Check if the object is in ancestors (loop)
            if (in_array($this->getFather_id(), $ancestors)) {
                throw new \Exception(__('Problème dans l\'arbre des objets', __FILE__));
            }
            $ancestors[] = $this->getId();

            $father->checkTreeConsistency($ancestors);
        }
    }

    /**
     * Method called before save. Check error and set default values
     *
     * @throws Exception
     */
    public function preSave() {
        if (is_numeric($this->getFather_id()) && $this->getFather_id() === $this->getId()) {
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

    /**
     * Save object in database
     *
     * @return bool True if save works
     */
    public function save() {
        return DB::save($this);
    }

    /**
     * Get direct chidren
     *
     * @param bool $visible Filter only visible
     *
     * @return array|mixed|null
     *
     * @throws Exception
     */
	public function getChild($_visible = true) {
		if (!isset($this->_child[$_visible])) {
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
			$this->_child[$_visible] = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
		}
		return $this->_child[$_visible];
	}


    /**
     * Get tree under this object
     *
     * @return array
     *
     * @throws Exception
     */
    public function getChilds() {
        $tree = array();
        foreach ($this->getChild() as $child) {
            $tree[] = $child;
            $tree = array_merge($tree, $child->getChilds());
        }
        return $tree;
    }


    public function getEqLogic($onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null, $searchOnchild = false) {
        $eqLogics = EqLogicManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible, $eqTypeName, $logicalId);
        if (is_array($eqLogics)) {
            foreach ($eqLogics as &$eqLogic) {
                $eqLogic->setObject($this);
            }
        }
        if ($searchOnchild) {
            $child_object = JeeObjectManager::buildTree($this);
            if (count($child_object) > 0) {
                foreach ($child_object as $object) {
                    $eqLogics = array_merge($eqLogics, $object->getEqLogic($onlyEnable, $onlyVisible, $eqTypeName, $logicalId));
                }
            }
        }
        return $eqLogics;
    }

    public function getEqLogicBySummary($summary = '', $onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null) {
        $def = config::byKey('object:summary');
        if ($summary == '' || !isset($def[$summary])) {
            return null;
        }
        $summaries = $this->getConfiguration('summary');
        if (!isset($summaries[$summary])) {
            return array();
        }
        $eqLogics = EqLogicManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible, $eqTypeName, $logicalId);
        $eqLogics_id = array();
        foreach ($summaries[$summary] as $infos) {
            $cmd = CmdManager::byId(str_replace('#', '', $infos['cmd']));
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

    public function getScenario($onlyEnable = true, $onlyVisible = false) {
        return ScenarioManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible);
    }

    public function preRemove() {
        dataStore::removeByTypeLinkId('object', $this->getId());
    }

    public function remove() {
        nextdom::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'object'));
        return DB::remove($this);
    }

    public function getFather() {
        return JeeObjectManager::byId($this->getFather_id());
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

    public function getHumanName($tag = false, $prettify = false) {
        if ($tag) {
            if ($prettify) {
                if ($this->getDisplay('tagColor') != '') {
                    return '<span class="label" style="text-shadow : none;background-color:' . $this->getDisplay('tagColor') . ' !important;color:' . $this->getDisplay('tagTextColor', 'white') . ' !important">' . $this->getDisplay('icon') . '&nbsp;&nbsp;' . $this->getName() . '</span>';
                } else {
                    return '<span class="label label-primary">' . $this->getDisplay('icon') . '&nbsp;&nbsp;' . $this->getName() . '</span>';
                }
            } else {
                return $this->getDisplay('icon') . '&nbsp;&nbsp;' . $this->getName();
            }
        } else {
            return $this->getName();
        }
    }

    public function getSummary($key = '', $raw = false) {
        $def = config::byKey('object:summary');
        if ($key == '' || !isset($def[$key])) {
            return null;
        }
        $summaries = $this->getConfiguration('summary');
        if (!isset($summaries[$key])) {
            return null;
        }
        $values = array();
        foreach ($summaries[$key] as $infos) {
            if (isset($infos['enable']) && $infos['enable'] == 0) {
                continue;
            }
            $value = cmd::cmdToValue($infos['cmd']);
            if (isset($infos['invert']) && $infos['invert'] == 1) {
                $value = !$value;
            }
            if (isset($def[$key]['count']) && $def[$key]['count'] == 'binary' && $value > 1) {
                $value = 1;
            }
            $values[] = $value;
        }
        if (count($values) == 0) {
            return null;
        }
        if ($raw) {
            return $values;
        }
        if ($def[$key]['calcul'] == 'text') {
            return trim(implode(',', $values), ',');
        }
        return round(nextdom::calculStat($def[$key]['calcul'], $values), 1);
    }

    public function getHtmlSummary($version = 'desktop') {
        if (trim($this->getCache('summaryHtml' . $version)) != '') {
			return $this->getCache('summaryHtml' . $version);
		}
        $return = '<span class="objectSummary' . $this->getId() . '" data-version="' . $version . '">';
        $def = config::byKey('object:summary');
		foreach ($def as $key => $value) {
            if ($this->getConfiguration('summary::hide::' . $version . '::' . $key, 0) == 1) {
                continue;
            }
            $result = $this->getSummary($key);
            if ($result !== null) {
                $style = '';
                if ($version == 'desktop') {
                    $style = 'color:' . $this->getDisplay($version . '::summaryTextColor', '#000000') . ';';
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
        $return = trim($return) . '</span>';
		$this->setCache('summaryHtml' . $version, $return);
		return $return;
    }

    public function getLinkData(&$data = array('node' => array(), 'link' => array()), $level = 0, $drill = null) {
        if ($drill === null) {
            $drill = config::byKey('graphlink::jeeObject::drill');
        }
        if (isset($data['node']['object' . $this->getId()])) {
            return;
        }
        $level++;
        if ($level > $drill) {
            return $data;
        }
        $icon = findCodeIcon($this->getDisplay('icon'));
        $data['node']['object' . $this->getId()] = array(
            'id' => 'object' . $this->getId(),
            'name' => $this->getName(),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontweight' => ($level == 1) ? 'bold' : 'normal',
            'fontsize' => '4em',
            'texty' => -35,
            'textx' => 0,
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=object&id=' . $this->getId(),
        );
        $use = $this->getUse();
        addGraphLink($this, 'object', $this->getEqLogic(), 'eqLogic', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        addGraphLink($this, 'object', $use['cmd'], 'cmd', $data, $level, $drill);
        addGraphLink($this, 'object', $use['scenario'], 'scenario', $data, $level, $drill);
        addGraphLink($this, 'object', $use['eqLogic'], 'eqLogic', $data, $level, $drill);
        addGraphLink($this, 'object', $use['dataStore'], 'dataStore', $data, $level, $drill);
        addGraphLink($this, 'object', $this->getChild(), 'object', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        addGraphLink($this, 'object', $this->getScenario(), 'scenario', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        return $data;
    }

    public function getUse() {
        $json = nextdom::fromHumanReadable(json_encode(utils::o2a($this)));
        return nextdom::getTypeUse($json);
    }

    public function getImgLink() {
        if ($this->getImage('data') == '') {
            return '';
        }
        $dir = __DIR__ . '/../../public/img/object';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        if ($this->getImage('sha512') == '') {
            $this->setImage('sha512', sha512($this->getImage('data')));
            $this->save();
        }
        $filename = $this->getImage('sha512') . '.' . $this->getImage('type');
        $filepath = $dir . '/' . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, base64_decode($this->getImage('data')));
        }
        return 'public/img/object/' . $filename;
    }

    public function toArray() {
        $return = utils::o2a($this, true);
        unset($return['image']);
        $return['img'] = $this->getImgLink();
        return $return;
    }

    /**
     * Get object id
     *
     * @return int|null Object id
     */
    public function getId() {
        return $this->id;
    }

    public function getImage($_key = '', $_default = '') {
        return utils::getJsonAttr($this->image, $_key, $_default);
    }

    public function setImage($_key, $_value) {
        $this->image = utils::setJsonAttr($this->image, $_key, $_value);
        return $this;
    }

    /**
     * Get object name
     *
     * @return string Object name
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get father object id
     *
     * @param int|null $default Default value if object as no father
     *
     * @return int|null Father object id
     */
    public function getFather_id($default = null) {
        if ($this->father_id == '' || !is_numeric($this->father_id)) {
            return $default;
        }
        return $this->father_id;
    }

    /**
     * Get visibility value
     *
     * @param null $default Default value if state is not set
     *
     * @return int|null
     */
    public function getIsVisible($default = null) {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $default;
        }
        return $this->isVisible;
    }

    /**
     * Get visibility state
     *
     * @return bool True if the object is visible
     */
    public function isVisible(): bool {
        if ($this->getIsVisible() === 1)
            return true;
        return false;
    }

    /**
     * Set object id
     *
     * @param int|null $id Object Id
     *
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Set object name
     *
     * @param string $name Object name
     *
     * @return $this
     */
    public function setName(string $name) {
        $name = str_replace(array('&', '#', ']', '[', '%'), '', $name);
        $this->name = $name;
        return $this;
    }

    /**
     * Set father object
     *
     * @param int|null $father_id Set father object id or null for root object
     *
     * @return $this
     */
    public function setFather_id($father_id = null) {
        $this->father_id = ($father_id == '') ? null : $father_id;
        return $this;
    }

    /**
     * Set visibility value
     *
     * @param int $isVisible 1 if visible, 0 for not visible
     *
     * @return $this
     */
    public function setIsVisible($isVisible) {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * Get object position
     *
     * @param int|null $default Default value if position is not set
     *
     * @return int|null Object position
     */
    public function getPosition($default = null) {
        if ($this->position == '' || !is_numeric($this->position)) {
            return $default;
        }
        return $this->position;
    }

    /**
     * Set position
     * TODO: Position dans ?
     *
     * @param int $position Object position
     *
     * @return $this
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * Get configuration information by key
     * TODO: Position dans ?
     * @param string $key Name of the information
     * @param mixed $default Default value
     *
     * @return mixed Value of the asked information or $default.
     */
    public function getConfiguration(string $key = '', $default = '') {
        return utils::getJsonAttr($this->configuration, $key, $default);
    }

    /**
     * Set configuration information by key
     *
     * @param string $key Name of the information
     * @param mixed $value Value of this information
     *
     * @return $this
     */
    public function setConfiguration(string $key, $value) {
        $this->configuration = utils::setJsonAttr($this->configuration, $key, $value);
        return $this;
    }

    /**
     * Get display information by key
     *
     * @param string $key Name of the information
     * @param mixed $default Value of this information
     *
     * @return mixed Value of the asked information or $default
     */
    public function getDisplay(string $key = '', $default = '') {
        return utils::getJsonAttr($this->display, $key, $default);
    }

    /**
     * Set display information by key
     *
     * @param string $key Name of the information
     * @param mixed $value value of this information
     *
     * @return $this
     */
    public function setDisplay(string $key, $value) {
        $this->display = utils::setJsonAttr($this->display, $key, $value);
        return $this;
    }

    /**
     * Get cache information of this object
     *
     * @param string $key Name of the information
     * @param mixed $default Default value
     *
     * @return mixed Value of the asked information or $default
     */
    public function getCache(string $key = '', $default = '') {
		$cache = cache::byKey('objectCacheAttr' . $this->getId())->getValue();
        return utils::getJsonAttr($cache, $key, $default);
    }

    /**
     * Store information of this object in cache
     *
     * @param string $key Name of the information to store
     * @param mixed $value Default value
     */
    public function setCache(string $key, $value = null) {
		cache::set('objectCacheAttr' . $this->getId(), utils::setJsonAttr(cache::byKey('objectCacheAttr' . $this->getId())->getValue(), $key, $value));
    }

}
