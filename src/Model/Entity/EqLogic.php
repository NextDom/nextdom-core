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

use NextDom\Enums\EqLogicViewType;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EqRealManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UserManager;
use NextDom\Managers\ViewDataManager;
use NextDom\Managers\ViewManager;

/**
 * Eqlogic
 *
 * ORM\Table(name="eqLogic", uniqueConstraints={
 *  ORM\UniqueConstraint(name="unique", columns={"name", "object_id"})}, indexes={
 *  ORM\Index(name="eqTypeName", columns={"eqType_name"}),
 *  ORM\Index(name="name", columns={"name"}), @ORM\Index(name="logical_id", columns={"logicalId"}),
 *  ORM\Index(name="generic_type", columns={"generic_type"}),
 *  ORM\Index(name="logica_id_eqTypeName", columns={"logicalId", "eqType_name"}),
 *  ORM\Index(name="object_id", columns={"object_id"}),
 *  ORM\Index(name="timeout", columns={"timeout"}),
 *  ORM\Index(name="eqReal_id", columns={"eqReal_id"}),
 *  ORM\Index(name="tags", columns={"tags"})
 * })
 * ORM\Entity
 */
class EqLogic implements EntityInterface
{
    const CLASS_NAME = EqLogic::class;
    const DB_CLASS_NAME = '`eqLogic`';

