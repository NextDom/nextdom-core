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

use NextDom\Enums\ActionRight;
use NextDom\Enums\CmdConfigKey;
use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
use NextDom\Enums\CmdViewType;
use NextDom\Enums\Common;
use NextDom\Enums\DateFormat;
use NextDom\Enums\EventType;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Api;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\TimeLineHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\HistoryManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ListenerManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ViewDataManager;
use NextDom\Managers\ViewManager;

/**
 * Cmd
 *
 * ORM\Table(name="cmd", uniqueConstraints={
 *      ORM\UniqueConstraint(name="unique", columns={"eqLogic_id", "name"})}, indexes={
 *          ORM\Index(name="isHistorized", columns={"isHistorized"}),
 *          ORM\Index(name="type", columns={"type"}),
 *          ORM\Index(name="name", columns={"name"}),
 *          ORM\Index(name="subtype", columns={"subType"}),
 *          ORM\Index(name="eqLogic_id", columns={"eqLogic_id"}),
 *          ORM\Index(name="value", columns={"value"}),
 *          ORM\Index(name="order", columns={"order"}),
 *          ORM\Index(name="logicalID", columns={"logicalId"}),
 *          ORM\Index(name="logicalId_eqLogicID", columns={"eqLogic_id", "logicalId"}),
 *          ORM\Index(name="genericType_eqLogicID", columns={"eqLogic_id", "generic_type"})
 *      })
 * ORM\Entity
 */
class Cmd implements EntityInterface
{
    private static $_templateArray = [];
    public $_collectDate = '';
    public $_valueDate = '';
    public $_eqLogic = null;
    public $_needRefreshWidget;
    public $_needRefreshAlert;
    protected $_changed = false;

    /**
     * @var string
     *
     * @ORM\Column(name="eqType", type="string", length=127, nullable=true)
     */
    protected $eqType;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;

