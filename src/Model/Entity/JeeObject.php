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

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\ScenarioManager;

/**
 * Object
 *
 * @ORM\Table(name="object", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})}, indexes={@ORM\Index(name="fk_object_object1_idx1", columns={"father_id"}), @ORM\Index(name="position", columns={"position"})})
 * @ORM\Entity
 */
class JeeObject implements EntityInterface
{
    const CLASS_NAME = JeeObject::class;
    const DB_CLASS_NAME = '`object`';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    protected $name = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    protected $isVisible = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    protected $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="father_id", referencedColumnName="id")
     * })
     */
    protected $father_id = null;

    protected $_child = array();
    protected $_changed = false;

    /**
     * Get table name for stored object in database
     * TODO: A supprimer
     * @return string
     */
    public function getTableName()
    {
        return 'object';
    }

    /**
     * Method called before save. Check error and set default values
     *
     * @throws \Exception
     */
    public function preSave()
    {
        if (is_numeric($this->getFather_id()) && $this->getFather_id() === $this->getId()) {
            throw new \Exception(__('L\'objet ne peut pas être son propre père', __FILE__));
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
     * Set father object
     *
     * @param int|null $_father_id Set father object id or null for root object
     *
     * @return $this
     */
    public function setFather_id($_father_id = null)
    {
        $_father_id = ($_father_id == '') ? null : $_father_id;
        $this->_changed = Utils::attrChanged($this->_changed, $this->father_id, $_father_id);
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
    public function checkTreeConsistency($ancestors = array())
    {
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
     * @return JeeObject|null
     * @throws \Exception
     */
    public function getFather()
    {
        return ObjectManager::byId($this->getFather_id());
    }

    /**
     * @return int
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
     * Get configuration information by key
     * TODO: Position dans ?
     * @param string $key Name of the information
     * @param mixed $default Default value
     *
     * @return mixed Value of the asked information or $default.
     */
    public function getConfiguration(string $key = '', $default = '')
    {
        return Utils::getJsonAttr($this->configuration, $key, $default);
    }

    /**
     * Set configuration information by key
     *
     * @param string $_key Name of the information
     * @param mixed $_value Value of this information
     *
     * @return $this
     */
    public function setConfiguration(string $_key, $_value)
    {
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Get tree under this object
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getChilds()
    {
        $tree = array();
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
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public function getChild($_visible = true)
    {
        if (!isset($this->_child[$_visible])) {
            $values = array(
                'id' => $this->id,
            );
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE father_id = :id';
            if ($_visible) {
                $sql .= ' AND isVisible = 1 ';
            }
            $sql .= ' ORDER BY position';
            $this->_child[$_visible] = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
        }
        return $this->_child[$_visible];
    }

    /**
     * @param string $summary
     * @param bool $onlyEnable
     * @param bool $onlyVisible
     * @param null $eqTypeName
     * @param null $logicalId
     * @return array|null
     * @throws \Exception
     */
    public function getEqLogicBySummary($summary = '', $onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null)
    {
        $def = ConfigManager::byKey('object:summary');
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

    public function preRemove()
    {
        DataStoreManager::removeByTypeLinkId('object', $this->getId());
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        NextDomHelper::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'object'));
        return DBHelper::remove($this);
    }

    /**
     * Get object name
     *
     * @return string Object name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set object name
     *
     * @param string $_name Object name
     *
     * @return $this
     */
    public function setName($_name)
    {
        $_name = str_replace(array('&', '#', ']', '[', '%'), '', $_name);
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    /**
     * @param string $version
     * @return mixed|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getHtmlSummary($version = 'desktop')
    {
        if (trim($this->getCache('summaryHtml' . $version)) != '') {
            return $this->getCache('summaryHtml' . $version);
        }
        $return = '<span class="objectSummary' . $this->getId() . '" data-version="' . $version . '">';
        $def = ConfigManager::byKey('object:summary');
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
                $return .= '<span style="' . $style . '" class="objectSummaryParent cursor" data-summary="' . $key . '" data-object_id="' . $this->getId() . '" data-displayZeroValue="' . $allowDisplayZero . '">' . $value['icon'] . ' <sup><span class="objectSummary' . $key . '">' . $result . '</span> ' . $value['unit'] . '</span></sup>';
            }
        }
        $return = trim($return) . '</span>';
        $this->setCache('summaryHtml' . $version, $return);
        return $return;
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
     * @param string $key
     * @param bool $raw
     * @return array|float|null|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getSummary($key = '', $raw = false)
    {
        $def = ConfigManager::byKey('object:summary');
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
            $value = CmdManager::cmdToValue($infos['cmd']);
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
        return round(NextDomHelper::calculStat($def[$key]['calcul'], $values), 1);
    }

    /**
     * Get display information by key
     *
     * @param string $key Name of the information
     * @param mixed $default Value of this information
     *
     * @return mixed Value of the asked information or $default
     */
    public function getDisplay(string $key = '', $default = '')
    {
        return Utils::getJsonAttr($this->display, $key, $default);
    }

    /**
     * Set display information by key
     *
     * @param string $key Name of the information
     * @param mixed $value value of this information
     *
     * @return $this
     */
    public function setDisplay(string $key, $value)
    {
        $display = Utils::setJsonAttr($this->display, $key, $value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->display, $display);
        $this->display = $display;
        return $this;
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
     * @param array $data
     * @param int $level
     * @param null $drill
     * @return array|null
     * @throws \ReflectionException
     */
    public function getLinkData(&$data = array('node' => array(), 'link' => array()), $level = 0, $drill = null)
    {
        if ($drill === null) {
            $drill = ConfigManager::byKey('graphlink::jeeObject::drill');
        }
        if (isset($data['node']['object' . $this->getId()])) {
            return null;
        }
        $level++;
        if ($level > $drill) {
            return $data;
        }
        $icon = Utils::findCodeIcon($this->getDisplay('icon'));
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
        Utils::addGraphLink($this, 'object', $this->getEqLogic(), 'eqLogic', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'object', $use['cmd'], 'cmd', $data, $level, $drill);
        Utils::addGraphLink($this, 'object', $use['scenario'], 'scenario', $data, $level, $drill);
        Utils::addGraphLink($this, 'object', $use['eqLogic'], 'eqLogic', $data, $level, $drill);
        Utils::addGraphLink($this, 'object', $use['dataStore'], 'dataStore', $data, $level, $drill);
        Utils::addGraphLink($this, 'object', $this->getChild(), 'object', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'object', $this->getScenario(false), 'scenario', $data, $level, $drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        return $data;
    }

    /**
     * @param bool $tag
     * @param bool $prettify
     * @return string
     */
    public function getHumanName($tag = false, $prettify = false)
    {
        if ($tag) {
            if ($prettify) {
                if ($this->getDisplay('tagColor') != '') {
                    return '<span class="label" style="text-shadow : none;background-color:' . $this->getDisplay('tagColor') . ' !important;color:' . $this->getDisplay('tagTextColor', 'white') . ' !important">' . $this->getDisplay('icon', '<i class="fas fa-tag"></i>') . '<i class="spacing-right"></i>' . $this->getName() . '</span>';
                } else {
                    return '<span class="label label-primary">' . $this->getDisplay('icon', '<i class="fas fa-tag"></i>') . '<i class="spacing-right"></i>' . $this->getName() . '</span>';
                }
            } else {
                return $this->getDisplay('icon', '<i class="fas fa-tag"></i>') . '<i class="spacing-right"></i>' . $this->getName();
            }
        } else {
            return $this->getName();
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getUse()
    {
        $json = NextDomHelper::fromHumanReadable(json_encode(Utils::o2a($this)));
        return NextDomHelper::getTypeUse($json);
    }

    /**
     * @param bool $onlyEnable
     * @param bool $onlyVisible
     * @param null $eqTypeName
     * @param null $logicalId
     * @param bool $searchOnchild
     * @return array|eqLogic[]
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
            $child_object = ObjectManager::buildTree($this);
            if (count($child_object) > 0) {
                foreach ($child_object as $object) {
                    $eqLogics = array_merge($eqLogics, $object->getEqLogic($onlyEnable, $onlyVisible, $eqTypeName, $logicalId));
                }
            }
        }
        return $eqLogics;
    }

    /**
     * @param bool $onlyEnable
     * @param bool $onlyVisible
     * @return array|mixed|null[]
     * @throws \Exception
     */
    /**
     * @param bool $onlyEnable
     * @param bool $onlyVisible
     * @return array|mixed|null[]
     * @throws \Exception
     */
    /**
     * @param bool $onlyEnable
     * @param bool $onlyVisible
     * @return array|mixed|null[]
     * @throws \Exception
     */
    public function getScenario($onlyEnable = true, $onlyVisible = false)
    {
        return ScenarioManager::byObjectId($this->getId(), $onlyEnable, $onlyVisible);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    /**
     * @return array
     * @throws \ReflectionException
     */
    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $return = Utils::o2a($this, true);
        unset($return['image']);
        $return['img'] = $this->getImgLink();
        return $return;
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
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
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getImage($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->image, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setImage($_key, $_value)
    {
        $this->image = Utils::setJsonAttr($this->image, $_key, $_value);
        return $this;
    }

    /**
     * Save object in database
     *
     * @return bool True if save works
     */
    public function save()
    {
        DBHelper::save($this);
        return true;
    }

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
     * Get visibility state
     *
     * @return bool True if the object is visible
     */
    public function isVisible(): bool
    {
        if ($this->getIsVisible() === 1)
            return true;
        return false;
    }

    /**
     * Set visibility value
     *
     * @param int $_isVisible 1 if visible, 0 for not visible
     *
     * @return $this
     */
    public function setIsVisible($_isVisible)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->isVisible, $_isVisible);
        $this->isVisible = $_isVisible;
        return $this;
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
     * Set position
     * TODO: Position dans ?
     *
     * @param int $_position Object position
     *
     * @return $this
     */
    public function setPosition($_position)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->position, $_position);
        $this->position = $_position;
        return $this;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
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
    /**
     * @param $_changed
     * @return $this
     */
    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }
}