    const UIDDELIMITER = '__';
    private static $_templateArray = [];
    protected $_debug = false;
    protected $_object = null;
    protected $_needRefreshWidget = false;
    protected $_timeoutUpdated = false;
    protected $_batteryUpdated = false;
    protected $_changed = false;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=false)
     */
    protected $name;
    /**
     * @var string
     *
     * @ORM\Column(name="generic_type", type="string", length=255, nullable=true)
     */
    protected $generic_type;
    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;
    /**
     * @var string
     *
     * @ORM\Column(name="eqType_name", type="string", length=127, nullable=false)
     */
    protected $eqType_name;
    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;
    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    protected $isVisible;
    /**
     * @var boolean
     *
     * @ORM\Column(name="isEnable", type="boolean", nullable=true)
     */
    protected $isEnable;
    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    protected $timeout;
    /**
     * @var string
     *
     * @ORM\Column(name="category", type="text", length=65535, nullable=true)
     */
    protected $category;
    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;
    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order = 9999;
    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=true)
     */
    protected $comment;
    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="string", length=255, nullable=true)
     */
    protected $tags;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var integer
     *
     * @ORM\Column(name="eqReal_id", type="integer", nullable=true)
     */
    protected $eqReal_id;
    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    protected $object_id;
    private $_cmds = [];

    /**
     * @param int $defaultValue
     * @return bool|int
     */
    public function getIsVisible($defaultValue = 0)
    {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $defaultValue;
        }
        return $this->isVisible;
    }

    /**
     * @param $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        if ($this->isVisible != $isVisible) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        if ($this->order == '' || !is_numeric($this->order)) {
            return 0;
        }
        return $this->order;
    }

    /**
     * @param $_order
     * @return $this
     */
    public function setOrder($_order)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->order, $_order);
        $this->order = $_order;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->comment, $comment);
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param null $defaultValue
     * @return int|null
     */
    public function getEqReal_id($defaultValue = null)
    {
        if ($this->eqReal_id == '' || !is_numeric($this->eqReal_id)) {
            return $defaultValue;
        }
        return $this->eqReal_id;
    }

    /**
     * @param $_eqReal_id
     * @return $this
     */
    public function setEqReal_id($_eqReal_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->eqReal_id, $_eqReal_id);
        $this->eqReal_id = $_eqReal_id;
        return $this;
    }

    /**
     * @return array|mixed|null
     */
    public function getEqReal()
    {
        return EqRealManager::byId($this->eqReal_id);
    }

    /**
     * @param string $cacheKey
     * @param string $defaultValue
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getCache($cacheKey = '', $defaultValue = '')
    {
        $cache = CacheManager::byKey('eqLogicCacheAttr' . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $cacheKey, $defaultValue);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /**
     * @param $cacheKey
     * @param null $cacheValue
     * @throws \Exception
     */
    public function setCache($cacheKey, $cacheValue = null)
    {
        CacheManager::set('eqLogicCacheAttr' . $this->getId(), Utils::setJsonAttr(CacheManager::byKey('eqLogicCacheAttr' . $this->getId())->getValue(), $cacheKey, $cacheValue));
    }

    /**
     * Get HTML code for battery widget
     *
     * @param string $display Display type (dashboard or mobile)
     *
     * @return string HTML code
     *
     * @throws \Exception
     */
    public function batteryWidget(string $display = 'dashboard'): string
    {
        $html = '';
        $level = 'good';
        $niveau = '3';
        $battery = $this->getConfiguration('battery_type', 'none');
        $batteryTime = $this->getConfiguration('batterytime', 'NA');
        $batterySince = 'NA';
        if ($batteryTime != 'NA') {
            $batterySince = ((strtotime(date("Y-m-d")) - strtotime(date("Y-m-d", strtotime($batteryTime)))) / 86400);
        }
        if (strpos($battery, ' ') !== false) {
            $battery = substr(strrchr($battery, " "), 1);
        }
        $plugins = $this->getEqType_name();
        $object_name = 'Aucun';
        if (is_object($this->getObject())) {
            $object_name = $this->getObject()->getName();
        }
        if ($this->getStatus('battery') <= $this->getConfiguration('battery_danger_threshold', ConfigManager::byKey('battery::danger'))) {
            $level = 'critical';
            $niveau = '0';
        } else if ($this->getStatus('battery') <= $this->getConfiguration('battery_warning_threshold', ConfigManager::byKey('battery::warning'))) {
            $level = 'warning';
            $niveau = '1';
        } else if ($this->getStatus('battery') <= 75) {
            $niveau = '2';
        }
        $classAttr = $level . ' ' . $battery . ' ' . $plugins . ' ' . $object_name;
        $idAttr = $level . '__' . $battery . '__' . $plugins . '__' . $object_name;
        $html .= '<div id="' . $idAttr . '" class="eqLogic eqLogic-widget eqLogic-battery ' . $classAttr . '">';
        if ($display == 'mobile') {
            $html .= '<span class="eqLogic-name">' . $this->getName() . '</span>';
        } else {
            $html .= '<a class="eqLogic-name" href="' . $this->getLinkToConfiguration() . '">' . $this->getName() . '</a>';
        }
        $html .= '<span class="eqLogic-place">' . $object_name . '</span>';
        $html .= '<div class="eqLogic-battery-icon"><i class="icon nextdom-battery' . $niveau . ' tooltips" title="' . $this->getStatus('battery', -2) . '%"></i></div>';
        $html .= '<div class="eqLogic-percent">' . $this->getStatus('battery', -2) . '%</div>';
        $html .= '<div>' . __('Le') . ' ' . date("d/m/y G:H:s", strtotime($this->getStatus('batteryDatetime', __('inconnue')))) . '</div>';
        if ($this->getConfiguration('battery_type', '') != '') {
            $html .= '<span class="informations pull-right" title="Piles">' . $this->getConfiguration('battery_type', '') . '</span>';
        }
        $html .= '<span class="informations pull-left" title="Plugin">' . ucfirst($this->getEqType_name()) . '</span>';
        if ($this->getConfiguration('battery_danger_threshold') != '' || $this->getConfiguration('battery_warning_threshold') != '') {
            $html .= '<i class="manual-threshold icon techno-fingerprint41 pull-right" title="Seuil manuel défini"></i>';
        }
        if ($batteryTime != 'NA') {
            $html .= '<i class="icon divers-calendar2 pull-right eqLogic-calendar" title="Pile(s) changée(s) il y a ' . $batterySince . ' jour(s) (' . $batteryTime . ')"> (' . $batterySince . 'j)</i>';
        } else {
            $html .= '<i class="icon divers-calendar2 pull-right eqLogic-calendar" title="Pas de date de changement de pile(s) renseignée"></i>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @param string $configKey
     * @param string $defaultValue
     * @return array|bool|mixed|null|string
     */
    public function getConfiguration($configKey = '', $defaultValue = '')
    {
        return Utils::getJsonAttr($this->configuration, $configKey, $defaultValue);
    }

    /**
     * @param $configKey
     * @param $configValue
     * @return $this
     */
    public function setConfiguration($configKey, $configValue)
    {
        if (in_array($configKey, array('battery_warning_threshold', 'battery_danger_threshold'))) {
            if ($this->getConfiguration($configKey, '') != $configValue) {
                $this->_batteryUpdated = true;
            }
        }
        $configuration = Utils::setJsonAttr($this->configuration, $configKey, $configValue);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getEqType_name()
    {
        return $this->eqType_name;
    }

    /**
     * @param $eqTypeName
     * @return $this
     */
    public function setEqType_name($eqTypeName)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->eqType_name, $eqTypeName);
        $this->eqType_name = $eqTypeName;
        return $this;
    }

    /**
     * @return JeeObject|null
     * @throws \Exception
     */
    public function getObject()
    {
        if ($this->_object === null) {
            $this->setObject(ObjectManager::byId($this->object_id));
        }
        return $this->_object;
    }

    /**
     * @param $_object
     * @return $this
     */
    public function setObject($_object)
    {
        $this->_object = $_object;
        return $this;
    }

    /**
     * @param string $statusKey
     * @param string $defaultValue
     *
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getStatus($statusKey = '', $defaultValue = '')
    {
        $status = CacheManager::byKey('eqLogicStatusAttr' . $this->getId())->getValue();
        return Utils::getJsonAttr($status, $statusKey, $defaultValue);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $name = str_replace(array('&', '#', ']', '[', '%', "'", "\\", "/"), '', $name);
        if ($name != $this->name) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkToConfiguration()
    {
        return 'index.php?v=d&p=' . $this->getEqType_name() . '&m=' . $this->getEqType_name() . '&id=' . $this->getId();
    }

    /**
     * Check and update a command information
     *
     * @param string|Cmd $_logicalId Logical id or cmd object
     * @param mixed $_value Value to update
     * @param null $_updateTime
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function checkAndUpdateCmd($_logicalId, $_value, $_updateTime = null)
    {
        if ($this->getIsEnable() == 0) {
            return false;
        }
        if (is_object($_logicalId)) {
            $cmd = $_logicalId;
        } else {
            $cmd = $this->getCmd('info', $_logicalId);
        }
        if (!is_object($cmd)) {
            return false;
        }
        $oldValue = $cmd->execCmd();
        if (($oldValue != $cmd->formatValue($_value)) || $oldValue === '') {
            $cmd->event($_value, $_updateTime);
            return true;
        }
        if ($_updateTime !== null) {
            if (strtotime($cmd->getCollectDate()) < strtotime($_updateTime)) {
                $cmd->event($_value, $_updateTime);
                return true;
            }
        } else if ($cmd->getConfiguration('repeatEventManagement', 'auto') == 'always') {
            $cmd->event($_value, $_updateTime);
            return true;
        }
        $cmd->setCache('collectDate', date('Y-m-d H:i:s'));
        return false;
    }

    /**
     * @param int $defaultValue
     * @return bool|int
     */
    public function getIsEnable($defaultValue = 0)
    {
        if ($this->isEnable == '' || !is_numeric($this->isEnable)) {
            return $defaultValue;
        }
        return $this->isEnable;
    }

    /**
     * @param $isEnable
     * @return $this
     * @throws \Exception
     */
    public function setIsEnable($isEnable)
    {
        if ($this->isEnable != $isEnable) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        if ($isEnable) {
            $this->setStatus(array('lastCommunication' => date('Y-m-d H:i:s'), 'timeout' => 0));
        }
        $this->isEnable = $isEnable;
        return $this;
    }

    /**
     * Get all commands of the object
     *
     * @param null $_type
     * @param null $_logicalId
     * @param null $_visible
     * @param bool $_multiple
     * @return Cmd[]
     * @throws \Exception
     */
    public function getCmd($_type = null, $_logicalId = null, $_visible = null, $_multiple = false)
    {
        if ($_logicalId !== null) {
            if (isset($this->_cmds[$_logicalId . '.' . $_multiple . '.' . $_type])) {
                return $this->_cmds[$_logicalId . '.' . $_multiple . '.' . $_type];
            }
            $cmds = CmdManager::byEqLogicIdAndLogicalId($this->id, $_logicalId, $_multiple, $_type);
        } else {
            $cmds = CmdManager::byEqLogicId($this->id, $_type, $_visible, $this);
        }
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                $cmd->setEqLogic($this);
            }
        } elseif (is_object($cmds)) {
            $cmds->setEqLogic($this);
        }
        if ($_logicalId !== null && is_object($cmds)) {
            $this->_cmds[$_logicalId . '.' . $_multiple . '.' . $_type] = $cmds;
        }
        return $cmds;
    }

    /**
     * @param $_name
     * @return EqLogic
     * @throws CoreException
     */
    public function copy($_name)
    {
        $eqLogicCopy = clone $this;
        $eqLogicCopy->setName($_name);
        $eqLogicCopy->setId('');
        $eqLogicCopy->save();
        foreach ($eqLogicCopy->getCmd() as $cmd) {
            $cmd->remove();
        }
        $cmd_link = array();
        foreach ($this->getCmd() as $cmd) {
            $cmdCopy = clone $cmd;
            $cmdCopy->setId('');
            $cmdCopy->setEqLogic_id($eqLogicCopy->getId());
            $cmdCopy->save();
            $cmd_link[$cmd->getId()] = $cmdCopy;
        }
        foreach ($this->getCmd() as $cmd) {
            if (!isset($cmd_link[$cmd->getId()])) {
                continue;
            }
            if ($cmd->getValue() != '' && isset($cmd_link[$cmd->getValue()])) {
                $cmd_link[$cmd->getId()]->setValue($cmd_link[$cmd->getValue()]->getId());
                $cmd_link[$cmd->getId()]->save();
            }
        }
        return $eqLogicCopy;
    }

    /**
     * @param bool $_direct
     * @throws CoreException
     */
    public function save($_direct = false)
    {
        if ($this->getName() == '') {
            throw new CoreException(__('Le nom de l\'équipement ne peut pas être vide : ') . print_r($this, true));
        }
        if ($this->getChanged()) {
            if ($this->getId() != '') {

                $this->emptyCacheWidget();
                $this->setConfiguration('updatetime', date('Y-m-d H:i:s'));
            } else {
                $this->setConfiguration('createtime', date('Y-m-d H:i:s'));
            }
            if ($this->getDisplay('showObjectNameOnview', -1) == -1) {
                $this->setDisplay('showObjectNameOnview', 1);
            }
            if ($this->getDisplay('showObjectNameOndview', -1) == -1) {
                $this->setDisplay('showObjectNameOndview', 1);
            }
            if ($this->getDisplay('showObjectNameOnmview', -1) == -1) {
                $this->setDisplay('showObjectNameOnmview', 1);
            }
            if ($this->getDisplay('height', -1) == -1 || intval($this->getDisplay('height')) < 2) {
                $this->setDisplay('height', 'auto');
            }
            if ($this->getDisplay('width', -1) == -1 || intval($this->getDisplay('height')) < 2) {
                $this->setDisplay('width', 'auto');
            }
            foreach (array('dashboard', 'mobile') as $key) {
                if ($this->getDisplay('layout::' . $key . '::table::parameters') == '') {
                    $this->setDisplay('layout::' . $key . '::table::parameters', array('center' => 1, 'styletd' => 'padding:3px;'));
                }
                if ($this->getDisplay('layout::' . $key) == 'table') {
                    if ($this->getDisplay('layout::' . $key . '::table::nbLine') == '') {
                        $this->setDisplay('layout::' . $key . '::table::nbLine', 1);
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::nbColumn') == '') {
                        $this->setDisplay('layout::' . $key . '::table::nbLine', 1);
                    }
                }
                foreach ($this->getCmd() as $cmd) {
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line') == '' && $cmd->getDisplay('layout::' . $key . '::table::cmd::line') != '') {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line', $cmd->getDisplay('layout::' . $key . '::table::cmd::line'));
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column') == '' && $cmd->getDisplay('layout::' . $key . '::table::cmd::column') != '') {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column', $cmd->getDisplay('layout::' . $key . '::table::cmd::column'));
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line', 1) > $this->getDisplay('layout::' . $key . '::table::nbLine', 1)) {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line', $this->getDisplay('layout::' . $key . '::table::nbLine', 1));
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column', 1) > $this->getDisplay('layout::' . $key . '::table::nbColumn', 1)) {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column', $this->getDisplay('layout::' . $key . '::table::nbColumn', 1));
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line') == '') {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::line', 1);
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column') == '') {
                        $this->setDisplay('layout::' . $key . '::table::cmd::' . $cmd->getId() . '::column', 1);
                    }
                }
            }
        }
        DBHelper::save($this, $_direct);
        $this->setChanged(false);
        if ($this->_needRefreshWidget) {
            $this->_needRefreshWidget = false;
            $this->refreshWidget();
        }
        if ($this->_batteryUpdated) {
            $this->_batteryUpdated = false;
            $this->batteryStatus();
        }
        if ($this->_timeoutUpdated) {
            $this->_timeoutUpdated = false;
            if ($this->getTimeout() == null) {
                foreach (MessageManager::byPluginLogicalId('core', 'noMessage' . $this->getId()) as $message) {
                    $message->remove();
                }
                $this->setStatus('timeout', 0);
            } else {
                EqLogicManager::checkAlive();
            }
        }
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

    /**
     *
     */
    public function emptyCacheWidget()
    {
        $users = UserManager::all();
        foreach (array('dashboard', 'mobile', 'mview', 'dview', 'dplan', 'view', 'plan') as $version) {
            $mc = CacheManager::byKey('widgetHtml' . $this->getId() . $version);
            $mc->remove();
            foreach ($users as $user) {
                $mc = CacheManager::byKey('widgetHtml' . $this->getId() . $version . $user->getId());
                $mc->remove();
            }
        }
    }

    /**
     * @param string $displayKey
     * @param string $defaultValue
     * @return array|bool|mixed|null|string
     */
    public function getDisplay($displayKey = '', $defaultValue = '')
    {
        return Utils::getJsonAttr($this->display, $displayKey, $defaultValue);
    }

    /**
     * @param $displayKey
     * @param $displayValue
     */
    public function setDisplay($displayKey, $displayValue)
    {
        if ($this->getDisplay($displayKey) != $displayValue) {
            $this->_needRefreshWidget = true;
        }
        $display = Utils::setJsonAttr($this->display, $displayKey, $displayValue);
        $this->_changed = Utils::attrChanged($this->_changed, $this->display, $display);
        $this->display = $display;
    }

    /**
     *
     */
    public function refreshWidget()
    {
        $this->_needRefreshWidget = false;
        $this->emptyCacheWidget();
        EventManager::add('eqLogic::update', array('eqLogic_id' => $this->getId()));
    }

    /**
     * @param string $_pourcent
     * @param string $_datetime
     * @throws \Exception
     */
    public function batteryStatus($_pourcent = '', $_datetime = '')
    {
        if ($this->getConfiguration('noBatterieCheck', 0) == 1) {
            return;
        }
        if ($_pourcent == '') {
            $_pourcent = $this->getStatus('battery');
            $_datetime = $this->getStatus('batteryDatetime');
        }
        if ($_pourcent > 100) {
            $_pourcent = 100;
        }
        if ($_pourcent < 0) {
            $_pourcent = 0;
        }
        $warning_threshold = $this->getConfiguration('battery_warning_threshold', ConfigManager::byKey('battery::warning'));
        $danger_threshold = $this->getConfiguration('battery_danger_threshold', ConfigManager::byKey('battery::danger'));
        if ($_pourcent != '' && $_pourcent < $danger_threshold) {
            $prevStatus = $this->getStatus('batterydanger', 0);
            $logicalId = 'lowBattery' . $this->getId();
            $message = 'Le module ' . $this->getEqType_name() . ' ' . $this->getHumanName() . ' a moins de ' . $danger_threshold . '% de batterie (niveau danger avec ' . $_pourcent . '% de batterie)';
            if ($this->getConfiguration('battery_type') != '') {
                $message .= ' (' . $this->getConfiguration('battery_type') . ')';
            }
            $this->setStatus('batterydanger', 1);
            if ($prevStatus == 0) {
                if (ConfigManager::ByKey('alert::addMessageOnBatterydanger') == 1) {
                    MessageManager::add($this->getEqType_name(), $message, '', $logicalId);
                }
                $cmds = explode(('&&'), ConfigManager::byKey('alert::batterydangerCmd'));
                if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::batterydangerCmd')) != '') {
                    foreach ($cmds as $id) {
                        $cmd = CmdManager::byId(str_replace('#', '', $id));
                        if (is_object($cmd)) {
                            $cmd->execCmd(array(
                                'title' => __('[' . ConfigManager::byKey('name', 'core', 'NEXTDOM') . '] ') . $message,
                                'message' => ConfigManager::byKey('name', 'core', 'NEXTDOM') . ' : ' . $message,
                            ));
                        }
                    }
                }
            }
        } else if ($_pourcent != '' && $_pourcent < $warning_threshold) {
            $prevStatus = $this->getStatus('batterywarning', 0);
            $logicalId = 'warningBattery' . $this->getId();
            $message = 'Le module ' . $this->getEqType_name() . ' ' . $this->getHumanName() . ' a moins de ' . $warning_threshold . '% de batterie (niveau warning avec ' . $_pourcent . '% de batterie)';
            if ($this->getConfiguration('battery_type') != '') {
                $message .= ' (' . $this->getConfiguration('battery_type') . ')';
            }
            $this->setStatus('batterywarning', 1);
            $this->setStatus('batterydanger', 0);
            if ($prevStatus == 0) {
                if (ConfigManager::ByKey('alert::addMessageOnBatterywarning') == 1) {
                    MessageManager::add($this->getEqType_name(), $message, '', $logicalId);
                }
                $cmds = explode(('&&'), ConfigManager::byKey('alert::batterywarningCmd'));
                if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::batterywarningCmd')) != '') {
                    foreach ($cmds as $id) {
                        $cmd = CmdManager::byId(str_replace('#', '', $id));
                        if (is_object($cmd)) {
                            $cmd->execCmd(array(
                                'title' => __('[' . ConfigManager::byKey('name', 'core', 'NEXTDOM') . '] ') . $message,
                                'message' => ConfigManager::byKey('name', 'core', 'NEXTDOM') . ' : ' . $message,
                            ));
                        }
                    }
                }
            }
        } else {
            foreach (MessageManager::byPluginLogicalId($this->getEqType_name(), 'warningBattery' . $this->getId()) as $message) {
                $message->remove();
            }
            foreach (MessageManager::byPluginLogicalId($this->getEqType_name(), 'lowBattery' . $this->getId()) as $message) {
                $message->remove();
            }
            $this->setStatus('batterydanger', 0);
            $this->setStatus('batterywarning', 0);
        }

        $this->setStatus(array('battery' => $_pourcent, 'batteryDatetime' => ($_datetime != '') ? $_datetime : date('Y-m-d H:i:s')));
    }

    /**
     * Get HTML code for battery widget
     *
     * @param string $display Display type
     *
     * @return string HTML code
     *
     * @param bool $_tag
     * @param bool $_prettify
     * @return string
     * @throws \Exception
     */
    public function getHumanName($_tag = false, $_prettify = false)
    {
        $name = '';
        $object = $this->getObject();
        if (is_object($object)) {
            if ($_tag) {
                if ($object->getDisplay('tagColor') != '') {
                    $name .= '<span class="label" style="text-shadow : none;background-color:' . $object->getDisplay('tagColor') . ';color:' . $object->getDisplay('tagTextColor', 'white') . '">' . $object->getName() . '</span>';
                } else {
                    $name .= '<span class="label label-primary">' . $object->getName() . '</span>';
                }
            } else {
                $name .= '[' . $object->getName() . ']';
            }
        } else {
            if ($_tag) {
                $name .= '<span class="label label-default">' . __('Aucun') . '</span>';
            } else {
                $name .= '[' . __('Aucun') . ']';
            }
        }
        if ($_prettify) {
            $name .= '<br/><strong>';
        }
        if ($_tag) {
            $name .= ' ' . $this->getName();
        } else {
            $name .= '[' . $this->getName() . ']';
        }
        if ($_prettify) {
            $name .= '</strong>';
        }
        return $name;
    }

    /**
     * @param $statusKey
     * @param null $statusValue
     * @throws \Exception
     */
    public function setStatus($statusKey, $statusValue = null)
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $changed = false;
        if (is_array($statusKey)) {
            foreach ($statusKey as $key => $value) {
                if (isset($NEXTDOM_INTERNAL_CONFIG['alerts'][$key])) {
                    $changed = ($this->getStatus($key) != $value);
                }
                if ($changed) {
                    break;
                }
            }
        } else {
            if (isset($JEEDOM_INTERNAL_CONFIG['alerts'][$statusKey])) {
                $changed = ($this->getStatus($statusKey) !== $statusValue);
            }
        }
        CacheManager::set('eqLogicStatusAttr' . $this->getId(), utils::setJsonAttr(CacheManager::byKey('eqLogicStatusAttr' . $this->getId())->getValue(), $statusKey, $statusValue));
        if ($changed) {
            $this->refreshWidget();
        }
    }

    /**
     * @param null $defaultValue
     * @return int|null
     */
    public function getTimeout($defaultValue = null)
    {
        if ($this->timeout == '' || !is_numeric($this->timeout)) {
            return $defaultValue;
        }
        return $this->timeout;
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if ($timeout == '' || is_nan(intval($timeout)) || $timeout < 1) {
            $timeout = null;
        }
        if ($timeout != $this->getTimeout()) {
            $this->_timeoutUpdated = true;
            $this->_changed = true;
        }
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get the name of the SQL table where data is stored.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'eqLogic';
    }

    /**
     * @return bool
     */
    public function hasOnlyEventOnlyCmd()
    {
        return true;
    }

    /**
     * Get HTML code depends of the view
     *
     * @param string $viewType Type of view : mobile, dashboard, scenario
     *
     * @return array|mixed
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function toHtml($viewType = EqLogicViewType::DASHBOARD)
    {
        $replace = $this->preToHtml($viewType);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = NextDomHelper::versionAlias($viewType);

        switch ($this->getDisplay('layout::' . $version)) {
            case 'table':
                $replace['#eqLogic_class#'] = 'eqLogic_layout_table';
                $table = EqLogicManager::generateHtmlTable($this->getDisplay('layout::' . $version . '::table::nbLine', 1), $this->getDisplay('layout::' . $version . '::table::nbColumn', 1), $this->getDisplay('layout::' . $version . '::table::parameters'));
                $br_before = 0;
                foreach ($this->getCmd(null, null, true) as $cmd) {
                    if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
                        continue;
                    }
                    $tag = '#cmd::' . $this->getDisplay('layout::' . $version . '::table::cmd::' . $cmd->getId() . '::line', 1) . '::' . $this->getDisplay('layout::' . $version . '::table::cmd::' . $cmd->getId() . '::column', 1) . '#';
                    if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
                        $table['tag'][$tag] .= '<br/>';
                    }
                    $table['tag'][$tag] .= $cmd->toHtml($viewType, '', $replace['#cmd-background-color#']);
                    $br_before = 0;
                    if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
                        $table['tag'][$tag] .= '<br/>';
                        $br_before = 1;
                    }
                }
                $replace['#cmd#'] = Utils::templateReplace($table['tag'], $table['html']);
                break;
            default:
                $replace['#eqLogic_class#'] = 'eqLogic_layout_default';
                $cmd_html = '';
                $br_before = 0;
                foreach ($this->getCmd(null, null, true) as $cmd) {
                    if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
                        continue;
                    }
                    if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
                        $cmd_html .= '<br/>';
                    }
                    $cmd_html .= $cmd->toHtml($viewType, '', $replace['#cmd-background-color#']);
                    $br_before = 0;
                    if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
                        $cmd_html .= '<br/>';
                        $br_before = 1;
                    }
                }
                $replace['#cmd#'] = $cmd_html;
                break;
        }
        if (!isset(self::$_templateArray[$version])) {
            self::$_templateArray[$version] = FileSystemHelper::getTemplateFileContent('core', $version, 'eqLogic');
        }
        return $this->postToHtml($viewType, Utils::templateReplace($replace, self::$_templateArray[$version]));
    }

    /**
     * Prepare data for HTML view
     *
     * @param string $viewType Type of view
     * @param array $_default
     * @param bool $_noCache
     * @return array|string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function preToHtml($viewType = EqLogicViewType::DASHBOARD, $_default = array(), $_noCache = false)
    {
        // Check if view type is valid
        if (!EqLogicViewType::exists($viewType)) {
            throw new CoreException(__('La version demandée ne peut pas être vide (dashboard, dview ou scénario)'));
        }
        if (!$this->hasRight('r')) {
            return '';
        }
        if (!$this->getIsEnable()) {
            return '';
        }
        $version = NextDomHelper::versionAlias($viewType, false);
        if ($this->getDisplay('showOn' . $version, 1) == 0) {
            return '';
        }
        $userId = '';
        if (isset($_SESSION) && is_object(UserManager::getStoredUser())) {
            $userId = UserManager::getStoredUser()->getId();
        }
        if (!$_noCache) {
            $mc = CacheManager::byKey('widgetHtml' . $this->getId() . $viewType . $userId);
            if ($mc->getValue() != '') {
                return preg_replace("/" . preg_quote(self::UIDDELIMITER) . "(.*?)" . preg_quote(self::UIDDELIMITER) . "/", self::UIDDELIMITER . mt_rand() . self::UIDDELIMITER, $mc->getValue());
            }
        }
        $tagsValue = '';
        if ($this->getTags() != null) {
            $tagsArray = explode(',', $this->getTags());
            foreach ($tagsArray as $tags) {
                if ($tags == null) {
                    continue;
                }
                $tagsValue .= 'tag-' . $tags . ' ';
            }
        }
        $tagsValue = trim($tagsValue);
        $replace = array(
            '#id#' => $this->getId(),
            '#name#' => $this->getName(),
            '#name_display#' => $this->getName(),
            '#hideEqLogicName#' => '',
            '#eqLink#' => $this->getLinkToConfiguration(),
            '#category#' => $this->getCategories(),
            '#color#' => '#ffffff',
            '#border#' => 'none',
            '#border-radius#' => '0px',
            '#style#' => '',
            '#max_width#' => '650px',
            '#logicalId#' => $this->getLogicalId(),
            '#object_name#' => '',
            '#height#' => $this->getDisplay('height', ConfigManager::byKey('widget::size') . 'px'),
            '#width#' => $this->getDisplay('width', ConfigManager::byKey('widget::size') . 'px'),
            '#uid#' => 'eqLogic' . $this->getId() . self::UIDDELIMITER . mt_rand() . self::UIDDELIMITER,
            '#refresh_id#' => '',
            '#version#' => $viewType,
            '#alert_name#' => '',
            '#alert_icon#' => '',
            '#eqType#' => $this->getEqType_name(),
            '#custom_layout#' => ($this->widgetPossibility('custom::layout')) ? 'allowLayout' : '',
            '#tag#' => $tagsValue,
            '#data-tags#' => $this->getTags(),
            '#generic_type#' => $this->getGenericType()
        );

        if ($this->getDisplay('background-color-default' . $version, 1) == 1) {
            if (isset($_default['#background-color#'])) {
                $replace['#background-color#'] = $_default['#background-color#'];
            } else {
                $replace['#background-color#'] = $this->getBackgroundColor($version);
            }
        } else {
            $replace['#background-color#'] = ($this->getDisplay('background-color-transparent' . $version, 0) == 1) ? 'transparent' : $this->getDisplay('background-color' . $version, $this->getBackgroundColor($version));
        }
        if ($this->getAlert() != '') {
            $alert = $this->getAlert();
            $replace['#alert_name#'] = $alert['name'];
            $replace['#alert_icon#'] = $alert['icon'];
            $replace['#background-color#'] = $alert['color'];
        }
        if ($this->getDisplay('color-default' . $version, 1) != 1) {
            $replace['#color#'] = $this->getDisplay('color' . $version, '#ffffff');
        }
        if ($this->getDisplay('border-default' . $version, 1) != 1) {
            $replace['#border#'] = $this->getDisplay('border' . $version, 'none');
        }
        if ($this->getDisplay('border-radius-default' . $version, 1) != 1) {
            $replace['#border-radius#'] = $this->getDisplay('border-radius' . $version, '4') . 'px';
        }
        $refresh_cmd = $this->getCmd('action', 'refresh');
        if (!is_object($refresh_cmd)) {
            foreach ($this->getCmd('action') as $cmd) {
                if ($cmd->getConfiguration('isRefreshCmd') == 1) {
                    $refresh_cmd = $cmd;
                }
            }
        }
        if (is_object($refresh_cmd) && $refresh_cmd->getIsVisible() == 1 && $refresh_cmd->getDisplay('showOn' . $version, 1) == 1) {
            $replace['#refresh_id#'] = $refresh_cmd->getId();
        }
        if ($this->getDisplay('showObjectNameOn' . $version, 0) == 1) {
            $object = $this->getObject();
            $replace['#object_name#'] = (is_object($object)) ? '(' . $object->getName() . ')' : '';
        }
        if ($this->getDisplay('showNameOn' . $version, 1) == 0) {
            $replace['#hideEqLogicName#'] = 'display:none;';
        }
        $vcolor = 'cmdColor';
        $parameters = $this->getDisplay('parameters');
        $replace['#cmd-background-color#'] = ($this->getPrimaryCategory() == '') ? NextDomHelper::getConfiguration('eqLogic:category:default:' . $vcolor) : NextDomHelper::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
        if (is_array($parameters) && isset($parameters['cmd-background-color'])) {
            $replace['#cmd-background-color#'] = $parameters['cmd-background-color'];
        }
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $replace['#' . $key . '#'] = $value;
            }
        }
        $replace['#style#'] = trim($replace['#style#'], ';');

        if (is_array($this->widgetPossibility('parameters'))) {
            foreach ($this->widgetPossibility('parameters') as $pKey => $parameter) {
                if (!isset($parameter['allow_displayType'])) {
                    continue;
                }
                if (!isset($parameter['type'])) {
                    continue;
                }
                if (is_array($parameter['allow_displayType']) && !in_array($version, $parameter['allow_displayType'])) {
                    continue;
                }
                if ($parameter['allow_displayType'] === false) {
                    continue;
                }
                $default = '';
                if (isset($parameter['default'])) {
                    $default = $parameter['default'];
                }
                if ($this->getDisplay('advanceWidgetParameter' . $pKey . $version . '-default', 1) == 1) {
                    $replace['#' . $pKey . '#'] = $default;
                    continue;
                }
                switch ($parameter['type']) {
                    case 'color':
                        if ($this->getDisplay('advanceWidgetParameter' . $pKey . $version . '-transparent', 0) == 1) {
                            $replace['#' . $pKey . '#'] = 'transparent';
                        } else {
                            $replace['#' . $pKey . '#'] = $this->getDisplay('advanceWidgetParameter' . $pKey . $version, $default);
                        }
                        break;
                    default:
                        $replace['#' . $pKey . '#'] = $this->getDisplay('advanceWidgetParameter' . $pKey . $version, $default);
                        break;
                }
            }
        }
        $default_opacity = ConfigManager::byKey('widget::background-opacity');
        if (isset($_SESSION) && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->getOptions('widget::background-opacity::' . $version, null) !== null) {
            $default_opacity = UserManager::getStoredUser()->getOptions('widget::background-opacity::' . $version);
        }
        $opacity = $this->getDisplay('background-opacity' . $version, $default_opacity);
        if ($replace['#background-color#'] != 'transparent' && $opacity != '' && $opacity < 1) {
            list($r, $g, $b) = sscanf($replace['#background-color#'], "#%02x%02x%02x");
            $replace['#background-color#'] = 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
            $replace['#opacity#'] = $opacity;
        }
        return $replace;
    }

    /**
     * @param $_right
     * @param User|null $_user
     * @return bool
     */
    public function hasRight($_right, $_user = null)
    {
        if ($_user != null) {
            if ($_user->getProfils() == 'admin' || $_user->getProfils() == 'user') {
                return true;
            }
            if (strpos($_user->getRights('eqLogic' . $this->getId()), $_right) !== false) {
                return true;
            }
            return false;
        }
        if (!AuthentificationHelper::isConnected()) {
            return false;
        }
        if (AuthentificationHelper::isConnectedAsAdmin() || AuthentificationHelper::isConnectedWithRights('user')) {
            return true;
        }
        if (strpos(UserManager::getStoredUser()->getRights('eqLogic' . $this->getId()), $_right) !== false) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $_tags = str_replace(array("'", '<', '>'), "", $tags);
        $this->_changed = Utils::attrChanged($this->_changed, $this->tags, $_tags);
        $this->tags = $_tags;
        return $this;
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
    public function getCategories()
    {
        $categories = "";
        if ($this->getCategory('security', 0) == 1) {
            $categories = $categories . ' security';
        }
        if ($this->getCategory('heating', 0) == 1) {
            $categories = $categories . ' heating';
        }
        if ($this->getCategory('light', 0) == 1) {
            $categories = $categories . ' light';
        }
        if ($this->getCategory('automatism', 0) == 1) {
            $categories = $categories . ' automatism';
        }
        if ($this->getCategory('energy', 0) == 1) {
            $categories = $categories . ' energy';
        }
        if ($this->getCategory('multimedia', 0) == 1) {
            $categories = $categories . ' multimedia';
        }
        if ($this->getCategory('default', 0) == 1) {
            $categories = $categories . ' default ';
        }
        return $categories;
    }

    /**
     * @param string $categoryKey
     * @param string $defaultValue
     * @return array|bool|int|mixed|null|string
     */
    public function getCategory($categoryKey = '', $defaultValue = '')
    {
        if ($categoryKey == 'other' && strpos($this->category, "1") === false) {
            return 1;
        }
        return Utils::getJsonAttr($this->category, $categoryKey, $defaultValue);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setCategory($_key, $_value)
    {
        if ($this->getCategory($_key) != $_value) {
            $this->_needRefreshWidget = true;
        }
        $category = Utils::setJsonAttr($this->category, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->category, $category);
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param $logicalId
     * @return $this
     */
    public function setLogicalId($logicalId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->logicalId, $logicalId);
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * @param string $_key
     * @param bool $_default
     * @return array|bool|mixed
     * @throws \ReflectionException
     */
    public function widgetPossibility($_key = '', $_default = true)
    {
        $class = new \ReflectionClass($this->getEqType_name());
        $method_toHtml = $class->getMethod('toHtml');
        $return = array();
        if ($method_toHtml->class == EqLogic::class) {
            $return['custom'] = true;
        } else {
            $return['custom'] = false;
        }
        $class = $this->getEqType_name();
        if (property_exists($class, '_widgetPossibility')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $return = $class::$_widgetPossibility;
            if ($_key != '') {
                if (isset($return[$_key])) {
                    return $return[$_key];
                }
                $keys = explode('::', $_key);
                foreach ($keys as $k) {
                    if (!isset($return[$k])) {
                        return false;
                    }
                    if (is_array($return[$k])) {
                        $return = $return[$k];
                    } else {
                        return $return[$k];
                    }
                }
                if (is_array($return) && strpos($_key, 'custom') !== false) {
                    return $_default;
                }
                return $return;
            }
        }
        if ($_key != '') {
            if (isset($return['custom']) && !isset($return[$_key])) {
                return $return['custom'];
            }
            return (isset($return[$_key])) ? $return[$_key] : $_default;
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getGenericType()
    {
        return $this->generic_type;
    }

    /**
     * @param $genericType
     * @return $this
     */
    public function setGenericType($genericType)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->generic_type, $genericType);
        $this->generic_type = $genericType;
        return $this;
    }

    /**
     * @param string $_version
     * @return mixed
     * @throws \Exception
     */
    public function getBackgroundColor($_version = 'dashboard')
    {
        $category = $this->getPrimaryCategory();
        if ($category != '') {
            return NextDomHelper::getConfiguration('eqLogic:category:' . $category . ':color');
        }
        return NextDomHelper::getConfiguration('eqLogic:category:default:color');
    }

    /**
     * @return string
     */
    public function getPrimaryCategory()
    {
        if ($this->getCategory('security', 0) == 1) {
            return 'security';
        }
        if ($this->getCategory('heating', 0) == 1) {
            return 'heating';
        }
        if ($this->getCategory('light', 0) == 1) {
            return 'light';
        }
        if ($this->getCategory('automatism', 0) == 1) {
            return 'automatism';
        }
        if ($this->getCategory('energy', 0) == 1) {
            return 'energy';
        }
        if ($this->getCategory('multimedia', 0) == 1) {
            return 'multimedia';
        }
        return '';
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAlert()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $hasAlert = '';
        $maxLevel = 0;
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $key => $data) {
            if ($this->getStatus($key, 0) != 0 && $NEXTDOM_INTERNAL_CONFIG['alerts'][$key]['level'] > $maxLevel) {
                $hasAlert = $data;
                $maxLevel = $NEXTDOM_INTERNAL_CONFIG['alerts'][$key]['level'];
            }
        }
        return $hasAlert;
    }

    /**
     * Store HTML view in cache
     *
     * @param string $viewType Define target view type
     * @param string $htmlCode HTML code to store
     *
     * @return string $htmlCode
     */
    public function postToHtml(string $viewType, string $htmlCode)
    {
        $user_id = '';
        if (isset($_SESSION) && is_object(UserManager::getStoredUser())) {
            $user_id = UserManager::getStoredUser()->getId();
        }
        CacheManager::set('widgetHtml' . $this->getId() . $viewType . $user_id, $htmlCode);
        return $htmlCode;
    }

    /**
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getMaxCmdAlert()
    {
        $return = 'none';
        $max = 0;
        global $NEXTDOM_INTERNAL_CONFIG;
        foreach ($this->getCmd('info') as $cmd) {
            $cmdLevel = $cmd->getCache('alertLevel');
            if (!isset($NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel])) {
                continue;
            }
            if ($NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel]['level'] > $max) {
                $return = $cmdLevel;
                $max = $NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel]['level'];
            }
        }
        return $return;
    }

    /**
     * @return bool
     */
    public function getShowOnChild()
    {
        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function remove()
    {
        foreach ($this->getCmd() as $cmd) {
            $cmd->remove();
        }
        ViewDataManager::removeByTypeLinkId('eqLogic', $this->getId());
        DataStoreManager::removeByTypeLinkId('eqLogic', $this->getId());
        $this->emptyCacheWidget();
        CacheManager::delete('eqLogicCacheAttr' . $this->getId());
        CacheManager::delete('eqLogicStatusAttr' . $this->getId());
        return DBHelper::remove($this);
    }

    /**
     * @throws \Exception
     */
    public function refresh()
    {
        DBHelper::refresh($this);
    }

    /**
     * @param $_message
     */
    public function displayDebug($_message)
    {
        if ($this->getDebug()) {
            echo $_message . "\n";
        }
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->_debug;
    }

    /**
     * @param $_debug
     */
    public function setDebug($_debug)
    {
        if ($_debug) {
            echo "Mode debug activé\n";
        }
        $this->_debug = $_debug;
    }

    /**
     * @param $_configuration
     * @throws CoreException
     */
    public function import($_configuration)
    {
        $cmdClass = $this->getEqType_name() . 'Cmd';
        if (isset($_configuration['configuration'])) {
            foreach ($_configuration['configuration'] as $key => $value) {
                $this->setConfiguration($key, $value);
            }
        }
        if (isset($_configuration['category'])) {
            foreach ($_configuration['category'] as $key => $value) {
                $this->setCategory($key, $value);
            }
        }
        $cmd_order = 0;
        $link_cmds = array();
        $link_actions = array();
        $arrayToRemove = [];
        if (isset($_configuration['commands'])) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                $exists = 0;
                foreach ($_configuration['commands'] as $command) {
                    if ($command['logicalId'] == $eqLogic_cmd->getLogicalId()) {
                        $exists++;
                    }
                }
                if ($exists < 1) {
                    $arrayToRemove[] = $eqLogic_cmd;
                }
            }
            foreach ($arrayToRemove as $cmdToRemove) {
                try {
                    $cmdToRemove->remove();
                } catch (\Exception $e) {

                }
            }
            foreach ($_configuration['commands'] as $command) {
                $cmd = null;
                foreach ($this->getCmd() as $liste_cmd) {
                    if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
                        || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
                        $cmd = $liste_cmd;
                        break;
                    }
                }
                try {
                    if ($cmd === null || !is_object($cmd)) {
                        /** @var Cmd $cmd */
                        $cmd = new $cmdClass();
                        $cmd->setOrder($cmd_order);
                        $cmd->setEqLogic_id($this->getId());
                    } else {
                        $command['name'] = $cmd->getName();
                        if (isset($command['display'])) {
                            unset($command['display']);
                        }
                    }
                    Utils::a2o($cmd, $command);
                    $cmd->setConfiguration('logicalId', $cmd->getLogicalId());
                    $cmd->save();
                    if (isset($command['value'])) {
                        $link_cmds[$cmd->getId()] = $command['value'];
                    }
                    if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
                        $link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
                    }
                    $cmd_order++;
                } catch (\Exception $exc) {

                }
                $cmd->event('');
            }
        }
        if (count($link_cmds) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($link_cmds as $cmd_id => $link_cmd) {
                    if ($link_cmd == $eqLogic_cmd->getName()) {
                        $cmd = CmdManager::byId($cmd_id);
                        if (is_object($cmd)) {
                            $cmd->setValue($eqLogic_cmd->getId());
                            $cmd->save();
                        }
                    }
                }
            }
        }
        if (count($link_actions) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($link_actions as $cmd_id => $link_action) {
                    if ($link_action == $eqLogic_cmd->getName()) {
                        $cmd = CmdManager::byId($cmd_id);
                        if (is_object($cmd)) {
                            $cmd->setConfiguration('updateCmdId', $eqLogic_cmd->getId());
                            $cmd->save();
                        }
                    }
                }
            }
        }
        $this->save();
    }

    /**
     * @param bool $_withCmd
     * @return array
     * @throws \Exception
     */
    public function export($_withCmd = true)
    {
        $eqLogic = clone $this;
        $eqLogic->setId('');
        $eqLogic->setLogicalId('');
        $eqLogic->setObject_id('');
        $eqLogic->setIsEnable('');
        $eqLogic->setIsVisible('');
        $eqLogic->setTimeout('');
        $eqLogic->setOrder('');
        $eqLogic->setConfiguration('nerverFail', '');
        $eqLogic->setConfiguration('noBatterieCheck', '');
        $return = Utils::o2a($eqLogic);
        foreach ($return as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    if ($value2 == '') {
                        unset($return[$key][$key2]);
                    }
                }
            } else {
                if ($value == '') {
                    unset($return[$key]);
                }
            }
        }
        if (isset($return['configuration']) && count($return['configuration']) == 0) {
            unset($return['configuration']);
        }
        if (isset($return['display']) && count($return['display']) == 0) {
            unset($return['display']);
        }
        if ($_withCmd) {
            $return['cmd'] = array();
            foreach ($this->getCmd() as $cmd) {
                $return['cmd'][] = $cmd->export();
            }
        }
        return $return;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {
        $return = Utils::o2a($this, true);
        $return['status'] = $this->getStatus();
        return $return;
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param null $_drill
     * @return array|null
     * @throws \Exception
     */
    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = null)
    {
        if ($_drill === null) {
            $_drill = ConfigManager::byKey('graphlink::eqLogic::drill');
        }
        if (isset($_data['node']['eqLogic' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $_data['node']['eqLogic' . $this->getId()] = array(
            'id' => 'eqLogic' . $this->getId(),
            'name' => $this->getName(),
            'width' => 60,
            'height' => 60,
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'image' => $this->getImage(),
            'isActive' => $this->getIsEnable(),
            'title' => $this->getHumanName(),
            'url' => $this->getLinkToConfiguration(),
        );
        $use = $this->getUse();
        $usedBy = $this->getUsedBy();
        Utils::addGraphLink($this, 'eqLogic', $this->getCmd(), 'cmd', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'eqLogic', $use['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $use['scenario'], 'scenario', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $use['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $use['dataStore'], 'dataStore', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $usedBy['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $usedBy['scenario'], 'scenario', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $usedBy['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'eqLogic', $usedBy['interactDef'], 'interactDef', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'eqLogic', $usedBy['plan'], 'plan', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'eqLogic', $usedBy['view'], 'view', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        if (!isset($_data['object' . $this->getObject_id()])) {
            Utils::addGraphLink($this, 'eqLogic', $this->getObject(), 'object', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        }
        return $_data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getImage()
    {
        $plugin = PluginManager::byId($this->getEqType_name());
        return $plugin->getPathImgIcon();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUse()
    {
        $json = NextDomHelper::fromHumanReadable(json_encode(Utils::o2a($this)));
        return NextDomHelper::getTypeUse($json);
    }

    /**
     * @param bool $_array
     * @return array
     * @throws \Exception
     */
    public function getUsedBy($_array = false)
    {
        $return = array('cmd' => array(), 'eqLogic' => array(), 'scenario' => array(), 'plan' => array(), 'view' => array());
        $return['cmd'] = CmdManager::searchConfiguration('#eqLogic' . $this->getId() . '#');
        $return['eqLogic'] = EqLogicManager::searchConfiguration(array('#eqLogic' . $this->getId() . '#', '"eqLogic":"' . $this->getId()));
        $return['interactDef'] = InteractDefManager::searchByUse(array('#eqLogic' . $this->getId() . '#', '"eqLogic":"' . $this->getId()));
        $return['scenario'] = ScenarioManager::searchByUse(array(
            array('action' => 'equipment', 'option' => $this->getId(), 'and' => true),
            array('action' => '#eqLogic' . $this->getId() . '#'),
        ));
        $return['view'] = ViewManager::searchByUse('eqLogic', $this->getId());
        $return['plan'] = PlanHeaderManager::searchByUse('eqLogic', $this->getId());
        if ($_array) {
            foreach ($return as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $return;
    }

    /**
     * @return int
     */
    public function getObject_id()
    {
        return $this->object_id;
    }

    /**
     * @param null $object_id
     * @return $this
     */
    public function setObject_id($object_id = null)
    {
        $object_id = (!is_numeric($object_id)) ? null : $object_id;
        $this->_changed = Utils::attrChanged($this->_changed, $this->object_id, $object_id);
        $this->object_id = $object_id;
        return $this;
    }

    /**
     * @param null $_type
     * @param null $_generic_type
     * @param null $_visible
     * @param bool $_multiple
     * @return array|mixed
     * @throws \Exception
     */
    public function getCmdByGenericType($_type = null, $_generic_type = null, $_visible = null, $_multiple = false)
    {
        if ($_generic_type !== null) {
            if (isset($this->_cmds[$_generic_type . '.' . $_multiple . '.' . $_type])) {
                return $this->_cmds[$_generic_type . '.' . $_multiple . '.' . $_type];
            }
            $cmds = CmdManager::byEqLogicIdAndGenericType($this->id, $_generic_type, $_multiple, $_type);
        } else {
            $cmds = CmdManager::byEqLogicId($this->id, $_type, $_visible, $this);
        }
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                $cmd->setEqLogic($this);
            }
        } elseif (is_object($cmds)) {
            $cmds->setEqLogic($this);
        }
        if ($_generic_type !== null && is_object($cmds)) {
            $this->_cmds[$_generic_type . '.' . $_multiple . '.' . $_type] = $cmds;
        }
        return $cmds;
    }

    /**
     * @param $_configuration
     * @param null $_type
     * @return array|mixed
     * @throws \Exception
     */
    public function searchCmdByConfiguration($_configuration, $_type = null)
    {
        return CmdManager::searchConfigurationEqLogic($this->id, $_configuration, $_type);
    }

    /**
     * @param EqLogic $srcEqLogic
     * @return $this
     */
    /**
     * @param EqLogic $srcEqLogic
     * @return $this
     */
    /**
     * @param EqLogic $srcEqLogic
     * @return $this
     */
    public function castFromEqLogic(EqLogic $srcEqLogic)
    {
        $attributes = $srcEqLogic->getAllAttributes();
        foreach ($attributes as $name => $value) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    /**
     * @return array
     */
    public function getAllAttributes()
    {
        return [
            '_debug' => $this->_debug,
            '_object' => $this->_object,
            '_needRefreshWidget' => $this->_needRefreshWidget,
            '_timeoutUpdated' => $this->_timeoutUpdated,
            '_batteryUpdated' => $this->_batteryUpdated,
            '_changed' => $this->_changed,
            'name' => $this->name,
            'generic_type' => $this->generic_type,
            'logicalId' => $this->logicalId,
            'eqType_name' => $this->eqType_name,
            'configuration' => $this->configuration,
            'isVisible' => $this->isVisible,
            'isEnable' => $this->isEnable,
            'timeout' => $this->timeout,
            'category' => $this->category,
            'display' => $this->display,
            'order' => $this->order,
            'comment' => $this->comment,
            'tags' => $this->tags,
            'id' => $this->id,
            'eqReal_id' => $this->eqReal_id,
            'object_id' => $this->object_id,
        ];
    }
}