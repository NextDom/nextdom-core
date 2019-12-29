<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

use NextDom\Enums\Common;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\DisplayEntity;
use NextDom\Model\Entity\Parents\IsVisibleEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\PositionEntity;

/**
 * Object for eqLogic group
 *
 * @ORM\Table(name="object", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})}, indexes={@ORM\Index(name="fk_object_object1_idx1", columns={"father_id"}), @ORM\Index(name="position", columns={"position"})})
 * @ORM\Entity
 */
class JeeObject extends BaseEntity
{
    const CLASS_NAME = JeeObject::class;
    const DB_CLASS_NAME = '`object`';
    const TABLE_NAME = NextDomObj::OBJECT;

    use ConfigurationEntity, DisplayEntity, NameEntity, IsVisibleEntity, PositionEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    protected $image;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="father_id", referencedColumnName="id")
     * })
     */
    protected $father_id = null;

    protected $_child = [];
    protected $_changed = false;

    /**
     * Get visibility value
     *
     * @param null $default Default value if state is not set
     *
     * @return int|null
     */
    public function getIsVisible($default = null)
    {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $default;
        }
        return $this->isVisible;
    }

    /**
     * Get object position
     *
     * @param int|null $default Default value if position is not set
     *
     * @return int|null Object position
     */
    public function getPosition($default = null)
    {
        if ($this->position == '' || !is_numeric($this->position)) {
            return $default;
        }
        return $this->position;
    }

    /**
     * Get tree under this object
     *
     * @return JeeObject[]
     *
     * @throws \Exception
     */
    public function getChilds()
    {
        $tree = [];
        foreach ($this->getChild() as $child) {
            $tree[] = $child;
            $tree = array_merge($tree, $child->getChilds());
        }
        return $tree;
    }

    /**
     * Get direct children
     *
     * @param bool $_visible
     * @return JeeObject[]
     *
     * @throws \Exception
     */
    public function getChild($_visible = true)
    {
        if (!isset($this->_child[$_visible])) {
            $this->_child[$_visible] = JeeObjectManager::getChildren($this->id, $_visible);
        }
        return $this->_child[$_visible];
    }

    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    /**
     * Method called before save. Check error and set default values
     *
     * @throws \Exception
     */
    public function preSave()
    {
        if (is_numeric($this->getFather_id()) && $this->getFather_id() === $this->getId()) {
            throw new CoreException(__('L\'objet ne peut pas être son propre père'));
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
     * Get father object id
     *
     * @param int|null $default Default value if object as no father
     *
     * @return int|null Father object id
     */
    public function getFather_id($default = null)
    {
        if ($this->father_id == '' || !is_numeric($this->father_id)) {
            return $default;
        }
        return $this->father_id;
    }

    /**
     * Set father object id
     *
     * @param int|null $_father_id Set father object id or null for root object
     *
     * @return $this
     */
    public function setFather_id($_father_id = null)
    {
        $_father_id = ($_father_id == '') ? null : $_father_id;
        $this->updateChangeState($this->father_id, $_father_id);
        $this->father_id = $_father_id;
        return $this;
    }

    /**
     * Get object id
     *
     * @return int|null Object id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set object id
     *
     * @param int|null $_id Object Id
     *
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /**
     * Check that the object tree does not have a loop.
     *
     * @param array $ancestors List of all objects ancestors
     *
     * @throws \Exception
     */
    public function checkTreeConsistency($ancestors = [])
    {
        $father = $this->getFather();
        // If object as a father
        if (is_object($father)) {
            // Check if the object is in ancestors (loop)
            if (in_array($this->getFather_id(), $ancestors)) {
                throw new CoreException(__('Problème dans l\'arbre des objets'));
            }
            $ancestors[] = $this->getId();

            $father->checkTreeConsistency($ancestors);
        }
    }

    /**
     * Get father
     *
     * @return JeeObject Father jeeObject
     *
     * @throws \Exception
     */
    public function getFather()
    {
        return JeeObjectManager::byId($this->getFather_id());
    }

    /**
     * Get number of parents
     *
     * @return int Number of parents
     *
     * @throws \Exception
     */
    public function parentNumber()
    {
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

    /**
     * Method called before remove
     *
     * @throws \Exception
     */
    public function preRemove()
    {
        DataStoreManager::removeByTypeLinkId('object', $this->getId());
        $params = ['object_id' => $this->getId()];
        $sql = 'UPDATE eqLogic set object_id= NULL where object_id = :object_id';
        DBHelper::exec($sql, $params);
        $sql = 'UPDATE scenario set object_id= NULL where object_id = :object_id';
        DBHelper::exec($sql, $params);
    }

    /**
     * Remove object from the database
     *
     * @return bool True on success
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        NextDomHelper::addRemoveHistory([Common::ID => $this->getId(), Common::NAME => $this->getName(), Common::DATE => date(DateFormat::FULL), Common::TYPE => NextDomObj::OBJECT]);
        return parent::remove();
    }

    /**
     * Set object name
     *
     * @param string $name Object name
     *
     * @return $this
     */
    public function setName($name)
    {
        $name = str_replace(['&', '#', ']', '[', '%'], '', $name);
        $this->updateChangeState($this->name, $name);
        $this->name = $name;
        return $this;
    }

    /**
     * Get eqLogic used in the summary
     *
     * @param string $summary Name of the summary
     * @param bool $onlyEnable Filter only enabled
     * @param bool $onlyVisible Filter only visible
     * @param string $eqTypeName Filter by eqTypeName (plugin)
     * @param string $logicalId Filter by logicalId
     *
     * @return EqLogic[] List of eqLogics
     *
     * @throws \Exception
     */
    public function getEqLogicBySummary($summary = '', $onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null)
    {
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        if ($summary == '' || !isset($def[$summary])) {
            return null;
        }
        $summaries = $this->getConfiguration(Common::SUMMARY);
        if (!isset($summaries[$summary])) {
            return [];
        }
        $eqLogics = EqLogicManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible, $eqTypeName, $logicalId);
        $eqLogics_id = [];
        foreach ($summaries[$summary] as $infos) {
            if ($infos['enable'] != 1) {
                continue;
            }
            $cmd = CmdManager::byId(str_replace('#', '', $infos[NextDomObj::CMD]));
            if (is_object($cmd)) {
                $eqLogics_id[$cmd->getEqLogic_id()] = $cmd->getEqLogic_id();
            }
        }
        $result = [];
        if (is_array($eqLogics)) {
            foreach ($eqLogics as $eqLogic) {
                if (isset($eqLogics_id[$eqLogic->getId()])) {
                    $eqLogic->setObject($this);
                    $result[] = $eqLogic;
                }
            }
        }
        return $result;
    }

    /**
     * Get summary in HTML format
     *
     * @param string $version Render version
     *
     * @return string
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getHtmlSummary($version = 'desktop')
    {
        if (trim($this->getCache('summaryHtml' . $version)) != '') {
            return $this->getCache('summaryHtml' . $version);
        }
        $result = '<span class="objectSummary' . $this->getId() . '" data-version="' . $version . '">';
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        $summaryResult = '';
        if (!empty($def)) {
            foreach ($def as $key => $value) {
                if ($this->getConfiguration('summary::hide::' . $version . '::' . $key, 0) == 1) {
                    continue;
                }
                $summaryResult = $this->getSummary($key);
                if ($summaryResult !== null) {
                    $style = '';
                    if ($version == 'desktop') {
                        $style = 'color:' . $this->getDisplay($version . '::summaryTextColor', '#000000') . ';';
                    }
                    $allowDisplayZero = $value['allowDisplayZero'];
                    if ($value['calcul'] == 'text') {
                        $allowDisplayZero = 1;
                    }
                    if ($allowDisplayZero == 0 && $summaryResult == 0) {
                        $style = 'display:none;';
                    }
                    $result .= '<span style="' . $style . '" class="objectSummaryParent cursor" data-summary="' . $key . '" data-object_id="' . $this->getId() . '" data-displayZeroValue="' . $allowDisplayZero . '">' . $value[Common::ICON] . ' <sup><span class="objectSummary' . $key . '">' . $summaryResult . '</span> ' . $value['unit'] . '</span></sup>';
                }
            }
        }
        $result = trim($result) . '</span>';
        $this->setCache('summaryHtml' . $version, $result);
        return $result;
    }

    /**
     * Get cache information of this object
     *
     * @param string $key Name of the information
     * @param mixed $default Default value
     *
     * @return mixed Value of the asked information or $default
     * @throws \Exception
     */
    public function getCache(string $key = '', $default = '')
    {
        $cache = CacheManager::byKey('objectCacheAttr' . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $key, $default);
    }

    /**
     * Get summary of the object
     *
     * @param string $summaryKey Summary key
     * @param bool $raw Get raw data
     *
     * @return mixed
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getSummary($summaryKey = '', $raw = false)
    {
        $def = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);
        if ($summaryKey == '' || !isset($def[$summaryKey])) {
            return null;
        }
        $summaries = $this->getConfiguration(Common::SUMMARY);
        if (!isset($summaries[$summaryKey])) {
            return null;
        }
        $values = [];
        foreach ($summaries[$summaryKey] as $infos) {
            if (isset($infos['enable']) && $infos['enable'] == 0) {
                continue;
            }
            $value = CmdManager::cmdToValue($infos[NextDomObj::CMD]);
            if (isset($infos['invert']) && $infos['invert'] == 1) {
                $value = !$value;
            }
            if (isset($def[$summaryKey]['count']) && $def[$summaryKey]['count'] == 'binary' && $value > 1) {
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
        if ($def[$summaryKey]['calcul'] == 'text') {
            return trim(implode(',', $values), ',');
        }
        return round(NextDomHelper::calculStat($def[$summaryKey]['calcul'], $values), 1);
    }

    /**
     * Store information of this object in cache
     *
     * @param string $key Name of the information to store
     * @param mixed $value Default value
     * @throws \Exception
     */
    public function setCache(string $key, $value = null)
    {
        CacheManager::set('objectCacheAttr' . $this->getId(), Utils::setJsonAttr(CacheManager::byKey('objectCacheAttr' . $this->getId())->getValue(), $key, $value));
    }

    /**
     * Get graph data
     *
     * @param array $data Graph data
     * @param int $level Current level in graph
     * @param int $drill Level limit
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function getLinkData(&$data = [Common::NODE => [], Common::LINK => []], $level = 0, $drill = null)
    {
        if ($drill === null) {
            $drill = ConfigManager::byKey('graphlink::object::drill');
        }
        if (isset($data[Common::NODE][NextDomObj::OBJECT . $this->getId()])) {
            return null;
        }
        $level++;
        if ($level > $drill) {
            return $data;
        }
        $icon = Utils::findCodeIcon($this->getDisplay(Common::ICON));
        $data[Common::NODE][NextDomObj::OBJECT . $this->getId()] = [
            'id' => NextDomObj::OBJECT . $this->getId(),
            'name' => $this->getName(),
            'icon' => $icon[Common::ICON],
            'fontfamily' => $icon['fontfamily'],
            'fontweight' => ($level == 1) ? 'bold' : 'normal',
            'fontsize' => '4em',
            'texty' => -35,
            'textx' => 0,
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=object&id=' . $this->getId(),
        ];
        $use = $this->getUse();
        Utils::addGraphLink($this, NextDomObj::OBJECT, $this->getEqLogic(), NextDomObj::EQLOGIC, $data, $level, $drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $use[NextDomObj::CMD], NextDomObj::CMD, $data, $level, $drill);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $use[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $data, $level, $drill);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $use[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $data, $level, $drill);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $use[NextDomObj::DATASTORE], NextDomObj::DATASTORE, $data, $level, $drill);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $this->getChild(), NextDomObj::OBJECT, $data, $level, $drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::OBJECT, $this->getScenario(false), NextDomObj::SCENARIO, $data, $level, $drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        return $data;
    }

    /**
     * Get object human name
     *
     * @param bool $tag With tag
     * @param bool $prettify In HTML format
     *
     * @return string
     */
    public function getHumanName($tag = false, $prettify = false)
    {
        if ($tag) {
            $tagIcon = '<i class="fas fa-tag"></i>';
            $spacingRight = '<i class="spacing-right"></i>';
            if ($prettify) {
                if ($this->getDisplay('tagColor') != '') {
                    return '<span class="label" style="text-shadow:none;background-color:' . $this->getDisplay('tagColor') . ' !important;color:' . $this->getDisplay('tagTextColor', 'white') . ' !important">' . $this->getDisplay(Common::ICON, $tagIcon) . $spacingRight . $this->getName() . '</span>';
                } else {
                    return '<span class="label label-primary">' . $this->getDisplay(Common::ICON, $tagIcon) . $spacingRight . $this->getName() . '</span>';
                }
            } else {
                return $this->getDisplay(Common::ICON, $tagIcon) . $spacingRight . $this->getName();
            }
        } else {
            return $this->getName();
        }
    }

    /**
     * Get object usage in string
     *
     * @return array List of usage
     *
     * @throws \ReflectionException
     */
    public function getUse()
    {
        $json = NextDomHelper::fromHumanReadable(json_encode(Utils::o2a($this)));
        return NextDomHelper::getTypeUse($json);
    }

    /**
     * Get eqLogics attached to the object
     *
     * @param bool $onlyEnable Filter only enabled eqLogics
     * @param bool $onlyVisible Filter only visible eqLogics
     * @param null $eqTypeName Filter by name
     * @param null $logicalId Filter by logicalId
     * @param bool $searchOnchild Search also in object childs
     *
     * @return EqLogic[]
     *
     * @throws \Exception
     */
    public function getEqLogic($onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null, $searchOnchild = false)
    {
        $eqLogics = EqLogicManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible, $eqTypeName, $logicalId);
        if (is_array($eqLogics)) {
            foreach ($eqLogics as &$eqLogic) {
                $eqLogic->setObject($this);
            }
        }
        if ($searchOnchild) {
            $childObjects = JeeObjectManager::buildTree($this);
            if (count($childObjects) > 0) {
                foreach ($childObjects as $childObject) {
                    $eqLogics = array_merge($eqLogics, $childObject->getEqLogic($onlyEnable, $onlyVisible, $eqTypeName, $logicalId));
                }
            }
        }
        return $eqLogics;
    }

    /**
     * Get scenario linked to the object
     *
     * @param bool $onlyEnable Filter only enabled scenario
     * @param bool $onlyVisible Filter only visible scenario
     *
     * @return Scenario[] List of scenarios
     *
     * @throws \Exception
     */
    public function getScenario($onlyEnable = true, $onlyVisible = false)
    {
        return ScenarioManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible);
    }

    /**
     *
     * Get data of the object in plain text array
     *
     * @return array
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $result = Utils::o2a($this, true);
        unset($result['image']);
        $result['img'] = $this->getImgLink();
        return $result;
    }

    /**
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getImgLink()
    {
        if ($this->getImage('data') == '') {
            return '';
        }
        $dir = NEXTDOM_ROOT . '/public/img/object';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        if ($this->getImage('sha512') == '') {
            $this->setImage('sha512', Utils::sha512($this->getImage('data')));
            $this->save();
        }
        $filename = $this->getImage('sha512') . '.' . $this->getImage('type');
        $filepath = $dir . '/' . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, base64_decode($this->getImage('data')));
        }
        return 'public/img/object/' . $filename;
    }

    /**
     * Get image of the object
     *
     * @param string $imageKey Image key
     * @param string $defaultValue Default value if not set
     * @return array|bool|mixed|null|string
     */
    public function getImage($imageKey = '', $defaultValue = '')
    {
        return Utils::getJsonAttr($this->image, $imageKey, $defaultValue);
    }

    /**
     * @param string $imageKey Image key
     * @param string $imageValue Image value (CSS icon)
     * @return $this
     */
    public function setImage($imageKey, $imageValue)
    {
        $this->image = Utils::setJsonAttr($this->image, $imageKey, $imageValue);
        return $this;
    }

    /**
     * Save object in database
     *
     * @return bool True if save works
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->_changed) {
            CacheManager::set('globalSummaryHtmldashboard', '');
            CacheManager::set('globalSummaryHtmlmobile', '');
            $this->setCache('summaryHtmldashboard', '');
            $this->setCache('summaryHtmlmobile', '');
        }
        DBHelper::save($this);
        return true;
    }
}