    /**
     * @var string
     *
     * @ORM\Column(name="generic_type", type="string", length=255, nullable=true)
     */
    protected $generic_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="text", length=65535, nullable=true)
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\Column(name="isHistorized", type="string", length=45, nullable=false)
     */
    protected $isHistorized = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="subType", type="string", length=45, nullable=true)
     */
    protected $subType;

    /**
     * @var string
     *
     * @ORM\Column(name="unite", type="string", length=45, nullable=true)
     */
    protected $unite;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;

    /**
     * @var integer
     *
     * @ORM\Column(name="isVisible", type="integer", nullable=true)
     */
    protected $isVisible = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    protected $value = null;

    /**
     * @var string
     *
     * @ORM\Column(name="html", type="text", length=16777215, nullable=true)
     */
    protected $html;

    /**
     * @var string
     *
     * @ORM\Column(name="alert", type="text", length=65535, nullable=true)
     */
    protected $alert;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var EqLogic
     *
     * ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Eqlogic")
     * ORM\JoinColumns({
     *   ORM\JoinColumn(name="eqLogic_id", referencedColumnName="id")
     * })
     */
    protected $eqLogic_id;

    /**
     * @param $_options
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function duringAlertLevel($_options)
    {
        $cmd = CmdManager::byId($_options['cmd_id']);
        if (!is_object($cmd)) {
            return;
        }
        if (!$cmd->isType(CmdType::INFO)) {
            return;
        }
        if (!is_object($cmd->getEqLogic()) || !$cmd->getEqLogic()->isEnabled()) {
            return;
        }
        $value = $cmd->execCmd();
        $level = $cmd->checkAlertLevel($value, false, $_options['level']);
        if ($level != 'none') {
            $cmd->actionAlertLevel($level, $value);
        }
    }

    /**
     * Test type of the command
     *
     * @param string $cmdType Type to test
     *
     * @return bool True on good type
     */
    public function isType(string $cmdType)
    {
        return $this->type === $cmdType;
    }

    /**
     * @return EqLogic
     * @throws \Exception
     */
    public function getEqLogic()
    {
        if ($this->_eqLogic == null) {
            $this->setEqLogic(EqLogicManager::byId($this->eqLogic_id));
        }
        return $this->_eqLogic;
    }

    /**
     * @param $_eqLogic
     * @return $this
     */
    public function setEqLogic($_eqLogic)
    {
        $this->_eqLogic = $_eqLogic;
        return $this;
    }

    /**
     *
     * @param mixed $_options
     * @param mixed $_sendNodeJsEvent
     * @param mixed $_quote
     * @return mixed result
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function execCmd($_options = null, $_sendNodeJsEvent = false, $_quote = false)
    {
        $result = null;
        if ($this->isType(CmdType::INFO)) {
            $state = $this->getCache(['collectDate', 'valueDate', 'value']);
            if (isset($state['collectDate'])) {
                $this->setCollectDate($state['collectDate']);
            } else {
                $this->setCollectDate(date(DateFormat::FULL));
            }
            if (isset($state['valueDate'])) {
                $this->setValueDate($state['valueDate']);
            } else {
                $this->setValueDate($this->getCollectDate());
            }
            return $state['value'];

        }
        $eqLogic = $this->getEqLogicId();
        if (!$this->isType(CmdType::INFO) && (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1)) {
            throw new CoreException(__('Equipement désactivé - impossible d\'exécuter la commande : ') . $this->getHumanName());
        }
        try {
            if ($_options !== null && $_options !== '') {
                $options = CmdManager::cmdToValue($_options);
                if (Utils::isJson($_options)) {
                    $options = json_decode($_options, true);
                }
            } else {
                $options = null;
            }
            if (isset($options['color'])) {
                $options['color'] = str_replace('"', '', $options['color']);
            }
            if ($this->isSubType(CmdSubType::COLOR) && isset($options['color']) && substr($options['color'], 0, 1) != '#') {
                $options['color'] = CmdManager::convertColor($options['color']);
            }
            $str_option = '';
            if (is_array($options) && ((count($options) > 1 && isset($options['uid'])) || count($options) > 0)) {
                LogHelper::addInfo(LogTarget::EVENT, __('Exécution de la commande ') . $this->getHumanName() . __(' avec les paramètres ') . json_encode($options, true));
            } else {
                LogHelper::addInfo(LogTarget::EVENT, __('Exécution de la commande ') . $this->getHumanName());
            }

            if ($this->getConfiguration(CmdConfigKey::TIMELIME_ENABLE)) {
                // @TODO: Problème Type et Subtype
                TimeLineHelper::addTimelineEvent(['type' => 'cmd', 'subtype' => 'action', 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => date(DateFormat::FULL), 'options' => $str_option]);
            }
            $this->preExecCmd($options);
            $result = $this->formatValue($this->execute($options), $_quote);
            $this->postExecCmd($options);
        } catch (\Exception $e) {
            $eqTypeName = $eqLogic->getEqType_name();
            if ($eqLogic->getConfiguration(CmdConfigKey::NEVER_FAIL) != 1) {
                $numberTryWithoutSuccess = $eqLogic->getStatus('numberTryWithoutSuccess', 0);
                $eqLogic->setStatus('numberTryWithoutSuccess', $numberTryWithoutSuccess);
                if ($numberTryWithoutSuccess >= ConfigManager::byKey('numberOfTryBeforeEqLogicDisable')) {
                    $message = 'Désactivation de <a href="' . $eqLogic->getLinkToConfiguration() . '">' . $eqLogic->getName();
                    $message .= '</a> ' . __('car il n\'a pas répondu ou mal répondu lors des 3 derniers essais');
                    MessageManager::add($eqTypeName, $message);
                    $eqLogic->setIsEnable(0);
                    $eqLogic->save();
                }
            }
            LogHelper::addError($eqTypeName, __('Erreur exécution de la commande ') . $this->getHumanName() . ' : ' . $e->getMessage());
            throw $e;
        }
        if ($options !== null && $this->getValue() == '') {
            if (isset($options['slider'])) {
                $this->setConfiguration(CmdConfigKey::LAST_CMD_VALUE, $options['slider']);
                $this->save();
            }
            if (isset($options['color'])) {
                $this->setConfiguration(CmdConfigKey::LAST_CMD_VALUE, $options['color']);
                $this->save();
            }
        }
        if ($this->getConfiguration(CmdConfigKey::UPDATE_CMD_ID) != '') {
            $cmd = CmdManager::byId($this->getConfiguration(CmdConfigKey::UPDATE_CMD_ID));
            if (is_object($cmd)) {
                $result = $this->getConfiguration('updateCmdToValue');
                switch ($this->getSubType()) {
                    case CmdSubType::SLIDER:
                        $result = str_replace('#slider#', $options['slider'], $result);
                        break;
                    case CmdSubType::COLOR:
                        $result = str_replace('#color#', $options['color'], $result);
                        break;
                }
                $cmd->event($result);
            }
        }
        return $result;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getCache($_key = '', $_default = '')
    {
        $cache = CacheManager::byKey(CmdConfigKey::CMD_CACHE_ATTR . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $_key, $_default);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $id);
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectDate()
    {
        return $this->_collectDate;
    }

    /**
     * @param $_collectDate
     * @return $this
     */
    public function setCollectDate($_collectDate)
    {
        $this->_collectDate = $_collectDate;
        return $this;
    }

    /**
     * @param bool $useTag
     * @param bool $prettify
     * @return string
     * @throws \Exception
     */
    public function getHumanName($useTag = false, $prettify = false)
    {
        $humanName = '';
        $eqLogic = $this->getEqLogicId();
        if (is_object($eqLogic)) {
            $humanName .= $eqLogic->getHumanName($useTag, $prettify);
        }
        if ($useTag) {
            $humanName .= ' - ' . $this->getName();
        } else {
            $humanName .= '[' . $this->getName() . ']';
        }
        return $humanName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $_name
     * @return $this
     */
    public function setName($_name)
    {
        $_name = Utils::cleanComponentName($_name);
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    /**
     * Test sub type of the command
     *
     * @param string $cmdSubType Subype to test
     *
     * @return bool True on good type
     */
    public function isSubType(string $cmdSubType)
    {
        return $this->subType === $cmdSubType;
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
     * Save configuration
     * @param $configKey
     * @param $configValue
     * @return $this
     */
    public function setConfiguration($configKey, $configValue)
    {
        if ($configKey == CmdConfigKey::ACTION_CODE_ACCESS && $configValue != ''
            && !Utils::isSha1($configValue) && !Utils::isSha512($configValue)) {
            $configValue = Utils::sha512($configValue);
        }
        $configuration = Utils::setJsonAttr($this->configuration, $configKey, $configValue);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Execute before command execution
     *
     * @param array $_values
     *
     * @throws \Exception
     */
    public function preExecCmd($_values = [])
    {
        if (!is_array($this->getConfiguration(CmdConfigKey::NEXTDOM_PRE_EXEC_CMD)) || count($this->getConfiguration(CmdConfigKey::NEXTDOM_PRE_EXEC_CMD)) == 0) {
            return;
        }
        foreach ($this->getConfiguration(CmdConfigKey::NEXTDOM_PRE_EXEC_CMD) as $action) {
            try {
                $options = [];
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                if (is_array($_values) && count($_values) > 0) {
                    foreach ($_values as $key => $value) {
                        foreach ($options as &$option) {
                            if (!is_array($option)) {
                                $option = str_replace('#' . $key . '#', $value, $option);
                            }
                        }
                    }
                }
                ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::CMD, __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Sur preExec de la commande') . $this->getHumanName() . __('. Détails : ') . $e->getMessage());
            }
        }
    }

    /**
     * @param $_value
     * @param bool $_quote
     * @return float|mixed|string
     */
    public function formatValue($_value, $_quote = false)
    {
        if (is_array($_value)) {
            return '';
        }
        if (trim($_value) == '' && $_value !== false && $_value !== 0) {
            return '';
        }
        $_value = trim(trim($_value), '"');
        if (@strpos(strtolower($_value), 'error::') !== false) {
            return $_value;
        }
        if ($this->isType(CmdType::INFO)) {
            switch ($this->getSubType()) {
                case CmdSubType::OTHER:
                case CmdSubType::STRING:
                    if ($_quote) {
                        return '"' . $_value . '"';
                    }
                    return $_value;
                case CmdSubType::BINARY:
                    if ($this->getConfiguration(CmdConfigKey::CALCUL_VALUE_OFFSET) != '') {
                        try {
                            if (preg_match("/[a-zA-Z#]/", $_value)) {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', '"' . $_value . '"', str_replace('\'#value#\'', '#value#', str_replace('"#value#"', '#value#', $this->getConfiguration(CmdConfigKey::CALCUL_VALUE_OFFSET)))));
                            } else {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $this->getConfiguration(CmdConfigKey::CALCUL_VALUE_OFFSET)));
                            }
                        } catch (\Exception $ex) {

                        }
                    }
                    $parsedBinary = strtolower($_value);
                    if ($parsedBinary == 'on' || $parsedBinary == 'high' || $parsedBinary == 'true' || $parsedBinary === true) {
                        return 1;
                    }
                    if ($parsedBinary == 'off' || $parsedBinary == 'low' || $parsedBinary == 'false' || $parsedBinary === false) {
                        return 0;
                    }
                    if ((is_numeric(intval($_value)) && intval($_value) > 1) || $_value === true || $_value == 1) {
                        return 1;
                    }
                    return 0;
                case CmdSubType::NUMERIC:
                    $_value = floatval(str_replace(',', '.', $_value));
                    $calculValueOffset = $this->getConfiguration(CmdConfigKey::CALCUL_VALUE_OFFSET);
                    if ($calculValueOffset != '') {
                        try {
                            if (preg_match("/[a-zA-Z#]/", $_value)) {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', '"' . $_value . '"', str_replace('\'#value#\'', '#value#', str_replace('"#value#"', '#value#', $calculValueOffset))));
                            } else {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $calculValueOffset));
                            }
                        } catch (\Exception $ex) {

                        }
                    }
                    if ($this->getConfiguration(CmdConfigKey::HISTORIZE_ROUND) !== '' && is_numeric($this->getConfiguration(CmdConfigKey::HISTORIZE_ROUND)) && $this->getConfiguration(CmdConfigKey::HISTORIZE_ROUND) >= 0) {
                        $_value = round($_value, $this->getConfiguration(CmdConfigKey::HISTORIZE_ROUND));
                    }
                    if ($_value > $this->getConfiguration(CmdConfigKey::MAX_VALUE, $_value) && $this->getConfiguration(CmdConfigKey::MAX_VALUE_REPLACE) == 1) {
                        $_value = $this->getConfiguration(CmdConfigKey::MAX_VALUE, $_value);
                    }
                    if ($_value < $this->getConfiguration(CmdConfigKey::MIN_VALUE, $_value) && $this->getConfiguration(CmdConfigKey::MIN_VALUE_REPLACE) == 1) {
                        $_value = $this->getConfiguration(CmdConfigKey::MIN_VALUE, $_value);
                    }
                    return floatval($_value);
            }
        }
        return $_value;
    }

    /**
     * Get sub type of the command
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @param $_subType
     * @return $this
     */
    public function setSubType($_subType)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->subType, $_subType);
        $this->subType = $_subType;
        return $this;
    }

    /**
     * Execute command overrided by plugins
     *
     * @param array $options Execute options
     *
     * @return bool Always false if not overrided
     */
    public function execute($options = null)
    {
        return false;
    }

    /**
     * Executed after execution
     *
     * @param array $_values
     *
     * @throws \Exception
     */
    public function postExecCmd($_values = [])
    {
        if (!is_array($this->getConfiguration(CmdConfigKey::NEXTDOM_POST_EXEC_CMD))) {
            return;
        }
        foreach ($this->getConfiguration(CmdConfigKey::NEXTDOM_POST_EXEC_CMD) as $action) {
            try {
                $options = [];
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                if (count($_values) > 0) {
                    foreach ($_values as $key => $value) {
                        foreach ($options as &$option) {
                            if (!is_array($option)) {
                                $option = str_replace('#' . $key . '#', $value, $option);
                            }
                        }
                    }
                }
                ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
            } catch (\Exception $e) {
                LogHelper::addError('cmd', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Sur preExec de la commande') . $this->getHumanName() . __('. Détails : ') . $e->getMessage());
            }
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $_value
     * @return $this
     */
    public function setValue($_value)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->value, $_value);
        $this->value = $_value;
        return $this;
    }

    /**
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if (empty($this->getName())) {
            throw new CoreException(__('Le nom de la commande ne peut pas être vide :') . print_r($this, true));
        }
        if (empty($this->getType())) {
            throw new CoreException($this->getHumanName() . ' ' . __('Le type de la commande ne peut pas être vide :') . print_r($this, true));
        }
        if (empty($this->getSubType())) {
            throw new CoreException($this->getHumanName() . ' ' . __('Le sous-type de la commande ne peut pas être vide :') . print_r($this, true));
        }
        if (empty($this->getEqLogic_id())) {
            throw new CoreException($this->getHumanName() . ' ' . __('Vous ne pouvez pas créer une commande sans la rattacher à un équipement'));
        }
        if ($this->getConfiguration(CmdConfigKey::MAX_VALUE) != '' && $this->getConfiguration(CmdConfigKey::MIN_VALUE) != '' && $this->getConfiguration(CmdConfigKey::MIN_VALUE) > $this->getConfiguration(CmdConfigKey::MAX_VALUE)) {
            throw new CoreException($this->getHumanName() . ' ' . __('La valeur minimum de la commande ne peut etre supérieure à la valeur maximum'));
        }
        if ($this->getEqType() == '') {
            $this->setEqType($this->getEqLogicId()->getEqType_name());
        }
        if ($this->getDisplay('generic_type') != '' && $this->getGeneric_type() == '') {
            $this->setGeneric_type($this->getDisplay('generic_type'));
            $this->setDisplay('generic_type', null);
        }
        if ($this->isType(CmdType::ACTION) && $this->getIsHistorized() == 1) {
            $this->setIsHistorized(0);
        }
        DBHelper::save($this);
        if ($this->_needRefreshWidget) {
            $this->_needRefreshWidget = false;
            $this->getEqLogicId()->refreshWidget();
        }
        if ($this->_needRefreshAlert && $this->isType(CmdType::INFO)) {
            $execCmdValue = $this->execCmd();
            $level = $this->checkAlertLevel($execCmdValue);
            if ($level != $this->getCache('alertLevel')) {
                $this->actionAlertLevel($level, $execCmdValue);
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $_type
     * @return $this
     */
    public function setType($_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getEqLogic_Id()
    {
        return $this->eqLogic_id;
    }

    /**
     * @return EqLogic|null
     * @throws \Exception
     */
    public function getEqLogicId()
    {
        if ($this->_eqLogic == null) {
            $this->setEqLogicId(EqLogicManager::byId($this->eqLogic_id));
        }
        return $this->_eqLogic;
    }

    /**
     *
     * @param $_eqLogic_id
     * @return $this
     */
    public function setEqLogic_id($_eqLogic_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->eqLogic_id, $_eqLogic_id);
        $this->eqLogic_id = $_eqLogic_id;
        return $this;
    }

    /**
     * @param $_eqLogic
     * @return $this
     */
    public function setEqLogicId($_eqLogic)
    {
        $this->_eqLogic = $_eqLogic;
        return $this;
    }

    /**
     * @return string
     */
    public function getEqType()
    {
        return $this->eqType;
    }

    /**
     * @param $_eqType
     * @return $this
     */
    public function setEqType($_eqType)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->eqType, $_eqType);
        $this->eqType = $_eqType;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getDisplay($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->display, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setDisplay($_key, $_value)
    {
        if ($this->getDisplay($_key) != $_value) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->display = Utils::setJsonAttr($this->display, $_key, $_value);
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneric_type()
    {
        return $this->generic_type;
    }

    /**
     * @param $generic_type
     * @return $this
     */
    public function setGeneric_type($generic_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->generic_type, $generic_type);
        $this->generic_type = $generic_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsHistorized()
    {
        return $this->isHistorized;
    }

    /**
     * @param $_isHistorized
     * @return $this
     */
    public function setIsHistorized($_isHistorized)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->isHistorized, $_isHistorized);
        $this->isHistorized = $_isHistorized;
        return $this;
    }

    /**
     * @param $_value
     * @param bool $_allowDuring
     * @param string $_checkLevel
     * @return int|string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function checkAlertLevel($_value, $_allowDuring = true, $_checkLevel = 'none')
    {
        if (!$this->isType(CmdType::INFO) || ($this->getAlert('warningif') == '' && $this->getAlert('dangerif') == '')) {
            return 'none';
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        $returnLevel = 'none';
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
            if ($this->getAlert($level . 'if') != '') {
                $check = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $this->getAlert($level . 'if')));
                if ($check == 1 || $check || $check == '1') {
                    $currentLevel = $level;
                    if ($_allowDuring && $currentLevel != 'none' && $this->getAlert($currentLevel . 'during') != '' && $this->getAlert($currentLevel . 'during') > 0) {
                        $cron = CronManager::byClassAndFunction('cmd', 'duringAlertLevel', ['cmd_id' => intval($this->getId()), 'level' => $currentLevel]);
                        $next = strtotime('+ ' . $this->getAlert($currentLevel . 'during', 1) . ' minutes ' . date(DateFormat::FULL));
                        if ($currentLevel != $this->getCache('alertLevel')) {
                            if (!is_object($cron)) {
                                if (!($currentLevel == 'warning' && $this->getCache('alertLevel') == 'danger')) {
                                    $cron = new Cron();
                                    $cron->setClass(NextDomObj::CMD);
                                    $cron->setFunction('duringAlertLevel');
                                    $cron->setOnce(1);
                                    $cron->setOption(['cmd_id' => intval($this->getId()), 'level' => $currentLevel]);
                                    $cron->setSchedule(CronManager::convertDateToCron($next));
                                    $cron->setLastRun(date(DateFormat::FULL));
                                    $cron->save();
                                } else { //je suis en condition de warning et le cron n'existe pas mais j'etais en danger, je suppose que le cron a expiré
                                    $returnLevel = $currentLevel;
                                }
                            }
                        } else { // il n'y a pas de cron mais j'etais deja dans ce niveau, j'y reste
                            $returnLevel = $this->getCache('alertLevel');
                        }
                    }
                    if (!($_allowDuring && $this->getAlert($currentLevel . 'during') != '' && $this->getAlert($currentLevel . 'during') > 0)) { //je suis en alerte sans delai ou en execution de cron
                        if ($_checkLevel == $currentLevel || $_checkLevel == 'none') { //si c'etait un cron, je ne teste que le niveau demandé
                            if (!($_checkLevel == 'warning' && $this->getCache('alertLevel') == 'danger')) {
                                $returnLevel = $currentLevel;
                            } else { // le cron me demande de passer en warning mais je suis deja en danger, je reste en danger
                                $returnLevel = $this->getCache('alertLevel');
                            }
                        }
                    }
                } else { // je ne suis pas dans la condition, je supprime le cron
                    $cron = CronManager::byClassAndFunction(NextDomObj::CMD, 'duringAlertLevel', ['cmd_id' => intval($this->getId()), 'level' => $level]);
                    if (is_object($cron)) {
                        $cron->remove(false);
                    }
                }
            }
        }
        return $returnLevel;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getAlert($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->alert, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setAlert($_key, $_value)
    {
        $alert = Utils::setJsonAttr($this->alert, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->alert, $alert);
        $this->alert = $alert;
        $this->_needRefreshAlert = true;
        return $this;
    }

    /**
     * @param $_level
     * @param $_value
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function actionAlertLevel($_level, $_value)
    {
        if (!$this->isType(CmdType::INFO)) {
            return;
        }
        if ($_level == $this->getCache('alertLevel')) {
            return;
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        $this->setCache('alertLevel', $_level);
        $eqLogic = $this->getEqLogic();
        if (!$eqLogic->isEnabled()) {
            return;
        }
        $maxAlert = $eqLogic->getMaxCmdAlert();
        $prevAlert = $eqLogic->getAlert();
        if (!$_value) {
            $_value = $this->execCmd();
        }
        if ($_level != 'none') {
            $message = __('Alert sur la commande ') . $this->getHumanName() . __(' niveau ') . $_level . __(' valeur : ') . $_value . trim(' ' . $this->getUnite());
            if ($this->getAlert($_level . 'during') != '' && $this->getAlert($_level . 'during') > 0) {
                $message .= ' ' . __('pendant plus de ') . $this->getAlert($_level . 'during') . __(' minute(s)');
            }
            $message .= ' => ' . NextDomHelper::toHumanReadable(str_replace('#value#', $_value, $this->getAlert($_level . 'if')));
            LogHelper::addInfo(LogTarget::EVENT, $message);
            $eqLogic = $this->getEqLogicId();
            if (ConfigManager::byKey('alert::addMessageOn' . ucfirst($_level)) == 1) {
                MessageManager::add($eqLogic->getEqType_name(), $message);
            }
            $cmds = explode(('&&'), ConfigManager::byKey('alert::' . $_level . 'Cmd'));
            if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::' . $_level . 'Cmd')) != '') {
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

        if ($prevAlert != $maxAlert) {
            $status = [
                'warning' => 0,
                'danger' => 0,
            ];
            if ($maxAlert != 'none' && isset($NEXTDOM_INTERNAL_CONFIG['alerts'][$maxAlert])) {
                $status[$maxAlert] = 1;
            }
            $eqLogic->setStatus($status);
            $eqLogic->refreshWidget();
        }
    }

    /**
     * @param $_key
     * @param null $_value
     * @return $this
     * @throws \Exception
     */
    public function setCache($_key, $_value = null)
    {
        CacheManager::set(CmdConfigKey::CMD_CACHE_ATTR . $this->getId(), Utils::setJsonAttr(CacheManager::byKey(CmdConfigKey::CMD_CACHE_ATTR . $this->getId())->getValue(), $_key, $_value));
        return $this;
    }

    /**
     * @return string
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * @param $_unite
     * @return $this
     */
    public function setUnite($_unite)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->unite, $_unite);
        $this->unite = $_unite;
        return $this;
    }

    /**
     * @param $eventValue
     * @param null $_datetime
     * @param int $eventLoop
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function event($eventValue, $_datetime = null, $eventLoop = 1)
    {
        if ($eventLoop > 4 || !$this->isType(CmdType::INFO)) {
            return;
        }
        $eqLogic = $this->getEqLogicId();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() == 0) {
            return;
        }
        $eventValue = $this->formatValue($eventValue);
        if ($this->isSubType(CmdSubType::NUMERIC) && ($eventValue > $this->getConfiguration(CmdConfigKey::MAX_VALUE, $eventValue) || $eventValue < $this->getConfiguration(CmdConfigKey::MIN_VALUE, $eventValue)) && strpos($eventValue, 'error') === false) {
            LogHelper::addInfo(LogTarget::CMD, __('La commande n\'est pas dans la plage de valeur autorisée : ') . $this->getHumanName() . ' => ' . $eventValue);
            return;
        }
        if ($this->getConfiguration(CmdConfigKey::DENY_VALUES) != '' && in_array($eventValue, explode(';', $this->getConfiguration(CmdConfigKey::DENY_VALUES)))) {
            return;
        }
        $oldValue = $this->execCmd();
        $repeat = ($oldValue == $eventValue && $oldValue !== '' && $oldValue !== null);
        $this->setCollectDate(($_datetime != null) ? $_datetime : date(DateFormat::FULL));
        $this->setCache('collectDate', $this->getCollectDate());
        $this->setValueDate(($repeat) ? $this->getValueDate() : $this->getCollectDate());
        $eqLogic->setStatus(['lastCommunication' => $this->getCollectDate(), 'timeout' => 0]);
        $display_value = $eventValue;
        if (method_exists($this, 'formatValueWidget')) {
            $display_value = $this->formatValueWidget($eventValue);
        } elseif ($this->isSubType(CmdSubType::BINARY) && $this->getDisplay('invertBinary') == 1) {
            $display_value = ($eventValue == 1) ? 0 : 1;
        } elseif ($this->isSubType(CmdSubType::NUMERIC) && trim($eventValue) == '') {
            $display_value = 0;
        } elseif ($this->isSubType(CmdSubType::BINARY) && trim($eventValue) == '') {
            $display_value = 0;
        }
        if ($repeat && $this->getConfiguration(CmdConfigKey::REPEAT_EVENT_MGMT, 'auto') == 'never') {
            $this->addHistoryValue($eventValue, $this->getCollectDate());
            $eqLogic->emptyCacheWidget();
            EventManager::adds(EventType::CMD_UPDATE, [['cmd_id' => $this->getId(), 'value' => $eventValue, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate()]]);
            return;
        }
        $eventLoop++;
        if ($repeat && ($this->getConfiguration(CmdConfigKey::REPEAT_EVENT_MGMT, 'auto') == 'always' || $this->isSubType(CmdSubType::BINARY))) {
            $repeat = false;
        }
        $message = __('Evènement sur la commande ') . $this->getHumanName() . __(' valeur : ') . $eventValue;
        if ($repeat) {
            $message .= ' (répétition)';
        }
        LogHelper::addInfo(LogTarget::EVENT, $message);
        $events = [];
        if (!$repeat) {
            $this->setCache(['value' => $eventValue, 'valueDate' => $this->getValueDate()]);
            ScenarioManager::check($this);
            $eqLogic->emptyCacheWidget();
            $level = $this->checkAlertLevel($eventValue);
            $events[] = ['cmd_id' => $this->getId(), 'value' => $eventValue, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate(), 'alertLevel' => $level];
            $foundInfo = false;
            $cmds = CmdManager::byValue($this->getId(), null, true);
            if (is_array($cmds) && count($cmds) > 0) {
                /** @var Cmd $cmd */
                foreach ($cmds as $cmd) {
                    if ($cmd->isType(CmdType::ACTION)) {
                        if (!$repeat) {
                            $events[] = ['cmd_id' => $cmd->getId(), 'value' => $eventValue, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate()];
                        }
                    } else {
                        if ($eventLoop > 1) {
                            $cmd->event($cmd->execute(), null, $eventLoop);
                        } else {
                            $foundInfo = true;
                        }
                    }
                }
            }
            if ($foundInfo) {
                ListenerManager::backgroundCalculDependencyCmd($this->getId());
            }
        } else {
            $events[] = ['cmd_id' => $this->getId(), 'value' => $eventValue, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate()];
        }
        if (count($events) > 0) {
            EventManager::adds(EventType::CMD_UPDATE, $events);
        }
        if (!$repeat) {
            ListenerManager::check($this->getId(), $eventValue, $this->getCollectDate());
            JeeObjectManager::checkSummaryUpdate($this->getId());
        }
        $this->addHistoryValue($eventValue, $this->getCollectDate());
        $this->checkReturnState($eventValue);
        if (!$repeat) {
            $this->checkCmdAlert($eventValue);
            if (isset($level) && $level != $this->getCache('alertLevel')) {
                $this->actionAlertLevel($level, $eventValue);
            }
            if ($this->getConfiguration(CmdConfigKey::TIMELIME_ENABLE)) {
                // @TODO: Il doit y avoir un problème avec les Types et SubType
                TimeLineHelper::addTimelineEvent(['type' => 'cmd', 'subtype' => 'info', 'cmdType' => $this->getSubType(), 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => $this->getValueDate(), 'value' => $eventValue . $this->getUnite()]);
            }
            $this->pushUrl($eventValue);
        }
        $this->influxDb($eventValue);
    }

    /**
     * @return string
     */
    public function getValueDate()
    {
        return $this->_valueDate;
    }

    /**
     * @param $_valueDate
     * @return $this
     */
    public function setValueDate($_valueDate)
    {
        $this->_valueDate = $_valueDate;
        return $this;
    }

    /**
     * @param $_value
     * @param string $_datetime
     *
     * @throws CoreException
     */
    public function addHistoryValue($_value, $_datetime = '')
    {
        if ($this->getIsHistorized() == 1 && ($_value == null || ($_value !== '' && $this->isType(CmdType::INFO) && $_value <= $this->getConfiguration(CmdConfigKey::MAX_VALUE, $_value) && $_value >= $this->getConfiguration(CmdConfigKey::MIN_VALUE, $_value)))) {
            $history = new History();
            $history->setCmd_id($this->getId());
            $history->setValue($_value);
            $history->setDatetime($_datetime);
            $history->save($this);
        }
    }

    /**
     * @param $_value
     * @throws \Exception
     */
    public function checkReturnState($_value)
    {
        if (is_numeric($this->getConfiguration(CmdConfigKey::RETURN_STATE_TIME))
            && $this->getConfiguration(CmdConfigKey::RETURN_STATE_TIME) > 0
            && $_value != $this->getConfiguration(CmdConfigKey::RETURN_STATE_VALUE)
            && trim($this->getConfiguration(CmdConfigKey::RETURN_STATE_VALUE)) != '') {
            $cron = CronManager::byClassAndFunction('cmd', 'returnState', ['cmd_id' => intval($this->getId())]);
            if (!is_object($cron)) {
                $cron = new cron();
            }
            $cron->setClass('cmd');
            $cron->setFunction('returnState');
            $cron->setOnce(1);
            $cron->setOption(['cmd_id' => intval($this->getId())]);
            $next = strtotime('+ ' . ($this->getConfiguration(CmdConfigKey::RETURN_STATE_TIME) + 1) . ' minutes ' . date(DateFormat::FULL));
            $cron->setSchedule(CronManager::convertDateToCron($next));
            $cron->setLastRun(date(DateFormat::FULL));
            $cron->save();
        }
    }

    /**
     * @param $_value
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function checkCmdAlert($_value)
    {
        if ($this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_OPERATOR) == '' || $this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_TEST) == '' || is_nan($this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_TIME, 0))) {
            return;
        }
        $check = NextDomHelper::evaluateExpression($_value . $this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_OPERATOR) . $this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_TEST));
        if ($check == 1 || $check || $check == '1') {
            if ($this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_TIME, 0) == 0) {
                $this->executeAlertCmdAction();
                return;
            }
            $next = strtotime('+ ' . ($this->getConfiguration(CmdConfigKey::NEXTDOM_CHECK_CMD_TIME) + 1) . ' minutes ' . date(DateFormat::FULL));
            $cron = CronManager::byClassAndFunction('cmd', 'cmdAlert', ['cmd_id' => intval($this->getId())]);
            if (!is_object($cron)) {
                $cron = new cron();
            } else {
                $nextRun = $cron->getNextRunDate();
                if ($nextRun !== false && $next > strtotime($nextRun) && strtotime($nextRun) > strtotime('now')) {
                    return;
                }
            }
            $cron->setClass('cmd');
            $cron->setFunction('cmdAlert');
            $cron->setOnce(1);
            $cron->setOption(['cmd_id' => intval($this->getId())]);
            $cron->setSchedule(CronManager::convertDateToCron($next));
            $cron->setLastRun(date(DateFormat::FULL));
            $cron->save();
        } else {
            $cron = CronManager::byClassAndFunction('cmd', 'cmdAlert', ['cmd_id' => intval($this->getId())]);
            if (is_object($cron)) {
                $cron->remove();
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function executeAlertCmdAction()
    {
        if (!is_array($this->getConfiguration(CmdConfigKey::ACTION_CHECK_CMD))) {
            return;
        }
        foreach ($this->getConfiguration(CmdConfigKey::ACTION_CHECK_CMD) as $action) {
            try {
                $options = [];
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
            } catch (\Exception $e) {
                LogHelper::addError('cmd', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Détails : ') . $e->getMessage());
            }
        }
    }

    /**
     * @param $_value
     * @throws \Exception
     */
    public function pushUrl($_value)
    {
        $url = $this->getConfiguration(CmdConfigKey::NEXTDOM_PUSH_URL);
        if ($url == '') {
            $url = ConfigManager::byKey('cmdPushUrl');
        }
        if ($url == '') {
            return;
        }
        $replace = [
            '#value#' => urlencode($_value),
            '#cmd_name#' => urlencode($this->getName()),
            '#cmd_id#' => $this->getId(),
            '#humanname#' => urlencode($this->getHumanName()),
            '#eq_name#' => urlencode($this->getEqLogicId()->getName()),
            '"' => ''
        ];
        $url = str_replace(array_keys($replace), $replace, $url);
        LogHelper::addInfo(LogTarget::EVENT, __('Appels de l\'URL de push pour la commande ') . $this->getHumanName() . ' : ' . $url);
        $http = new \com_http($url);
        $http->setLogError(false);
        try {
            $http->exec();
        } catch (\Exception $e) {
            LogHelper::addError('cmd', __('Erreur push sur : ') . $url . ' => ' . $e->getMessage());
        }
    }

    /**
     * @param $valueToSend
     * @throws \Exception
     */
    public function influxDb($valueToSend)
    {
        $influxDbConf = ConfigManager::byKeys(['influxDbIp', 'influxDbPort', 'influxDbDatabase']);
        if ($influxDbConf['influxDbIp'] !== '') {
            if (empty($this->getUnite())) {
                $unite = 'state';
            } else {
                $unite = $this->getUnite();
            }

            if ($this->isType(CmdType::INFO)
                && ($this->isSubType(CmdSubType::NUMERIC) || $this->isSubType(CmdSubType::BINARY))) {
                $client = new \InfluxDB\Client($influxDbConf['influxDbIp'], $influxDbConf['influxDbPort']);
                $influxDbDatabase = $client->selectDB($influxDbConf['influxDbDatabase']);

                $points = [
                    new \InfluxDB\Point(
                        $unite,
                        $valueToSend,
                        ['equipment' => $this->getHumanName()]
                    ),
                ];

                $influxDbDatabase->writePoints($points);
            }
        }
    }

    /**
     * @return string
     */
    public function getEqType_name()
    {
        @trigger_error('This method is deprecated. Use getEqType', E_USER_DEPRECATED);
        return $this->eqType;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        if ($this->order == '') {
            return 0;
        }
        return $this->order;
    }

    /**
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        if ($this->order != $order) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->order = $order;
        return $this;
    }

    /**
     * Get visibility state
     *
     * @return bool True if visible
     */
    public function isVisible()
    {
        return $this->getIsvisible() == 1;
    }

    /**
     * @return int
     */
    public function getIsvisible()
    {
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
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getHtml($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->html, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     * @throws \Exception
     */
    public function setHtml($_key, $_value)
    {
        if (in_array($_key, ['dashboard', 'dview', 'mview', 'dplan']) && $this->getWidgetTemplateCode($_key, true) == $_value) {
            $_value = '';
        }
        if ($this->getHtml($_key) != $_value) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->html = Utils::setJsonAttr($this->html, $_key, $_value);
        return $this;
    }

    /**
     * TODO: Déplacer dans CmdManager ???
     *
     * @param string $viewVersion
     * @param bool $_noCustom
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getWidgetTemplateCode($viewVersion = CmdViewType::DASHBOARD, $_noCustom = false)
    {
        $version = NextDomHelper::versionAlias($viewVersion);

        $templateName = 'cmd.' . $this->getType() . '.' . $this->getSubType() . '.' . $this->getTemplate($version, 'default');
        $cacheKey = $version . '::' . $templateName;
        if (!isset(self::$_templateArray[$cacheKey])) {
            $templateContent = FileSystemHelper::getCoreTemplateFileContent($version, $templateName, '');
            if ($templateContent == '') {
                if (ConfigManager::byKey('active', 'widget') == 1) {
                    $templateContent = FileSystemHelper::getCoreTemplateFileContent($version, $templateName, 'widget');
                }
                if ($templateContent == '') {
                    foreach (PluginManager::listPlugin(true) as $plugin) {
                        $templateContent = FileSystemHelper::getCoreTemplateFileContent($version, $templateName, $plugin->getId());
                        if ($templateContent != '') {
                            break;
                        }
                    }
                }
                if ($templateContent == '') {
                    $templateName = 'cmd.' . $this->getType() . '.' . $this->getSubType() . '.default';
                    $templateContent = FileSystemHelper::getCoreTemplateFileContent($version, $templateName, '');
                }
            }
            self::$_templateArray[$cacheKey] = $templateContent;
        } else {
            $templateContent = self::$_templateArray[$cacheKey];
        }
        return $templateContent;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getTemplate($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->template, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setTemplate($_key, $_value)
    {
        if ($this->getTemplate($_key) != $_value) {
            $this->_needRefreshWidget = true;
            $this->_changed = true;
        }
        $this->template = Utils::setJsonAttr($this->template, $_key, $_value);
        return $this;
    }

    /**
     * @return int
     */
    public function getEventOnly()
    {
        return 1;
    }

    public function setEventOnly()
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
    }

    /**
     * @return bool
     */
    public function dontRemoveCmd()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'cmd';
    }

    public function refresh()
    {
        DBHelper::refresh($this);
    }

    /**
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        ViewDataManager::removeByTypeLinkId('cmd', $this->getId());
        DataStoreManager::removeByTypeLinkId('cmd', $this->getId());
        $this->getEqLogicId()->emptyCacheWidget();
        $this->emptyHistory();
        CacheManager::delete(CmdConfigKey::CMD_CACHE_ATTR . $this->getId());
        CacheManager::delete('cmd' . $this->getId());
        NextDomHelper::addRemoveHistory(['id' => $this->getId(), 'name' => $this->getHumanName(), 'date' => date(DateFormat::FULL), 'type' => 'cmd']);
        return DBHelper::remove($this);
    }

    /**
     * @param string $_date
     * @return array|mixed|null
     * @throws CoreException
     */
    public function emptyHistory($_date = '')
    {
        return HistoryManager::emptyHistory($this->getId(), $_date);
    }

    /**
     * @param string $viewVersion
     * @param string $_options
     * @param null $_cmdColor
     * @return mixed|string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function toHtml($viewVersion = CmdViewType::DASHBOARD, $_options = '', $_cmdColor = null)
    {
        $version2 = NextDomHelper::versionAlias($viewVersion, false);
        if ($this->getDisplay('showOn' . $version2, 1) == 0) {
            return '';
        }
        $version = NextDomHelper::versionAlias($viewVersion);
        $htmlRender = '';
        $htmlData = [
            '#id#' => $this->getId(),
            '#name#' => $this->getName(),
            '#name_display#' => ($this->getDisplay('icon') != '') ? $this->getDisplay('icon') : $this->getName(),
            '#history#' => '',
            '#displayHistory#' => 'display : none;',
            '#unite#' => $this->getUnite(),
            '#minValue#' => $this->getConfiguration(CmdConfigKey::MIN_VALUE, 0),
            '#maxValue#' => $this->getConfiguration(CmdConfigKey::MAX_VALUE, 100),
            '#logicalId#' => $this->getLogicalId(),
            '#uid#' => 'cmd' . $this->getId() . EqLogic::UIDDELIMITER . mt_rand() . EqLogic::UIDDELIMITER,
            '#version#' => $viewVersion,
            '#eqLogic_id#' => $this->getEqLogic_id(),
            '#generic_type#' => $this->getGeneric_type(),
            '#hideCmdName#' => '',
        ];
        if ($this->getConfiguration(CmdConfigKey::LIST_VALUE, '') != '') {
            $listOption = '';
            $elements = explode(';', $this->getConfiguration(CmdConfigKey::LIST_VALUE, ''));
            $foundSelect = false;
            foreach ($elements as $element) {
                $coupleArray = explode('|', $element);
                $cmdValue = $this->getCmdValue();
                if (is_object($cmdValue) && $cmdValue->isType(CmdType::INFO)) {
                    if ($cmdValue->execCmd() == $coupleArray[0]) {
                        $listOption .= '<option value="' . $coupleArray[0] . '" selected>' . $coupleArray[1] . '</option>';
                        $foundSelect = true;
                    } else {
                        $listOption .= '<option value="' . $coupleArray[0] . '">' . $coupleArray[1] . '</option>';
                    }
                } else {
                    $listOption .= '<option value="' . $coupleArray[0] . '">' . $coupleArray[1] . '</option>';
                }
            }
            if (!$foundSelect) {
                $listOption = '<option value="">' . __('Aucun') . '</option>' . $listOption;
            }
            $htmlData['#listValue#'] = $listOption;
        }
        if ($this->getDisplay('showNameOn' . $version2, 1) == 0) {
            $htmlData['#hideCmdName#'] = 'display:none;';
        }
        if ($this->getDisplay('showIconAndName' . $version2, 0) == 1) {
            $htmlData['#name_display#'] = $this->getDisplay('icon') . ' ' . $this->getName();
        }
        $templateCode = $this->getWidgetTemplateCode($viewVersion);

        if ($_cmdColor == null && $version != 'scenario') {
            $eqLogic = $this->getEqLogicId();
            if ($eqLogic->getPrimaryCategory() == '') {
                $htmlData['#cmdColor#'] = NextDomHelper::getConfiguration('eqLogic:category:default:cmdColor');
            } else {
                $htmlData['#cmdColor#'] = NextDomHelper::getConfiguration('eqLogic:category:' . $eqLogic->getPrimaryCategory() . ':cmdColor');
            }
        } else {
            $htmlData['#cmdColor#'] = $_cmdColor;
        }

        if ($this->isType(CmdType::INFO)) {
            $this->addDataForInfoCmdRender($htmlData, $version, $version2, $templateCode);
            return Utils::templateReplace($htmlData, $templateCode);
        } else {
            $htmlRender = $this->addDataForOthersCmdRender($htmlData, $htmlRender, $_options, $templateCode);
            return Utils::templateReplace($htmlData, $htmlRender);
        }
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param $_logicalId
     * @return $this
     */
    public function setLogicalId($_logicalId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->logicalId, $_logicalId);
        $this->logicalId = $_logicalId;
        return $this;
    }

    /**
     * @return Cmd|bool
     * @throws \Exception
     */
    public function getCmdValue()
    {
        $cmd = CmdManager::byId(str_replace('#', '', $this->getValue()));
        if (is_object($cmd)) {
            return $cmd;
        }
        return false;
    }

    private function addDataForInfoCmdRender(&$htmlData, $version, $version2, $templateCode)
    {
        $htmlData['#state#'] = '';
        $htmlData['#tendance#'] = '';
        if (!$this->getEqLogicId()->isEnabled()) {
            $templateCode = FileSystemHelper::getCoreTemplateFileContent($version, 'cmd.error', '');
            $htmlData['#state#'] = 'N/A';
        } else {
            $htmlData['#state#'] = $this->execCmd();
            if (strpos($htmlData['#state#'], 'error::') !== false) {
                $templateCode = FileSystemHelper::getCoreTemplateFileContent($version, 'cmd.error', '');
                $htmlData['#state#'] = str_replace('error::', '', $htmlData['#state#']);
            } else {
                if ($this->isSubType(CmdSubType::BINARY) && $this->getDisplay('invertBinary') == 1) {
                    $htmlData['#state#'] = ($htmlData['#state#'] == 1) ? 0 : 1;
                }
                if ($this->isSubType(CmdSubType::NUMERIC) && trim($htmlData['#state#']) == '') {
                    $htmlData['#state#'] = 0;
                }
            }
            if (method_exists($this, 'formatValueWidget')) {
                $htmlData['#state#'] = $this->formatValueWidget($htmlData['#state#']);
            }
        }

        $htmlData['#state#'] = str_replace(["\'", "'"], ["'", "\'"], $htmlData['#state#']);
        $htmlData['#collectDate#'] = $this->getCollectDate();
        $htmlData['#valueDate#'] = $this->getValueDate();
        $htmlData['#alertLevel#'] = $this->getCache('alertLevel', 'none');
        if ($this->getIsHistorized() == 1) {
            $htmlData['#history#'] = 'history cursor';
            if (ConfigManager::byKey('displayStatsWidget') == 1
                && strpos($templateCode, '#displayHistory#') !== false
                && $this->getDisplay('showStatsOn' . $version2, 1) == 1) {
                $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . ConfigManager::byKey('historyCalculPeriod') . ' hour'));
                $htmlData['#displayHistory#'] = '';
                $historyStatistique = $this->getStatistique($startHist, date(DateFormat::FULL));
                if ($historyStatistique['avg'] == 0 && $historyStatistique['min'] == 0 && $historyStatistique['max'] == 0) {
                    $htmlData['#averageHistoryValue#'] = round($htmlData['#state#'], 1);
                    $htmlData['#minHistoryValue#'] = round($htmlData['#state#'], 1);
                    $htmlData['#maxHistoryValue#'] = round($htmlData['#state#'], 1);
                } else {
                    $htmlData['#averageHistoryValue#'] = round($historyStatistique['avg'], 1);
                    $htmlData['#minHistoryValue#'] = round($historyStatistique['min'], 1);
                    $htmlData['#maxHistoryValue#'] = round($historyStatistique['max'], 1);
                }
                $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . ConfigManager::byKey('historyCalculTendance') . ' hour'));
                $tendance = $this->getTendance($startHist, date(DateFormat::FULL));
                if ($tendance > ConfigManager::byKey('historyCalculTendanceThresholddMax')) {
                    $htmlData['#tendance#'] = 'fa fa-arrow-up';
                } else if ($tendance < ConfigManager::byKey('historyCalculTendanceThresholddMin')) {
                    $htmlData['#tendance#'] = 'fa fa-arrow-down';
                } else {
                    $htmlData['#tendance#'] = 'fa fa-minus';
                }
            }
        }
        $parameters = $this->getDisplay('parameters');
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $htmlData['#' . $key . '#'] = $value;
            }
        }
    }

    /**
     * @param $_startTime
     * @param $_endTime
     * @return array
     * @throws CoreException
     */
    public function getStatistique($_startTime, $_endTime)
    {
        if (!$this->isType(CmdType::INFO) || $this->isType(CmdType::STRING)) {
            return [];
        }
        return HistoryManager::getStatistics($this->getId(), $_startTime, $_endTime);
    }

    /**
     * @param $_startTime
     * @param $_endTime
     * @return float|int
     * @throws \Exception
     */
    public function getTendance($_startTime, $_endTime)
    {
        return HistoryManager::getTendance($this->getId(), $_startTime, $_endTime);
    }

    private function addDataForOthersCmdRender(&$htmlData, $htmlRender, $_options, $templateCode)
    {
        $cmdValue = $this->getCmdValue();
        if (is_object($cmdValue) && $cmdValue->isType(CmdType::INFO)) {
            $htmlData['#state#'] = $cmdValue->execCmd();
            $htmlData['#valueName#'] = $cmdValue->getName();
            $htmlData['#unite#'] = $cmdValue->getUnite();
            $htmlData['#valueDate#'] = $cmdValue->getValueDate();
            $htmlData['#collectDate#'] = $cmdValue->getCollectDate();
            $htmlData['#alertLevel#'] = $cmdValue->getCache('alertLevel', 'none');
            if (trim($htmlData['#state#']) == '' && ($cmdValue->getSubType() == CmdSubType::BINARY || $cmdValue->getSubType() == CmdSubType::NUMERIC)) {
                $htmlData['#state#'] = 0;
            }
            if ($cmdValue->getSubType() == CmdSubType::BINARY && $cmdValue->getDisplay('invertBinary') == 1) {
                $htmlData['#state#'] = ($htmlData['#state#'] == 1) ? 0 : 1;
            }
        } else {
            $htmlData['#state#'] = ($this->getLastValue() !== null) ? $this->getLastValue() : '';
            $htmlData['#valueName#'] = $this->getName();
            $htmlData['#unite#'] = $this->getUnite();
        }
        $htmlData['#state#'] = str_replace(["\'", "'"], ["'", "\'"], $htmlData['#state#']);
        $parameters = $this->getDisplay('parameters');
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $htmlData['#' . $key . '#'] = $value;
            }
        }

        $htmlRender .= Utils::templateReplace($htmlData, $templateCode);
        if (trim($htmlRender) == '') {
            return $htmlRender;
        }
        if ($_options != '') {
            $options = NextDomHelper::toHumanReadable($_options);
            $options = Utils::isJson($options, $options);
            if (is_array($options)) {
                foreach ($options as $key => $value) {
                    $htmlData['#' . $key . '#'] = $value;
                }
            }
        }
        if (!isset($htmlData['#title#'])) {
            $htmlData['#title#'] = '';
        }
        if (!isset($htmlData['#message#'])) {
            $htmlData['#message#'] = '';
        }
        if (!isset($htmlData['#slider#'])) {
            $htmlData['#slider#'] = '';
        }
        if (!isset($htmlData['#color#'])) {
            $htmlData['#color#'] = '';
        }
        $htmlData['#title_placeholder#'] = $this->getDisplay('title_placeholder', __('Titre'));
        $htmlData['#message_placeholder#'] = $this->getDisplay('message_placeholder', __('Message'));
        $htmlData['#message_cmd_type#'] = $this->getDisplay('message_cmd_type', 'info');
        $htmlData['#message_cmd_subtype#'] = $this->getDisplay('message_cmd_subtype', '');
        $htmlData['#message_disable#'] = $this->getDisplay('message_disable', 0);
        $htmlData['#title_disable#'] = $this->getDisplay('title_disable', 0);
        $htmlData['#title_color#'] = $this->getDisplay('title_color', 0);
        $htmlData['#title_possibility_list#'] = str_replace("'", "\'", $this->getDisplay('title_possibility_list', ''));
        $htmlData['#slider_placeholder#'] = $this->getDisplay('slider_placeholder', __('Valeur'));
        $htmlData['#other_tooltips#'] = ($htmlData['#name#'] != $this->getName()) ? $this->getName() : '';
        return $htmlRender;
    }

    /**
     * @return array|bool|mixed|null|string
     */
    public function getLastValue()
    {
        return $this->getConfiguration(CmdConfigKey::LAST_CMD_VALUE, null);
    }

    /**
     * @param $_response
     * @param string $_plugin
     * @param string $_network
     * @return string
     * @throws \Exception
     */
    public function generateAskResponseLink($_response, $_plugin = 'core', $_network = 'external')
    {
        $token = $this->getCache('ask::token', ConfigManager::genKey());
        $this->setCache(['ask::count' => 0, 'ask::token' => $token]);
        $result = NetworkHelper::getNetworkAccess($_network) . '/core/api/jeeApi.php?';
        $result .= 'type=ask';
        $result .= '&plugin=' . $_plugin;
        $result .= '&apikey=' . Api::getApiKey($_plugin);
        $result .= '&token=' . $token;
        $result .= '&response=' . urlencode($_response);
        return $result . '&cmd_id=' . $this->getId();
    }

    /**
     * @param $_response
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function askResponse($_response)
    {
        if ($this->getCache('ask::variable', 'none') == 'none') {
            return false;
        }
        $askEndTime = $this->getCache('ask::endtime', null);
        if ($askEndTime == null || $askEndTime < strtotime('now')) {
            return false;
        }
        $dataStore = new DataStore();
        $dataStore->setType(NextDomObj::SCENARIO);
        $dataStore->setKey($this->getCache('ask::variable', 'none'));
        $dataStore->setValue($_response);
        $dataStore->setLink_id(-1);
        $dataStore->save();
        $this->setCache(['ask::variable' => 'none', 'ask::count' => 0, 'ask::token' => null, 'ask::endtime' => null]);
        return true;
    }

    /**
     * @param $_startTime
     * @param $_endTime
     * @return array|float|int|null
     * @throws \Exception
     */
    public function getTemporalAvg($_startTime, $_endTime)
    {
        if (!$this->isType(CmdType::INFO) || $this->isType(CmdType::STRING)) {
            return [];
        }
        return HistoryManager::getTemporalAvg($this->getId(), $_startTime, $_endTime);
    }

    /**
     * @param null $_dateStart
     * @param null $_dateEnd
     * @return History[]
     * @throws \Exception
     */
    public function getHistory($_dateStart = null, $_dateEnd = null)
    {
        return HistoryManager::all($this->id, $_dateStart, $_dateEnd);
    }

    /**
     * @param null $_dateStart
     * @param null $_dateEnd
     * @param string $_period
     * @param int $_offset
     * @return array|mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getPluralityHistory($_dateStart = null, $_dateEnd = null, $_period = 'day', $_offset = 0)
    {
        return HistoryManager::getPlurality($this->id, $_dateStart, $_dateEnd, $_period, $_offset);
    }

    /**
     * @param string $_key
     * @param bool $_default
     * @return array|bool|mixed
     * @throws \ReflectionException
     */
    public function widgetPossibility($_key = '', $_default = true)
    {
        $reflectedClass = new \ReflectionClass($this->getEqType());
        $method_toHtml = $reflectedClass->getMethod('toHtml');
        $result = [];
        $result[Common::CUSTOM] = $method_toHtml->class == EqLogic::class;
        $reflectedClass = new \ReflectionClass($this->getEqType() . 'Cmd');
        $method_toHtml = $reflectedClass->getMethod('toHtml');
        $result[Common::CUSTOM] = $method_toHtml->class == Cmd::class;
        $reflectedClass = $this->getEqType() . 'Cmd';
        if (property_exists($reflectedClass, '_widgetPossibility')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $result = $reflectedClass::$_widgetPossibility;
            if ($_key != '') {
                $keys = explode('::', $_key);
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
                if (is_array($result)) {
                    return $_default;
                }
                return $result;
            }
        }

        if ($_key != '') {
            if (isset($result[Common::CUSTOM]) && !isset($result[$_key])) {
                return $result[Common::CUSTOM];
            }
            return (isset($result[$_key])) ? $result[$_key] : $_default;
        }
        return $result;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function export()
    {
        $cmd = clone $this;
        $cmd->setId('');
        $cmd->setOrder('');
        $cmd->setEqLogic_id('');
        $cmd->setDisplay('graphType', '');
        $cmdValue = $cmd->getCmdValue();
        if (is_object($cmdValue)) {
            $cmd->setValue($cmdValue->getName());
        } else {
            $cmd->setValue('');
        }
        $result = Utils::o2a($cmd);
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
        if (isset($result[Common::CONFIGURATION]) && count($result[Common::CONFIGURATION]) == 0) {
            unset($result[Common::CONFIGURATION]);
        }
        if (isset($result[Common::DISPLAY]) && count($result[Common::DISPLAY]) == 0) {
            unset($result[Common::DISPLAY]);
        }
        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDirectUrlAccess()
    {
        $url = '/core/api/jeeApi.php?apikey=' . ConfigManager::byKey('api') . '&type=cmd&id=' . $this->getId();
        if ($this->isType(CmdType::ACTION)) {
            switch ($this->getSubType()) {
                case CmdSubType::SLIDER:
                    $url .= '&slider=50';
                    break;
                case CmdSubType::COLOR:
                    $url .= '&color=#123456';
                    break;
                case CmdSubType::MESSAGE:
                    $url .= '&title=montitre&message=monmessage';
                    break;
                case CmdSubType::SELECT:
                    $url .= '&select=value';
                    break;
            }
        }
        return NetworkHelper::getNetworkAccess('external') . $url;
    }

    /**
     * @param $accessCode
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function checkAccessCode($accessCode)
    {
        if (!$this->isType(CmdType::ACTION) || trim($this->getConfiguration(CmdConfigKey::ACTION_CODE_ACCESS)) == '') {
            return true;
        }
        if (sha1($accessCode) == $this->getConfiguration(CmdConfigKey::ACTION_CODE_ACCESS)) {
            $this->setConfiguration(CmdConfigKey::ACTION_CODE_ACCESS, Utils::sha512($accessCode));
            $this->save();
            return true;
        }
        if (Utils::sha512($accessCode) == $this->getConfiguration(CmdConfigKey::ACTION_CODE_ACCESS)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function exportApi()
    {
        $result = Utils::o2a($this);
        $result['currentValue'] = (!$this->isType(CmdType::ACTION)) ? $this->execCmd(null, 2) : $this->getConfiguration(CmdConfigKey::LAST_CMD_VALUE, null);
        return $result;
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param null $_drill
     * @return array|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = null)
    {
        if ($_drill == null) {
            $_drill = ConfigManager::byKey('graphlink::cmd::drill');
        }
        if (isset($_data['node']['cmd' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = ($this->isType(CmdType::INFO)) ? Utils::findCodeIcon('fa-eye') : Utils::findCodeIcon('fa-hand-paper-o');
        $_data['node']['cmd' . $this->getId()] = [
            'id' => 'cmd' . $this->getId(),
            'name' => $this->getName(),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'texty' => -14,
            'textx' => 0,
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'title' => $this->getHumanName(),
            'url' => $this->getEqLogicId()->getLinkToConfiguration(),
        ];
        $usedBy = $this->getUsedBy();
        $use = $this->getUse();
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::CMD], NextDomObj::CMD, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::INTERACT_DEF], NextDomObj::INTERACT_DEF, $_data, $_level, $_drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::PLAN], NextDomObj::PLAN, $_data, $_level, $_drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::CMD, $usedBy[NextDomObj::VIEW], NextDomObj::VIEW, $_data, $_level, $_drill, [Common::DASH_VALUE => '2,6', Common::LENGTH_FACTOR => 0.6]);
        Utils::addGraphLink($this, NextDomObj::CMD, $use[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $use[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $use[NextDomObj::CMD], NextDomObj::CMD, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $use[NextDomObj::DATASTORE], NextDomObj::DATASTORE, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::CMD, $this->getEqLogicId(), NextDomObj::EQLOGIC, $_data, $_level, $_drill, [Common::DASH_VALUE => '1,0', Common::LENGTH_FACTOR => 0.6]);
        return $_data;
    }

    /**
     * @param bool $resultHasArray
     * @return array
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getUsedBy($resultHasArray = false)
    {
        $result = [
            NextDomObj::CMD => [],
            NextDomObj::EQLOGIC => [],
            NextDomObj::SCENARIO => [],
            NextDomObj::PLAN => [],
            NextDomObj::VIEW => []
        ];
        $result[NextDomObj::CMD] = CmdManager::searchConfiguration('#' . $this->getId() . '#');
        $result[NextDomObj::EQLOGIC] = EqLogicManager::searchConfiguration('#' . $this->getId() . '#');
        $result[NextDomObj::SCENARIO] = ScenarioManager::searchByUse([[Common::ACTION => '#' . $this->getId() . '#']]);
        $result[NextDomObj::INTERACT_DEF] = InteractDefManager::searchByUse('#' . $this->getId() . '#');
        $result[NextDomObj::VIEW] = ViewManager::searchByUse(NextDomObj::CMD, $this->getId());
        $result[NextDomObj::PLAN] = PlanHeaderManager::searchByUse(NextDomObj::CMD, $this->getId());
        if ($resultHasArray) {
            foreach ($result as &$usage) {
                $usage = Utils::o2a($usage);
            }
        }
        return $result;
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
     * Check if user have right to execute the action.
     *
     * @param User $user User to test
     *
     * @return bool True if user can execute
     *
     * @throws \Exception
     */
    public function hasRight($user = null)
    {
        if ($this->isType(CmdType::ACTION)) {
            return $this->getEqLogicId()->hasRight(ActionRight::EXECUTE, $user);
        } else {
            return $this->getEqLogicId()->hasRight(ActionRight::READ, $user);
        }
    }

    /**
     * Get changed state
     *
     * @return bool True if attribute has changed
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Set changed state
     *
     * @param bool $changed New changed state
     *
     * @return $this
     */
    public function setChanged($changed)
    {
        $this->_changed = $changed;
        return $this;
    }

    /**
     * Cast this object from a source command
     *
     * @param Cmd $srcCmd
     *
     * @return $this
     */
    public function castFromCmd(Cmd $srcCmd)
    {
        $attributes = $srcCmd->getAllAttributes();
        foreach ($attributes as $name => $value) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * Get all attributes of the Cmd class
     *
     * @return array List of attributes
     */
    public function getAllAttributes()
    {
        return [
            '_collectDate' => $this->_collectDate,
            '_valueDate' => $this->_valueDate,
            '_eqLogic' => $this->_eqLogic,
            '_needRefreshWidget' => $this->_needRefreshWidget,
            '_needRefreshAlert' => $this->_needRefreshAlert,
            '_changed' => $this->_changed,
            'eqType' => $this->eqType,
            'logicalId' => $this->logicalId,
            'generic_type' => $this->generic_type,
            'order' => $this->order,
            'name' => $this->name,
            'configuration' => $this->configuration,
            'template' => $this->template,
            'isHistorized' => $this->isHistorized,
            'type' => $this->type,
            'subType' => $this->subType,
            'unite' => $this->unite,
            'display' => $this->display,
            'isVisible' => $this->isVisible,
            'value' => $this->value,
            'html' => $this->html,
            'alert' => $this->alert,
            'id' => $this->id,
            'eqLogic_id' => $this->eqLogic_id
        ];
    }
}
