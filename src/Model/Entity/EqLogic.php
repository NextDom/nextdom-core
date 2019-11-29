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

use NextDom\Enums\BatteryStatus;
use NextDom\Enums\CacheKey;
use NextDom\Enums\CmdType;
use NextDom\Enums\Common;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\EqLogicCategory;
use NextDom\Enums\EqLogicConfigKey;
use NextDom\Enums\EqLogicViewType;
use NextDom\Enums\NextDomObj;
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
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\MessageManager;
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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string EqLogic Name
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=false)
     */
    protected $name;
    /**
     * @var string Used if eqLogic is a simple object (list in nextdom.config.php)
     *
     * @ORM\Column(name="generic_type", type="string", length=255, nullable=true)
     */
    protected $generic_type;
    /**
     * @var string Another id used by plugin
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;
    /**
     * @var string EqLogic plugin type
     *
     * @ORM\Column(name="eqType_name", type="string", length=127, nullable=false)
     */
    protected $eqType_name;
    /**
     * @var string Configuration data
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;
    /**
     * @var int 1 if eqLogic is visible, 0 for hidden
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    protected $isVisible;
    /**
     * @var int 1 if eqLogic is enabled, 0 for hidden
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
     * Get eqLogic visibility
     *
     * @return bool True if eqLogic is visible
     */
    public function isVisible()
    {
        return $this->getIsVisible() == 1;
    }

    /**
     * Get visibility of the eqLogic
     * 1 for visible, 0 for hidden
     *
     * @param int $defaultValue
     *
     * @return int
     */
    public function getIsVisible($defaultValue = 0)
    {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $defaultValue;
        }
        return $this->isVisible;
    }

    /**
     * Set the visibility of the eqLogic
     *
     * @param int $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        if ($this->isVisible != $isVisible) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
            $this->isVisible = $isVisible;
        }
        return $this;
    }

    /**
     * Get order used in eqLogic list
     *
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
     * Set eqLogic order in list
     *
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->order, $order);
        $this->order = $order;
        return $this;
    }

    /**
     * Get the comment attached to the eqLogic
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the comment attached to the eqLogic
     *
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
     * TODO: Unused
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
     * @TODO: Unused
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
     * @TODO: Unused
     * @return array|mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getEqReal()
    {
        return EqRealManager::byId($this->eqReal_id);
    }

    /**
     * Get data in cache linked to the eqLogic
     *
     * @param string $cacheKey Key of the data
     * @param string $defaultValue Default if not defined
     *
     * @return mixed Data
     *
     * @throws \Exception
     */
    public function getCache($cacheKey = '', $defaultValue = '')
    {
        $cache = CacheManager::byKey(CacheKey::EQLOGIC_CACHE_ATTR . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $cacheKey, $defaultValue);
    }

    /**
     * Get Id
     *
     * @return int EqLogic id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Change id
     *
     * @param int|string $newId New id or '' if you want to reset id
     *
     * @return $this
     */
    public function setId($newId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $newId);
        $this->id = $newId;
        return $this;
    }

    /**
     * Store data in cache
     *
     * @param string $cacheKey Key of the data
     * @param mixed $cacheValue Data to store
     *
     * @throws \Exception
     */
    public function setCache($cacheKey, $cacheValue = null)
    {
        CacheManager::set(CacheKey::EQLOGIC_CACHE_ATTR . $this->getId(), Utils::setJsonAttr(CacheManager::byKey(CacheKey::EQLOGIC_CACHE_ATTR . $this->getId())->getValue(), $cacheKey, $cacheValue));
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
    public function batteryWidget(string $display = EqLogicViewType::DASHBOARD): string
    {
        $level = 'good';
        $numericLevel = '3';
        $batteryType = $this->getConfiguration(EqLogicConfigKey::BATTERY_TYPE, 'none');
        $batteryTime = $this->getConfiguration(EqLogicConfigKey::BATTERY_TIME, 'NA');
        $lastBatteryCheck = 'NA';
        if ($batteryTime !== 'NA') {
            $lastBatteryCheck = round((strtotime(date(DateFormat::FULL_DAY)) - strtotime(date(DateFormat::FULL_DAY, strtotime($batteryTime)))) / 86400, 1);
        }
        if (strpos($batteryType, ' ') !== false) {
            $batteryType = substr(strrchr($batteryType, " "), 1);
        }
        $plugins = $this->getEqType_name();
        $attachedObject = __('Aucun');
        if (is_object($this->getObject())) {
            $attachedObject = $this->getObject()->getName();
        }
        if ($this->getStatus('battery') <= $this->getConfiguration(EqLogicConfigKey::BATTERY_DANGER_THRESHOLD, ConfigManager::byKey('battery::danger'))) {
            $level = 'critical';
            $numericLevel = '0';
        } else if ($this->getStatus('battery') <= $this->getConfiguration(EqLogicConfigKey::BATTERY_WARNING_THRESHOLD, ConfigManager::byKey('battery::warning'))) {
            $level = 'warning';
            $numericLevel = '1';
        } else if ($this->getStatus('battery') <= 75) {
            $numericLevel = '2';
        }
        $cssClassAttr = $level . ' ' . $batteryType . ' ' . $plugins . ' ' . $attachedObject;
        $idAttr = $level . '__' . $batteryType . '__' . $plugins . '__' . $attachedObject;
        $html = '<div id="' . $idAttr . '" class="eqLogic eqLogic-widget eqLogic-battery ' . $cssClassAttr . '">';
        if ($display == 'mobile') {
            $html .= '<span class="eqLogic-name">' . $this->getName() . '</span>';
        } else {
            $html .= '<a class="eqLogic-name" href="' . $this->getLinkToConfiguration() . '">' . $this->getName() . '</a>';
        }
        $html .= '<span class="eqLogic-place">' . $attachedObject . '</span>';
        $html .= '<div class="eqLogic-battery-icon"><i class="icon nextdom-battery' . $numericLevel . ' tooltips" title="' . $this->getStatus('battery', -2) . '%"></i></div>';
        $html .= '<div class="eqLogic-percent">' . $this->getStatus('battery', -2) . '%</div>';
        $html .= '<div>' . __('Le') . ' ' . date("Y-m-d H:i:s", strtotime($this->getStatus('batteryDatetime', __('inconnue')))) . '</div>';
        if ($this->getConfiguration('battery_type', '') != '') {
            $html .= '<span class="informations pull-right" title="Piles">' . $this->getConfiguration('battery_type', '') . '</span>';
        }
        $html .= '<span class="informations pull-left" title="Plugin">' . ucfirst($this->getEqType_name()) . '</span>';
        if ($this->getConfiguration(EqLogicConfigKey::BATTERY_DANGER_THRESHOLD) != '' || $this->getConfiguration(EqLogicConfigKey::BATTERY_WARNING_THRESHOLD) != '') {
            $html .= '<i class="manual-threshold icon techno-fingerprint41 pull-right" title="Seuil manuel défini"></i>';
        }
        if ($batteryTime != 'NA') {
            $html .= '<i class="icon divers-calendar2 pull-right eqLogic-calendar" title="Pile(s) changée(s) il y a ' . $lastBatteryCheck . ' jour(s) (' . $batteryTime . ')"> (' . $lastBatteryCheck . 'j)</i>';
        } else {
            $html .= '<i class="icon divers-calendar2 pull-right eqLogic-calendar" title="Pas de date de changement de pile(s) renseignée"></i>';
        }
        return $html . '</div>';
    }

    /**
     * Get object specific configuration data
     *
     * @param string $configKey Configuration key
     * @param string $defaultValue Default value if not found
     *
     * @return mixed
     */
    public function getConfiguration($configKey = '', $defaultValue = '')
    {
        return Utils::getJsonAttr($this->configuration, $configKey, $defaultValue);
    }

    /**
     * Set object specific configuration data
     *
     * @param string $configKey Configuration key
     * @param string $configValue Value to define
     *
     * @return $this
     */
    public function setConfiguration($configKey, $configValue)
    {
        if (in_array($configKey, [EqLogicConfigKey::BATTERY_WARNING_THRESHOLD, EqLogicConfigKey::BATTERY_DANGER_THRESHOLD]) &&
            $this->getConfiguration($configKey, '') != $configValue) {
            $this->_batteryUpdated = true;
        }
        $configuration = Utils::setJsonAttr($this->configuration, $configKey, $configValue);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Get eqLogic type name (linked plugin)
     *
     * @return string EqLogic type (plugin ID)
     */
    public function getEqType_name()
    {
        return $this->eqType_name;
    }

    /**
     * Set eqLogic type name (linked plugin)
     *
     * @param string $eqTypeName EqLogic type (plugin ID)
     *
     * @return $this
     */
    public function setEqType_name($eqTypeName)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->eqType_name, $eqTypeName);
        $this->eqType_name = $eqTypeName;
        return $this;
    }

    /**
     * Get attached object
     *
     * @return JeeObject|null Attached object or null
     *
     * @throws \Exception
     */
    public function getObject()
    {
        if ($this->_object === null) {
            $this->setObject(JeeObjectManager::byId($this->object_id));
        }
        return $this->_object;
    }

    /**
     * Set attached object
     *
     * @param JeeObject $_object Object to link
     *
     * @return $this
     */
    public function setObject($_object)
    {
        $this->_object = $_object;
        return $this;
    }

    /**
     * Get status from cache
     *
     * @param string $statusKey Status key
     * @param string $defaultValue Default value of the status
     *
     * @return mixed
     * @throws \Exception
     */
    public function getStatus($statusKey = '', $defaultValue = '')
    {
        $status = CacheManager::byKey(CacheKey::EQLOGIC_STATUS_ATTR . $this->getId())->getValue();
        return Utils::getJsonAttr($status, $statusKey, $defaultValue);
    }

    /**
     * Get EqLogic name
     *
     * @return string EqLogic name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set eqLogic name
     *
     * @param string $name New name
     *
     * @return $this
     */
    public function setName($name)
    {
        // Remove forbidden characters
        $name = str_replace(['&', '#', ']', '[', '%', "'", "\\", "/"], '', $name);
        if ($name !== $this->name) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get configuration URL
     *
     * @return string Configuration URL
     */
    public function getLinkToConfiguration()
    {
        return 'index.php?v=d&p=' . $this->getEqType_name() . '&m=' . $this->getEqType_name() . '&id=' . $this->getId();
    }

    /**
     * Check and update a command information
     *
     * @param string|Cmd $logicalId Logical id or cmd object
     * @param mixed $newValue Value to update
     * @param null $updateEventTime Date of the update
     *
     * @return bool True if new value is different of the old value.
     *
     * @throws \Exception
     */
    public function checkAndUpdateCmd($logicalId, $newValue, $updateEventTime = null)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        if (is_object($logicalId)) {
            $cmd = $logicalId;
        } else {
            $cmd = $this->getCmd(CmdType::INFO, $logicalId);
        }
        if (!is_object($cmd)) {
            return false;
        }
        $oldValue = $cmd->execCmd();
        if (($oldValue != $cmd->formatValue($newValue)) || $oldValue === '') {
            $cmd->event($newValue, $updateEventTime);
            return true;
        }
        if ($updateEventTime !== null && $updateEventTime !== false) {
            if (strtotime($cmd->getCollectDate()) < strtotime($updateEventTime)) {
                $cmd->event($newValue, $updateEventTime);
                return true;
            }
        } else if ($cmd->getConfiguration(EqLogicConfigKey::REPEAT_EVENT_MANAGEMENT, 'auto') == 'always') {
            $cmd->event($newValue, $updateEventTime);
            return true;
        }
        $cmd->setCache('collectDate', date(DateFormat::FULL));
        return false;
    }

    /**
     * Get eqLogic enable status
     *
     * @return bool True if eqLogic is enabled
     */
    public function isEnabled()
    {
        return $this->getIsEnable() == 1;
    }

    /**
     * Get eqLogic enabled state
     *
     * @param int $defaultValue Default value if not set
     *
     * @return int
     */
    public function getIsEnable($defaultValue = 0)
    {
        if ($this->isEnable == '' || !is_numeric($this->isEnable)) {
            return $defaultValue;
        }
        return $this->isEnable;
    }

    /**
     * Set if the eqLogic is enabled
     *
     * @param int $isEnable 1 if eqLogic is enabled or 0 to disable
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setIsEnable($isEnable)
    {
        if ($this->isEnable != $isEnable) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        if ($isEnable) {
            $this->setStatus(['lastCommunication' => date(DateFormat::FULL), 'timeout' => 0]);
        }
        $this->isEnable = $isEnable;
        return $this;
    }

    /**
     * Get one or more commands of the object
     *
     * @param string|null $cmdType Filter by command type
     * @param string|null $logicalId Filter by logical id
     * @param string|null $visible Filter only visible commands
     * @param string|bool $multiple Get multiple commands
     *
     * @return Cmd|Cmd[]
     *
     * @throws \Exception
     */
    public function getCmd($cmdType = null, $logicalId = null, $visible = null, $multiple = false)
    {
        $result = null;
        if ($logicalId !== null) {
            if (isset($this->_cmds[$logicalId . '.' . $multiple . '.' . $cmdType])) {
                return $this->_cmds[$logicalId . '.' . $multiple . '.' . $cmdType];
            }
            $result = CmdManager::byEqLogicIdAndLogicalId($this->id, $logicalId, $multiple, $cmdType);
        } else {
            $result = CmdManager::byEqLogicId($this->id, $cmdType, $visible, $this);
        }
        // Attach this eqLogic to the command
        if (is_array($result)) {
            foreach ($result as $cmd) {
                $cmd->setEqLogic($this);
            }
        } elseif (is_object($result)) {
            $result->setEqLogic($this);
        }
        // Store in cache
        if ($logicalId !== null && is_object($result)) {
            $this->_cmds[$logicalId . '.' . $multiple . '.' . $cmdType] = $result;
        }
        return $result;
    }

    /**
     * Get copy of this eqLogic
     *
     * @param string $newName
     *
     * @return EqLogic Copied eqLogic
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function copy($newName)
    {
        $eqLogicCopy = clone $this;
        $eqLogicCopy->setName($newName);
        $eqLogicCopy->setId('');
        $eqLogicCopy->save();

        // Clear data
        foreach ($eqLogicCopy->getCmd() as $cmd) {
            $cmd->remove();
        }
        $cmdLink = [];
        // Clone all commands
        foreach ($this->getCmd() as $cmd) {
            $cmdCopy = clone $cmd;
            $cmdCopy->setId('');
            $cmdCopy->setEqLogic_id($eqLogicCopy->getId());
            $cmdCopy->save();
            $cmdLink[$cmd->getId()] = $cmdCopy;
        }
        foreach ($this->getCmd() as $cmd) {
            if (!isset($cmdLink[$cmd->getId()])) {
                continue;
            }
            if ($cmd->getValue() != '' && isset($cmdLink[$cmd->getValue()])) {
                $cmdLink[$cmd->getId()]->setValue($cmdLink[$cmd->getValue()]->getId());
                $cmdLink[$cmd->getId()]->save();
            }
        }
        return $eqLogicCopy;
    }

    /**
     * Save in database
     *
     * @param bool $noProcess Don't call preSave, preInsert, etc.
     *
     * @return $this
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save($noProcess = false)
    {
        if ($this->getName() == '') {
            throw new CoreException(__('Le nom de l\'équipement ne peut pas être vide : ') . print_r($this, true));
        }
        if ($this->getChanged()) {
            if ($this->id != '') {
                $this->emptyCacheWidget();
                $this->setConfiguration('updatetime', date(DateFormat::FULL));
            } else {
                $this->setConfiguration('createtime', date(DateFormat::FULL));
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
            foreach (['dashboard', 'mobile'] as $key) {
                if ($this->getDisplay('layout::' . $key . '::table::parameters') == '') {
                    $this->setDisplay('layout::' . $key . '::table::parameters', ['center' => 1, 'styletd' => 'padding:3px;']);
                }
                if ($this->getDisplay('layout::' . $key) == 'table') {
                    if ($this->getDisplay('layout::' . $key . '::table::nbLine') == '') {
                        $this->setDisplay('layout::' . $key . '::table::nbLine', 1);
                    }
                    if ($this->getDisplay('layout::' . $key . '::table::nbColumn') == '') {
                        $this->setDisplay('layout::' . $key . '::table::nbColumn', 1);
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

        DBHelper::save($this, $noProcess);

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
        return $this;
    }

    /**
     * Get changed data state
     *
     * @return bool True if a data changed since last save
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Set changed data state
     *
     * @param bool $newChangedStatus New data changed state
     *
     * @return $this
     */
    public function setChanged($newChangedStatus)
    {
        $this->_changed = $newChangedStatus;
        return $this;
    }

    /**
     * Remove widget render in cache
     */
    public function emptyCacheWidget()
    {
        $users = UserManager::all();
        foreach (EqLogicViewType::getValues() as $version) {
            $mc = CacheManager::byKey(CacheKey::WIDGET_HTML . $this->getId() . $version);
            $mc->remove();
            foreach ($users as $user) {
                $mc = CacheManager::byKey(CacheKey::WIDGET_HTML . $this->getId() . $version . $user->getId());
                $mc->remove();
            }
        }
    }

    /**
     * Get display parameters
     *
     * @param string $displayKey Parameter key
     * @param mixed $defaultValue Default value if not defined
     *
     * @return mixed Parameters depends of the key
     */
    public function getDisplay($displayKey = '', $defaultValue = '')
    {
        return Utils::getJsonAttr($this->display, $displayKey, $defaultValue);
    }

    /**
     * Set a display parameters
     *
     * @param string $displayKey Key for storage
     * @param mixed $displayValue Data to store
     *
     * @return $this
     */
    public function setDisplay($displayKey, $displayValue)
    {
        if ($this->getDisplay($displayKey) != $displayValue) {
            $this->_needRefreshWidget = true;
        }
        $display = Utils::setJsonAttr($this->display, $displayKey, $displayValue);
        $this->_changed = Utils::attrChanged($this->_changed, $this->display, $display);
        $this->display = $display;

        return $this;
    }

    /**
     * Refresh widget if change occurs since la render
     */
    public function refreshWidget()
    {
        $this->_needRefreshWidget = false;
        $this->emptyCacheWidget();
        EventManager::add('eqLogic::update', ['eqLogic_id' => $this->getId()]);
    }

    /**
     * Update battery status
     *
     * @param string $percent
     * @param string $batteyDateTime
     *
     * @throws \Exception
     */
    public function batteryStatus($percent = '', $batteyDateTime = '')
    {
        if ($this->getConfiguration(EqLogicConfigKey::NO_BATTERY_CHECK, 0) == 1) {
            return;
        }
        if ($percent === '') {
            $percent = $this->getStatus('battery');
            $batteyDateTime = $this->getStatus('batteryDatetime');
        }
        if ($percent > 100) {
            $percent = 100;
        }
        if ($percent < 0) {
            $percent = 0;
        }
        $warningThreshold = $this->getConfiguration(EqLogicConfigKey::BATTERY_WARNING_THRESHOLD, ConfigManager::byKey('battery::warning'));
        $dangerThreshold = $this->getConfiguration(EqLogicConfigKey::BATTERY_DANGER_THRESHOLD, ConfigManager::byKey('battery::danger'));
        if ($percent != '' && $percent < $dangerThreshold) {
            $prevStatus = $this->getStatus(BatteryStatus::DANGER, 0);
            $logicalId = 'lowBattery' . $this->getId();
            $message = 'Le module ' . $this->getEqType_name() . ' ' . $this->getHumanName() . ' a moins de ' . $dangerThreshold . '% de batterie (niveau danger avec ' . $percent . '% de batterie)';
            if ($this->getConfiguration('battery_type') != '') {
                $message .= ' (' . $this->getConfiguration('battery_type') . ')';
            }
            $this->setStatus(BatteryStatus::DANGER, 1);
            if ($prevStatus == 0) {
                if (ConfigManager::ByKey('alert::addMessageOnBatterydanger') == 1) {
                    MessageManager::add($this->getEqType_name(), $message, '', $logicalId);
                }
                $cmds = explode(('&&'), ConfigManager::byKey('alert::batterydangerCmd'));
                if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::batterydangerCmd')) != '') {
                    foreach ($cmds as $id) {
                        $cmd = CmdManager::byId(str_replace('#', '', $id));
                        if (is_object($cmd)) {
                            $cmd->execCmd([
                                'title' => __('[' . ConfigManager::byKey('name', 'core', 'NEXTDOM') . '] ') . $message,
                                'message' => ConfigManager::byKey('name', 'core', 'NEXTDOM') . ' : ' . $message,
                            ]);
                        }
                    }
                }
            }
        } else if ($percent != '' && $percent < $warningThreshold) {
            $prevStatus = $this->getStatus(BatteryStatus::WARNING, 0);
            $logicalId = 'warningBattery' . $this->getId();
            $message = 'Le module ' . $this->getEqType_name() . ' ' . $this->getHumanName() . ' a moins de ' . $warningThreshold . '% de batterie (niveau warning avec ' . $percent . '% de batterie)';
            if ($this->getConfiguration('battery_type') != '') {
                $message .= ' (' . $this->getConfiguration('battery_type') . ')';
            }
            $this->setStatus(BatteryStatus::WARNING, 1);
            $this->setStatus(BatteryStatus::DANGER, 0);
            if ($prevStatus == 0) {
                if (ConfigManager::ByKey('alert::addMessageOnBatterywarning') == 1) {
                    MessageManager::add($this->getEqType_name(), $message, '', $logicalId);
                }
                $cmds = explode(('&&'), ConfigManager::byKey('alert::batterywarningCmd'));
                if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::batterywarningCmd')) != '') {
                    foreach ($cmds as $id) {
                        $cmd = CmdManager::byId(str_replace('#', '', $id));
                        if (is_object($cmd)) {
                            $cmd->execCmd([
                                'title' => __('[' . ConfigManager::byKey('name', 'core', 'NEXTDOM') . '] ') . $message,
                                'message' => ConfigManager::byKey('name', 'core', 'NEXTDOM') . ' : ' . $message,
                            ]);
                        }
                    }
                }
            }
        } else {
            MessageManager::removeByPluginLogicalId($this->getEqType_name(), 'warningBattery' . $this->getId());
            MessageManager::removeByPluginLogicalId($this->getEqType_name(), 'lowBattery' . $this->getId());
            $this->setStatus(BatteryStatus::DANGER, 0);
            $this->setStatus(BatteryStatus::WARNING, 0);
        }

        $this->setStatus(['battery' => $percent, 'batteryDatetime' => ($batteyDateTime != '') ? $batteyDateTime : date(DateFormat::FULL)]);
    }

    /**
     * Get HTML code for battery widget
     *
     * @param bool $_tag
     * @param bool $_prettify
     * @return string HTML code
     *
     * @throws \Exception
     */
    public function getHumanName($_tag = false, $_prettify = false)
    {
        $name = '';
        $linkedObject = $this->getObject();
        if (is_object($linkedObject)) {
            if ($_tag) {
                if ($linkedObject->getDisplay('tagColor') != '') {
                    $name .= '<span class="label" style="text-shadow : none;background-color:' . $linkedObject->getDisplay('tagColor') . ';color:' . $linkedObject->getDisplay('tagTextColor', 'white') . '">' . $linkedObject->getName() . '</span>';
                } else {
                    $name .= '<span class="label label-primary">' . $linkedObject->getName() . '</span>';
                }
            } else {
                $name .= '[' . $linkedObject->getName() . ']';
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
            if (isset($NEXTDOM_INTERNAL_CONFIG['alerts'][$statusKey])) {
                $changed = ($this->getStatus($statusKey) !== $statusValue);
            }
        }
        CacheManager::set('eqLogicStatusAttr' . $this->getId(), utils::setJsonAttr(CacheManager::byKey('eqLogicStatusAttr' . $this->getId())->getValue(), $statusKey, $statusValue));
        if ($changed) {
            $this->refreshWidget();
        }
    }

    /**
     * Get eqLogic timeout
     *
     * @param int|string $defaultValue Default value if timeout is not define
     *
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
     * Get eqLogic timeout
     *
     * @param int $timeout Timeout
     *
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
     * @throws \NextDom\Exceptions\OperatingSystemException
     * @throws \ReflectionException
     */
    public function toHtml($viewType = EqLogicViewType::DASHBOARD)
    {
        $replace = $this->preToHtml($viewType);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = NextDomHelper::versionAlias($viewType);

        if ($this->getDisplay('layout::' . $version) == 'table') {
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
        } else {
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
        }

        if (!isset(self::$_templateArray[$version])) {
            $default_widgetTheme = ConfigManager::byKey('widget::theme');
            if (isset($_SESSION) && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->getOptions('widget::theme', null) !== null) {
                $default_widgetTheme = UserManager::getStoredUser()->getOptions('widget::theme');
            }
            self::$_templateArray[$version] = FileSystemHelper::getCoreTemplateFileContent($version, 'eqLogic', '', $default_widgetTheme);

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
     * @throws \NextDom\Exceptions\OperatingSystemException
     * @throws \ReflectionException
     */
    public function preToHtml($viewType = EqLogicViewType::DASHBOARD, $_default = [], $_noCache = false)
    {
        // Check if view type is valid
        if (!EqLogicViewType::exists($viewType)) {
            throw new CoreException(__('La version demandée ne peut pas être vide (dashboard, dview ou scénario)'));
        }
        if (!$this->hasRight('r')) {
            return '';
        }
        if (!$this->isEnabled()) {
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
            $mc = CacheManager::byKey(CacheKey::WIDGET_HTML . $this->getId() . $viewType . $userId);
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
        $replace = [
            '#id#' => $this->getId(),
            '#name#' => $this->getName(),
            '#name_display#' => $this->getName(),
            '#hideEqLogicName#' => '',
            '#eqLink#' => $this->getLinkToConfiguration(),
            '#category#' => $this->getPrimaryCategory(),
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
        ];

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
        $refresh_cmd = $this->getCmd(CmdType::ACTION, 'refresh');
        if (!is_object($refresh_cmd)) {
            foreach ($this->getCmd(CmdType::ACTION) as $cmd) {
                if ($cmd->getConfiguration('isRefreshCmd') == 1) {
                    $refresh_cmd = $cmd;
                }
            }
        }
        if (is_object($refresh_cmd) && $refresh_cmd->isVisible() && $refresh_cmd->getDisplay('showOn' . $version, 1) == 1) {
            $replace['#refresh_id#'] = $refresh_cmd->getId();
        }
        if ($this->getDisplay('showObjectNameOn' . $version, 0) == 1) {
            $linkedObject = $this->getObject();
            $replace['#object_name#'] = (is_object($linkedObject)) ? '(' . $linkedObject->getName() . ')' : '';
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
                if (isset($parameter[EqLogicCategory::DEFAULT])) {
                    $default = $parameter[EqLogicCategory::DEFAULT];
                }
                if ($this->getDisplay('advanceWidgetParameter' . $pKey . $version . '-default', 1) == 1) {
                    $replace['#' . $pKey . '#'] = $default;
                    continue;
                }
                if ($parameter['type'] == 'color') {
                    if ($this->getDisplay('advanceWidgetParameter' . $pKey . $version . '-transparent', 0) == 1) {
                        $replace['#' . $pKey . '#'] = 'transparent';
                    } else {
                        $replace['#' . $pKey . '#'] = $this->getDisplay('advanceWidgetParameter' . $pKey . $version, $default);
                    }
                } else {
                    $replace['#' . $pKey . '#'] = $this->getDisplay('advanceWidgetParameter' . $pKey . $version, $default);
                }
            }
        }
        $default_opacity = ConfigManager::byKey(ConfigKey::WIDGET_BACKGROUND_OPACITY);
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
     * Test if user have right
     *
     * @param $rightToCheck
     * @param User|null $user
     * @return bool
     */
    public function hasRight($rightToCheck, $user = null)
    {
        if ($user != null) {
            if ($user->getProfils() == 'admin' || $user->getProfils() == 'user') {
                return true;
            }
            if (strpos($user->getRights('eqLogic' . $this->getId()), $rightToCheck) !== false) {
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
        if (strpos(UserManager::getStoredUser()->getRights('eqLogic' . $this->getId()), $rightToCheck) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get list of eqLogic tags
     *
     * @return mixed List of eqLogic tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Define list of eqLogics tags
     *
     * @param mixed $tags List of tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $_tags = str_replace(["'", '<', '>'], "", $tags);
        $this->_changed = Utils::attrChanged($this->_changed, $this->tags, $_tags);
        $this->tags = $_tags;
        return $this;
    }

    /**
     * Get primary category
     *
     * @return string
     */
    public function getPrimaryCategory()
    {
        $categoryOrder = [
            EqLogicCategory::SECURITY,
            EqLogicCategory::HEATING,
            EqLogicCategory::LIGHT,
            EqLogicCategory::AUTOMATISM,
            EqLogicCategory::ENERGY,
            EqLogicCategory::MULTIMEDIA
        ];
        foreach ($categoryOrder as $categoryCode) {
            if ($this->getCategory($categoryCode, 0) == 1) {
                return $categoryCode;
            }
        }
        return '';
    }

    /**
     * Test if the object belongs to a category
     *
     * @param string $categoryKey Key of the category
     * @param string $defaultValue Default value if not set
     *
     * @return mixed 1 if object belongs the category
     */
    public function getCategory($categoryKey = '', $defaultValue = '')
    {
        if ($categoryKey == 'other' && strpos($this->category, "1") === false) {
            return 1;
        }
        return Utils::getJsonAttr($this->category, $categoryKey, $defaultValue);
    }

    /**
     * Define if the object belongs to a category
     *
     * @param string $categoryKey Key of the category
     * @param int $belong 1 if eqLogic belong the category or 0
     *
     * @return $this
     */
    public function setCategory($categoryKey, $belong)
    {
        if ($this->getCategory($categoryKey) != $belong) {
            $this->_needRefreshWidget = true;
        }
        $category = Utils::setJsonAttr($this->category, $categoryKey, $belong);
        $this->_changed = Utils::attrChanged($this->_changed, $this->category, $category);
        $this->category = $category;
        return $this;
    }

    /**
     * Set logicalId (Id used by plugins)
     *
     * @return string Logical Id
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * Get logicalId (Id used by plugins)
     *
     * @param string $logicalId logical Id
     *
     * @return $this
     */
    public function setLogicalId($logicalId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->logicalId, $logicalId);
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * Get widget specific possibility
     *
     * @param string $keyPossibility Key of the possibility
     * @param bool $defaultValue Default value is not set
     *
     * @return mixed Possibility data
     *
     * @throws \ReflectionException
     */
    public function widgetPossibility($keyPossibility = '', $defaultValue = true)
    {
        $reflectedClass = new \ReflectionClass($this->getEqType_name());
        $method_toHtml = $reflectedClass->getMethod('toHtml');
        $result = [];
        $result[Common::CUSTOM] = $method_toHtml->class == EqLogic::class;
        $reflectedClass = $this->getEqType_name();
        if (property_exists($reflectedClass, '_widgetPossibility')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $result = $reflectedClass::$_widgetPossibility;
            if ($keyPossibility != '') {
                if (isset($result[$keyPossibility])) {
                    return $result[$keyPossibility];
                }
                $keys = explode('::', $keyPossibility);
                foreach ($keys as $k) {
                    if (!isset($result[$k])) {
                        return false;
                    }
                    if (is_array($result[$k])) {
                        $result = $result[$k];
                    } else {
                        return $result[$k];
                    }
                }
                if (is_array($result) && strpos($keyPossibility, Common::CUSTOM) !== false) {
                    return $defaultValue;
                }
                return $result;
            }
        }
        if ($keyPossibility != '') {
            if (isset($result[Common::CUSTOM]) && !isset($result[$keyPossibility])) {
                return $result[Common::CUSTOM];
            }
            return (isset($result[$keyPossibility])) ? $result[$keyPossibility] : $defaultValue;
        }
        return $result;
    }

    /**
     * Get generic type for simple eqLogic
     *
     * @return string Generic type
     */
    public function getGenericType()
    {
        return $this->generic_type;
    }

    /**
     * Set generic type for simple EqLogic
     *
     * @param string $genericType Generic type
     *
     * @return $this
     */
    public function setGenericType($genericType)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->generic_type, $genericType);
        $this->generic_type = $genericType;
        return $this;
    }

    /**
     * Get widget background color
     *
     * @param string $version Display version (unused ???)
     *
     * @return string Background color
     *
     * @throws \Exception
     */
    public function getBackgroundColor($version = 'dashboard')
    {
        $primaryCategory = $this->getPrimaryCategory();
        if ($primaryCategory != '') {
            return NextDomHelper::getConfiguration('eqLogic:category:' . $primaryCategory . ':color');
        }
        return NextDomHelper::getConfiguration('eqLogic:category:default:color');
    }

    /**
     * Get alert if presents
     *
     * @return mixed Alert data
     *
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
     * @throws \Exception
     */
    public function postToHtml(string $viewType, string $htmlCode)
    {
        $user_id = '';
        if (isset($_SESSION) && is_object(UserManager::getStoredUser())) {
            $user_id = UserManager::getStoredUser()->getId();
        }
        CacheManager::set(CacheKey::WIDGET_HTML . $this->getId() . $viewType . $user_id, $htmlCode);
        return $htmlCode;
    }

    /**
     * Get max command alter for this eqLogic
     *
     * @return mixed Max command alert
     *
     * @throws \Exception
     */
    public function getMaxCmdAlert()
    {
        $result = 'none';
        $max = 0;
        global $NEXTDOM_INTERNAL_CONFIG;
        foreach ($this->getCmd(CmdType::INFO) as $cmd) {
            $cmdLevel = $cmd->getCache('alertLevel');
            if (!isset($NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel])) {
                continue;
            }
            if ($NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel]['level'] > $max) {
                $result = $cmdLevel;
                $max = $NEXTDOM_INTERNAL_CONFIG['alerts'][$cmdLevel]['level'];
            }
        }
        return $result;
    }

    /**
     * @TODO: ???
     * @return bool
     */
    public function getShowOnChild()
    {
        return false;
    }

    /**
     * Remove from database
     *
     * @return bool True on success
     *
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
        CacheManager::delete(CacheKey::EQLOGIC_CACHE_ATTR . $this->getId());
        CacheManager::delete('eqLogicStatusAttr' . $this->getId());
        return DBHelper::remove($this);
    }

    /**
     * Refresh data from the database
     *
     * @throws \Exception
     */
    public function refresh()
    {
        DBHelper::refresh($this);
    }

    /**
     * Show debug message if in debug mode
     * @param $_message
     */
    public function displayDebug($_message)
    {
        if ($this->getDebug()) {
            echo $_message . "\n";
        }
    }

    /**
     * Get debug state
     *
     * @return bool True if debug is activated
     */
    public function getDebug()
    {
        return $this->_debug;
    }

    /**
     * Set debug state
     *
     * @param bool $newDebugState True for activate debug
     */
    public function setDebug($newDebugState)
    {
        if ($newDebugState) {
            echo "Mode debug activé\n";
        }
        $this->_debug = $newDebugState;
    }

    /**
     * Import eqLogic from a plain text array
     *
     * @param array $data Specific configuration
     * @param bool $noRemove Avoid to remove commands
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function import($data, $noRemove = false)
    {
        $cmdClass = $this->getEqType_name() . 'Cmd';
        if (isset($data['configuration'])) {
            foreach ($data['configuration'] as $key => $value) {
                $this->setConfiguration($key, $value);
            }
        }
        if (isset($data['category'])) {
            foreach ($data['category'] as $key => $value) {
                $this->setCategory($key, $value);
            }
        }
        $cmdOrder = 0;
        $linkCmds = [];
        $linkActions = [];
        $arrayToRemove = [];
        if (isset($data['commands'])) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                $exists = 0;
                foreach ($data['commands'] as $command) {
                    if ($command['logicalId'] == $eqLogic_cmd->getLogicalId()) {
                        $exists++;
                    }
                }
                if ($exists < 1) {
                    $arrayToRemove[] = $eqLogic_cmd;
                }
            }
            if (!$noRemove) {
                foreach ($arrayToRemove as $cmdToRemove) {
                    try {
                        $cmdToRemove->remove();
                    } catch (\Exception $e) {

                    }
                }
            }
            foreach ($data['commands'] as $command) {
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
                        $cmd->setOrder($cmdOrder);
                        $cmd->setEqLogic_id($this->getId());
                    } else {
                        $command['name'] = $cmd->getName();
                        if (isset($command[Common::DISPLAY])) {
                            unset($command[Common::DISPLAY]);
                        }
                    }
                    Utils::a2o($cmd, $command);
                    $cmd->setConfiguration('logicalId', $cmd->getLogicalId());
                    $cmd->save();
                    if (isset($command['value'])) {
                        $linkCmds[$cmd->getId()] = $command['value'];
                    }
                    if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
                        $linkActions[$cmd->getId()] = $command['configuration']['updateCmdId'];
                    }
                    $cmdOrder++;
                } catch (\Exception $exc) {

                }
                $cmd->event('');
            }
        }
        if (count($linkCmds) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($linkCmds as $cmd_id => $link_cmd) {
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
        if (count($linkActions) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($linkActions as $cmd_id => $link_action) {
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
     * Export eqLogic data in plain text array
     *
     * @param bool $withCommands Add commands data
     *
     * @return array EqLogic data
     *
     * @throws \Exception
     */
    public function export($withCommands = true)
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
        $result = Utils::o2a($eqLogic);
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    if ($value2 == '') {
                        unset($result[$key][$key2]);
                    }
                }
            } else {
                if ($value == '') {
                    unset($result[$key]);
                }
            }
        }
        if (isset($result['configuration']) && count($result['configuration']) == 0) {
            unset($result['configuration']);
        }
        if (isset($result[Common::DISPLAY]) && count($result[Common::DISPLAY]) == 0) {
            unset($result[Common::DISPLAY]);
        }
        if ($withCommands) {
            $result['cmd'] = [];
            foreach ($this->getCmd() as $cmd) {
                $result['cmd'][] = $cmd->export();
            }
        }
        return $result;
    }

    /**
     * Get command of the eqLogic by generic type
     *
     * @param string $cmdType Filter by command type
     * @param string $genericType Filter by generic type
     * @param int|bool $onlyVisible Filter only visible
     * @param bool $multipleResults Get multiple results if exists
     *
     * @return Cmd|Cmd[] List of commands
     *
     * @throws \Exception
     */
    public function getCmdByGenericType($cmdType = null, $genericType = null, $onlyVisible = null, $multipleResults = false)
    {
        if ($genericType !== null) {
            if (isset($this->_cmds[$genericType . '.' . $multipleResults . '.' . $cmdType])) {
                return $this->_cmds[$genericType . '.' . $multipleResults . '.' . $cmdType];
            }
            $cmds = CmdManager::byEqLogicIdAndGenericType($this->id, $genericType, $multipleResults, $cmdType);
        } else {
            $cmds = CmdManager::byEqLogicId($this->id, $cmdType, $onlyVisible, $this);
        }
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                $cmd->setEqLogic($this);
            }
        } elseif (is_object($cmds)) {
            $cmds->setEqLogic($this);
        }
        if ($genericType !== null && is_object($cmds)) {
            $this->_cmds[$genericType . '.' . $multipleResults . '.' . $cmdType] = $cmds;
        }
        return $cmds;
    }

    /**
     * Search cmd with a specific configuration
     *
     * @param $configuration
     * @param string $cmdType
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function searchCmdByConfiguration($configuration, $cmdType = null)
    {
        return CmdManager::searchConfigurationEqLogic($this->id, $configuration, $cmdType);
    }

    /**
     * Get link data for graph representation
     *
     * @param array $data Data to show
     * @param int $distance Distance from the center of the graph
     * @param int $drill Drill limit
     *
     * @return array EqLogic graph data representation
     *
     * @throws \Exception
     */
    public function getLinkData(&$data = ['node' => [], 'link' => []], $distance = 0, $drill = null)
    {
        if ($drill === null) {
            $drill = ConfigManager::byKey('graphlink::eqLogic::drill');
        }
        if (isset($data['node']['eqLogic' . $this->getId()])) {
            return null;
        }
        $distance++;
        if ($distance > $drill) {
            return $data;
        }
        $data['node']['eqLogic' . $this->getId()] = [
            'id' => 'eqLogic' . $this->getId(),
            'name' => $this->getName(),
            'width' => 60,
            'height' => 60,
            'fontweight' => ($distance == 1) ? 'bold' : 'normal',
            'image' => $this->getImage(),
            'isActive' => $this->getIsEnable(),
            'title' => $this->getHumanName(),
            'url' => $this->getLinkToConfiguration(),
        ];
        $use = $this->getUse();
        $usedBy = $this->getUsedBy();
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $this->getCmd(), NextDomObj::CMD, $data, $distance, $drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $use[NextDomObj::CMD], NextDomObj::CMD, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $use[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $use[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $use[NextDomObj::DATASTORE], NextDomObj::DATASTORE, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::CMD], NextDomObj::CMD, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $data, $distance, $drill);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::INTERACT_DEF], NextDomObj::INTERACT_DEF, $data, $distance, $drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::PLAN], NextDomObj::PLAN, $data, $distance, $drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::EQLOGIC, $usedBy[NextDomObj::VIEW], NextDomObj::VIEW, $data, $distance, $drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        if (!isset($data['object' . $this->getObject_id()])) {
            Utils::addGraphLink($this, NextDomObj::EQLOGIC, $this->getObject(), NextDomObj::OBJECT, $data, $distance, $drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        }
        return $data;
    }

    /**
     * Get linked image
     *
     * @return string Image path
     *
     * @throws \Exception
     */
    public function getImage()
    {
        $plugin = PluginManager::byId($this->getEqType_name());
        return $plugin->getPathImgIcon();
    }

    /**
     * Get list of usage in string (@TODO: ???)
     *
     * @return array List of usage
     *
     * @throws \Exception
     */
    public function getUse()
    {
        $json = NextDomHelper::fromHumanReadable(json_encode(Utils::o2a($this)));
        return NextDomHelper::getTypeUse($json);
    }

    /**
     * Get list of this eqLogic usage
     *
     * @param bool $arrayResult True for result in array (no objects)
     *
     * @return array List of eqLogic usage
     *
     * @throws \Exception
     */
    public function getUsedBy($arrayResult = false)
    {
        $result = ['cmd' => [], 'eqLogic' => [], 'scenario' => [], 'plan' => [], 'view' => []];
        $result['cmd'] = CmdManager::searchConfiguration('#eqLogic' . $this->getId() . '#');
        $result['eqLogic'] = EqLogicManager::searchConfiguration(['#eqLogic' . $this->getId() . '#', '"eqLogic":"' . $this->getId()]);
        $result['interactDef'] = InteractDefManager::searchByUse(['#eqLogic' . $this->getId() . '#', '"eqLogic":"' . $this->getId()]);
        $result['scenario'] = ScenarioManager::searchByUse([
            ['action' => 'equipment', 'option' => $this->getId(), 'and' => true],
            ['action' => '#eqLogic' . $this->getId() . '#'],
        ]);
        $result['view'] = ViewManager::searchByUse('eqLogic', $this->getId());
        $result['plan'] = PlanHeaderManager::searchByUse('eqLogic', $this->getId());
        if ($arrayResult) {
            foreach ($result as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $result;
    }

    /**
     * Get id of the attached object
     *
     * @return int Id of the attached object
     */
    public function getObject_id()
    {
        return $this->object_id;
    }

    /**
     * Set attached room id
     *
     * @param int $object_id Room id
     *
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
     * Get object data in array
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toArray()
    {
        $result = Utils::o2a($this, true);
        $result['status'] = $this->getStatus();
        return $result;
    }

    /**
     * Cast from a raw eqLogic
     *
     * @param EqLogic $srcEqLogic EqLogic to cast
     *
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
     * Get all attributes of the object (used for cast)
     *
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

    /**
     * Get the name of the SQL table where data is stored.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'eqLogic';
    }
}