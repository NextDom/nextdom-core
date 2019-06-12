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
use NextDom\Managers\ListenerManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\ObjectManager;
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
    private static $_templateArray = array();
    /**
     * TODO: Mis en public pour y accéder depuis CmdManager
     */
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
     */
    public static function duringAlertLevel($_options)
    {
        $cmd = CmdManager::byId($_options['cmd_id']);
        if (!is_object($cmd)) {
            return;
        }
        if ($cmd->getType() != 'info') {
            return;
        }
        $value = $cmd->execCmd();
        $level = $cmd->checkAlertLevel($value, false);
        if ($level != 'none') {
            $cmd->actionAlertLevel($level, $value);
        }
    }

    /**
     * @return string
     */
    public function getEqType_name()
    {
        trigger_error('This method is deprecated. Use getEqType', E_USER_DEPRECATED);
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
        if (in_array($_key, array('dashboard', 'dview', 'mview', 'dplan')) && $this->getWidgetTemplateCode($_key, true) == $_value) {
            $_value = '';
        }
        if ($this->getHtml($_key) != $_value) {
            $this->_needRefreshWidget = true;
        }
        $this->html = Utils::setJsonAttr($this->html, $_key, $_value);
        return $this;
    }

    /**
     * TODO: Déplacer dans CmdManager ???
     *
     * @param string $_version
     * @param bool $_noCustom
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getWidgetTemplateCode($_version = 'dashboard', $_noCustom = false)
    {
        $version = NextDomHelper::versionAlias($_version);

        $template_name = 'cmd.' . $this->getType() . '.' . $this->getSubType() . '.' . $this->getTemplate($version, 'default');
        if (!isset(self::$_templateArray[$version . '::' . $template_name])) {
            $template = FileSystemHelper::getTemplateFileContent('core', $version, $template_name);
            if ($template == '') {
                if (ConfigManager::byKey('active', 'widget') == 1) {
                    $template = FileSystemHelper::getTemplateFileContent('core', $version, $template_name, 'widget');
                }
                if ($template == '') {
                    foreach (PluginManager::listPlugin(true) as $plugin) {
                        $template = FileSystemHelper::getTemplateFileContent('core', $version, $template_name, $plugin->getId());
                        if ($template != '') {
                            break;
                        }
                    }
                }
                if ($template == '') {
                    $template_name = 'cmd.' . $this->getType() . '.' . $this->getSubType() . '.default';
                    $template = FileSystemHelper::getTemplateFileContent('core', $version, $template_name);
                }
            }
            self::$_templateArray[$version . '::' . $template_name] = $template;
        } else {
            $template = self::$_templateArray[$version . '::' . $template_name];
        }
        return $template;
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
        CacheManager::delete('cmdCacheAttr' . $this->getId());
        CacheManager::delete('cmd' . $this->getId());
        NextDomHelper::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getHumanName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'cmd'));
        return DBHelper::remove($this);
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
     * @param string $_date
     * @return array|mixed|null
     * @throws CoreException
     */
    public function emptyHistory($_date = '')
    {
        return HistoryManager::emptyHistory($this->getId(), $_date);
    }

    /**
     * @param bool $_tag
     * @param bool $_prettify
     * @return string
     * @throws \Exception
     */
    public function getHumanName($_tag = false, $_prettify = false)
    {
        $name = '';
        $eqLogic = $this->getEqLogicId();
        if (is_object($eqLogic)) {
            $name .= $eqLogic->getHumanName($_tag, $_prettify);
        }
        if ($_tag) {
            $name .= ' - ' . $this->getName();
        } else {
            $name .= '[' . $this->getName() . ']';
        }
        return $name;
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
     * @param string $_version
     * @param string $_options
     * @param null $_cmdColor
     * @return mixed|string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function toHtml($_version = 'dashboard', $_options = '', $_cmdColor = null)
    {
        $version2 = NextDomHelper::versionAlias($_version, false);
        if ($this->getDisplay('showOn' . $version2, 1) == 0) {
            return '';
        }
        $version = NextDomHelper::versionAlias($_version);
        $html = '';
        $replace = array(
            '#id#' => $this->getId(),
            '#name#' => $this->getName(),
            '#name_display#' => ($this->getDisplay('icon') != '') ? $this->getDisplay('icon') : $this->getName(),
            '#history#' => '',
            '#displayHistory#' => 'display : none;',
            '#unite#' => $this->getUnite(),
            '#minValue#' => $this->getConfiguration('minValue', 0),
            '#maxValue#' => $this->getConfiguration('maxValue', 100),
            '#logicalId#' => $this->getLogicalId(),
            '#uid#' => 'cmd' . $this->getId() . EqLogic::UIDDELIMITER . mt_rand() . EqLogic::UIDDELIMITER,
            '#version#' => $_version,
            '#eqLogic_id#' => $this->getEqLogic_id(),
            '#generic_type#' => $this->getGeneric_type(),
            '#hideCmdName#' => '',
        );
        if ($this->getConfiguration('listValue', '') != '') {
            $listOption = '';
            $elements = explode(';', $this->getConfiguration('listValue', ''));
            $foundSelect = false;
            foreach ($elements as $element) {
                $coupleArray = explode('|', $element);
                $cmdValue = $this->getCmdValue();
                if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
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
            $replace['#listValue#'] = $listOption;
        }
        if ($this->getDisplay('showNameOn' . $version2, 1) == 0) {
            $replace['#hideCmdName#'] = 'display:none;';
        }
        if ($this->getDisplay('showIconAndName' . $version2, 0) == 1) {
            $replace['#name_display#'] = $this->getDisplay('icon') . ' ' . $this->getName();
        }
        $template = $this->getWidgetTemplateCode($_version);

        if ($_cmdColor == null && $version != 'scenario') {
            $eqLogic = $this->getEqLogicId();
            if ($eqLogic->getPrimaryCategory() == '') {
                $replace['#cmdColor#'] = NextDomHelper::getConfiguration('eqLogic:category:default:cmdColor');
            } else {
                $replace['#cmdColor#'] = NextDomHelper::getConfiguration('eqLogic:category:' . $eqLogic->getPrimaryCategory() . ':cmdColor');
            }
        } else {
            $replace['#cmdColor#'] = $_cmdColor;
        }

        if ($this->getType() == 'info') {
            $replace['#state#'] = '';
            $replace['#tendance#'] = '';
            if ($this->getEqLogicId()->getIsEnable() == 0) {
                $template = FileSystemHelper::getTemplateFileContent('core', $version, 'cmd.error');
                $replace['#state#'] = 'N/A';
            } else {
                $replace['#state#'] = $this->execCmd();
                if (strpos($replace['#state#'], 'error::') !== false) {
                    $template = FileSystemHelper::getTemplateFileContent('core', $version, 'cmd.error');
                    $replace['#state#'] = str_replace('error::', '', $replace['#state#']);
                } else {
                    if ($this->getSubType() == 'binary' && $this->getDisplay('invertBinary') == 1) {
                        $replace['#state#'] = ($replace['#state#'] == 1) ? 0 : 1;
                    }
                    if ($this->getSubType() == 'numeric' && trim($replace['#state#']) == '') {
                        $replace['#state#'] = 0;
                    }
                }
                if (method_exists($this, 'formatValueWidget')) {
                    $replace['#state#'] = $this->formatValueWidget($replace['#state#']);
                }
            }

            $replace['#state#'] = str_replace(array("\'", "'"), array("'", "\'"), $replace['#state#']);
            $replace['#collectDate#'] = $this->getCollectDate();
            $replace['#valueDate#'] = $this->getValueDate();
            $replace['#alertLevel#'] = $this->getCache('alertLevel', 'none');
            if ($this->getIsHistorized() == 1) {
                $replace['#history#'] = 'history cursor';
                if (ConfigManager::byKey('displayStatsWidget') == 1 && strpos($template, '#displayHistory#') !== false) {
                    if ($this->getDisplay('showStatsOn' . $version2, 1) == 1) {
                        $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . ConfigManager::byKey('historyCalculPeriod') . ' hour'));
                        $replace['#displayHistory#'] = '';
                        $historyStatistique = $this->getStatistique($startHist, date('Y-m-d H:i:s'));
                        if ($historyStatistique['avg'] == 0 && $historyStatistique['min'] == 0 && $historyStatistique['max'] == 0) {
                            $replace['#averageHistoryValue#'] = round($replace['#state#'], 1);
                            $replace['#minHistoryValue#'] = round($replace['#state#'], 1);
                            $replace['#maxHistoryValue#'] = round($replace['#state#'], 1);
                        } else {
                            $replace['#averageHistoryValue#'] = round($historyStatistique['avg'], 1);
                            $replace['#minHistoryValue#'] = round($historyStatistique['min'], 1);
                            $replace['#maxHistoryValue#'] = round($historyStatistique['max'], 1);
                        }
                        $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . ConfigManager::byKey('historyCalculTendance') . ' hour'));
                        $tendance = $this->getTendance($startHist, date('Y-m-d H:i:s'));
                        if ($tendance > ConfigManager::byKey('historyCalculTendanceThresholddMax')) {
                            $replace['#tendance#'] = 'fa fa-arrow-up';
                        } else if ($tendance < ConfigManager::byKey('historyCalculTendanceThresholddMin')) {
                            $replace['#tendance#'] = 'fa fa-arrow-down';
                        } else {
                            $replace['#tendance#'] = 'fa fa-minus';
                        }
                    }
                }
            }
            $parameters = $this->getDisplay('parameters');
            if (is_array($parameters)) {
                foreach ($parameters as $key => $value) {
                    $replace['#' . $key . '#'] = $value;
                }
            }
            return Utils::templateReplace($replace, $template);
        } else {
            $cmdValue = $this->getCmdValue();
            if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
                $replace['#state#'] = $cmdValue->execCmd();
                $replace['#valueName#'] = $cmdValue->getName();
                $replace['#unite#'] = $cmdValue->getUnite();
                $replace['#valueDate#'] = $cmdValue->getValueDate();
                $replace['#collectDate#'] = $cmdValue->getCollectDate();
                $replace['#alertLevel#'] = $cmdValue->getCache('alertLevel', 'none');
                if (trim($replace['#state#']) == '' && ($cmdValue->getSubType() == 'binary' || $cmdValue->getSubType() == 'numeric')) {
                    $replace['#state#'] = 0;
                }
                if ($cmdValue->getSubType() == 'binary' && $cmdValue->getDisplay('invertBinary') == 1) {
                    $replace['#state#'] = ($replace['#state#'] == 1) ? 0 : 1;
                }
            } else {
                $replace['#state#'] = ($this->getLastValue() !== null) ? $this->getLastValue() : '';
                $replace['#valueName#'] = $this->getName();
                $replace['#unite#'] = $this->getUnite();
            }
            $replace['#state#'] = str_replace(array("\'", "'"), array("'", "\'"), $replace['#state#']);
            $parameters = $this->getDisplay('parameters');
            if (is_array($parameters)) {
                foreach ($parameters as $key => $value) {
                    $replace['#' . $key . '#'] = $value;
                }
            }

            $html .= Utils::templateReplace($replace, $template);
            if (trim($html) == '') {
                return $html;
            }
            if ($_options != '') {
                $options = NextDomHelper::toHumanReadable($_options);
                $options = Utils::isJson($options, $options);
                if (is_array($options)) {
                    foreach ($options as $key => $value) {
                        $replace['#' . $key . '#'] = $value;
                    }
                }
            }
            if (!isset($replace['#title#'])) {
                $replace['#title#'] = '';
            }
            if (!isset($replace['#message#'])) {
                $replace['#message#'] = '';
            }
            if (!isset($replace['#slider#'])) {
                $replace['#slider#'] = '';
            }
            if (!isset($replace['#color#'])) {
                $replace['#color#'] = '';
            }
            $replace['#title_placeholder#'] = $this->getDisplay('title_placeholder', __('Titre'));
            $replace['#message_placeholder#'] = $this->getDisplay('message_placeholder', __('Message'));
            $replace['#message_cmd_type#'] = $this->getDisplay('message_cmd_type', 'info');
            $replace['#message_cmd_subtype#'] = $this->getDisplay('message_cmd_subtype', '');
            $replace['#message_disable#'] = $this->getDisplay('message_disable', 0);
            $replace['#title_disable#'] = $this->getDisplay('title_disable', 0);
            $replace['#title_color#'] = $this->getDisplay('title_color', 0);
            $replace['#title_possibility_list#'] = str_replace("'", "\'", $this->getDisplay('title_possibility_list', ''));
            $replace['#slider_placeholder#'] = $this->getDisplay('slider_placeholder', __('Valeur'));
            $replace['#other_tooltips#'] = ($replace['#name#'] != $this->getName()) ? $this->getName() : '';
            $html = Utils::templateReplace($replace, $html);
            return $html;
        }
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
     * @param $_eqLogic
     * @return $this
     */
    public function setEqLogicId($_eqLogic)
    {
        $this->_eqLogic = $_eqLogic;
        return $this;
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
        if ($this->getType() == 'info') {
            $state = $this->getCache(array('collectDate', 'valueDate', 'value'));
            if (isset($state['collectDate'])) {
                $this->setCollectDate($state['collectDate']);
            } else {
                $this->setCollectDate(date('Y-m-d H:i:s'));
            }
            if (isset($state['valueDate'])) {
                $this->setValueDate($state['valueDate']);
            } else {
                $this->setValueDate($this->getCollectDate());
            }
            return $state['value'];

        }
        $eqLogic = $this->getEqLogicId();
        if ($this->getType() != 'info' && (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1)) {
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
            if ($this->getSubType() == 'color' && isset($options['color']) && substr($options['color'], 0, 1) != '#') {
                $options['color'] = CmdManager::convertColor($options['color']);
            }
            $str_option = '';
            if (is_array($options) && ((count($options) > 1 && isset($options['uid'])) || count($options) > 0)) {
                LogHelper::add('event', 'info', __('Exécution de la commande ') . $this->getHumanName() . __(' avec les paramètres ') . json_encode($options, true));
            } else {
                LogHelper::add('event', 'info', __('Exécution de la commande ') . $this->getHumanName());
            }

            if ($this->getConfiguration('timeline::enable')) {
                TimeLineHelper::addTimelineEvent(array('type' => 'cmd', 'subtype' => 'action', 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => date('Y-m-d H:i:s'), 'options' => $str_option));
            }
            $this->preExecCmd($options);
            $value = $this->formatValue($this->execute($options), $_quote);
            $this->postExecCmd($options);
        } catch (\Exception $e) {
            $type = $eqLogic->getEqType_name();
            if ($eqLogic->getConfiguration('nerverFail') != 1) {
                $numberTryWithoutSuccess = $eqLogic->getStatus('numberTryWithoutSuccess', 0);
                $eqLogic->setStatus('numberTryWithoutSuccess', $numberTryWithoutSuccess);
                if ($numberTryWithoutSuccess >= ConfigManager::byKey('numberOfTryBeforeEqLogicDisable')) {
                    $message = 'Désactivation de <a href="' . $eqLogic->getLinkToConfiguration() . '">' . $eqLogic->getName();
                    $message .= '</a> ' . __('car il n\'a pas répondu ou mal répondu lors des 3 derniers essais');
                    MessageManager::add($type, $message);
                    $eqLogic->setIsEnable(0);
                    $eqLogic->save();
                }
            }
            LogHelper::add($type, 'error', __('Erreur exécution de la commande ') . $this->getHumanName() . ' : ' . $e->getMessage());
            throw $e;
        }
        if ($options !== null && $this->getValue() == '') {
            if (isset($options['slider'])) {
                $this->setConfiguration('lastCmdValue', $options['slider']);
                $this->save();
            }
            if (isset($options['color'])) {
                $this->setConfiguration('lastCmdValue', $options['color']);
                $this->save();
            }
        }
        if ($this->getConfiguration('updateCmdId') != '') {
            $cmd = CmdManager::byId($this->getConfiguration('updateCmdId'));
            if (is_object($cmd)) {
                $value = $this->getConfiguration('updateCmdToValue');
                switch ($this->getSubType()) {
                    case 'slider':
                        $value = str_replace('#slider#', $options['slider'], $value);
                        break;
                    case 'color':
                        $value = str_replace('#color#', $options['color'], $value);
                        break;
                }
                $cmd->event($value);
            }
        }
        return $value;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getCache($_key = '', $_default = '')
    {
        $cache = CacheManager::byKey('cmdCacheAttr' . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $_key, $_default);
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
     * @param array $_values
     * @throws \Exception
     */
    public function preExecCmd($_values = array())
    {
        if (!is_array($this->getConfiguration('nextdomPreExecCmd')) || count($this->getConfiguration('nextdomPreExecCmd')) == 0) {
            return;
        }
        foreach ($this->getConfiguration('nextdomPreExecCmd') as $action) {
            try {
                $options = array();
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
                LogHelper::addError('cmd', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Sur preExec de la commande') . $this->getHumanName() . __('. Détails : ') . $e->getMessage());
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
        if ($this->getType() == 'info') {
            switch ($this->getSubType()) {
                case 'string':
                    if ($_quote) {
                        return '"' . $_value . '"';
                    }
                    return $_value;
                case 'other':
                    if ($_quote) {
                        return '"' . $_value . '"';
                    }
                    return $_value;
                case 'binary':
                    if ($this->getConfiguration('calculValueOffset') != '') {
                        try {
                            if (preg_match("/[a-zA-Z#]/", $_value)) {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', '"' . $_value . '"', str_replace('\'#value#\'', '#value#', str_replace('"#value#"', '#value#', $this->getConfiguration('calculValueOffset')))));
                            } else {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $this->getConfiguration('calculValueOffset')));
                            }
                        } catch (\Exception $ex) {

                        }
                    }
                    $value = strtolower($_value);
                    if ($value == 'on' || $value == 'high' || $value == 'true' || $value == true) {
                        return 1;
                    }
                    if ($value == 'off' || $value == 'low' || $value == 'false' || $value == false) {
                        return 0;
                    }
                    if ((is_numeric(intval($_value)) && intval($_value) > 1) || $_value == true || $_value == 1) {
                        return 1;
                    }
                    return 0;
                case 'numeric':
                    $_value = floatval(str_replace(',', '.', $_value));
                    if ($this->getConfiguration('calculValueOffset') != '') {
                        try {
                            if (preg_match("/[a-zA-Z#]/", $_value)) {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', '"' . $_value . '"', str_replace('\'#value#\'', '#value#', str_replace('"#value#"', '#value#', $this->getConfiguration('calculValueOffset')))));
                            } else {
                                $_value = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $this->getConfiguration('calculValueOffset')));
                            }
                        } catch (\Exception $ex) {

                        }
                    }
                    if ($this->getConfiguration('historizeRound') !== '' && is_numeric($this->getConfiguration('historizeRound')) && $this->getConfiguration('historizeRound') >= 0) {
                        $_value = round($_value, $this->getConfiguration('historizeRound'));
                    }
                    if ($_value > $this->getConfiguration('maxValue', $_value) && $this->getConfiguration('maxValueReplace') == 1) {
                        $_value = $this->getConfiguration('maxValue', $_value);
                    }
                    if ($_value < $this->getConfiguration('minValue', $_value) && $this->getConfiguration('minValueReplace') == 1) {
                        $_value = $this->getConfiguration('minValue', $_value);
                    }
                    return floatval($_value);
            }
        }
        return $_value;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setConfiguration($_key, $_value)
    {
        if ($_key == 'actionCodeAccess' && $_value != '') {
            if (!Utils::isSha1($_value) && !Utils::isSha512($_value)) {
                $_value = Utils::sha512($_value);
            }
        }
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * TODO ???
     * @param $_options
     * @return bool
     */
    public function execute($_options)
    {
        return false;
    }

    /**
     * @param array $_values
     * @throws \Exception
     */
    public function postExecCmd($_values = array())
    {
        if (!is_array($this->getConfiguration('nextdomPostExecCmd'))) {
            return;
        }
        foreach ($this->getConfiguration('nextdomPostExecCmd') as $action) {
            try {
                $options = array();
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
        if ($this->getConfiguration('maxValue') != '' && $this->getConfiguration('minValue') != '' && $this->getConfiguration('minValue') > $this->getConfiguration('maxValue')) {
            throw new CoreException($this->getHumanName() . ' ' . __('La valeur minimum de la commande ne peut etre supérieure à la valeur maximum'));
        }
        if ($this->getEqType() == '') {
            $this->setEqType($this->getEqLogicId()->getEqType_name());
        }
        if ($this->getDisplay('generic_type') !== '' && $this->getGeneric_type() == '') {
            $this->setGeneric_type($this->getDisplay('generic_type'));
            $this->setDisplay('generic_type', '');
        }
        DBHelper::save($this);
        if ($this->_needRefreshWidget) {
            $this->_needRefreshWidget = false;
            $this->getEqLogicId()->refreshWidget();
        }
        if ($this->_needRefreshAlert && $this->getType() == 'info') {
            $value = $this->execCmd();
            $level = $this->checkAlertLevel($value);
            if ($level != $this->getCache('alertLevel')) {
                $this->actionAlertLevel($level, $value);
            }
        }
        return true;
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
     * @param $_value
     * @param bool $_allowDuring
     * @return int|string
     * @throws CoreException
     */
    public function checkAlertLevel($_value, $_allowDuring = true)
    {
        if ($this->getType() != 'info' || ($this->getAlert('warningif') == '' && $this->getAlert('dangerif') == '')) {
            return 'none';
        }
        global $NEXTDOM_INTERNAL_CONFIG;

        $currentLevel = 'none';
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
            if (!$value['check']) {
                continue;
            }
            if ($this->getAlert($level . 'if') != '') {
                $check = NextDomHelper::evaluateExpression(str_replace('#value#', $_value, $this->getAlert($level . 'if')));
                if ($check == 1 || $check || $check == '1') {
                    $currentLevel = $level;
                }
            }
        }
        if ($this->getCache('alertLevel') == $currentLevel) {
            return $currentLevel;
        }
        if ($_allowDuring && $this->getAlert($currentLevel . 'during') != '' && $this->getAlert($currentLevel . 'during') > 0) {
            $cron = CronManager::byClassAndFunction('cmd', 'duringAlertLevel', array('cmd_id' => intval($this->getId())));
            $next = strtotime('+ ' . $this->getAlert($currentLevel . 'during', 1) . ' minutes ' . date('Y-m-d H:i:s'));
            if (!is_object($cron)) {
                $cron = new cron();
            } else {
                $nextRun = $cron->getNextRunDate();
                if ($nextRun !== false && $next > strtotime($nextRun) && strtotime($nextRun) > strtotime('now')) {
                    return 'none';
                }
            }
            $cron->setClass('cmd');
            $cron->setFunction('duringAlertLevel');
            $cron->setOnce(1);
            $cron->setOption(array('cmd_id' => intval($this->getId())));
            $cron->setSchedule(CronManager::convertDateToCron($next));
            $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->save();
            return 'none';
        }
        if ($_allowDuring && $currentLevel == 'none') {
            $cron = CronManager::byClassAndFunction('cmd', 'duringAlertLevel', array('cmd_id' => intval($this->getId())));
            if (is_object($cron)) {
                $cron->remove(false);
            }
        }
        return $currentLevel;
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
        if ($this->getType() != 'info') {
            return;
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        $this->setCache('alertLevel', $_level);
        $eqLogic = $this->getEqLogicId();
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
            $message .= ' => ' . str_replace('#value#', $_value, $this->getAlert($_level . 'if'));
            LogHelper::add('event', 'info', $message);
            $eqLogic = $this->getEqLogicId();
            if (ConfigManager::byKey('alert::addMessageOn' . ucfirst($_level)) == 1) {
                MessageManager::add($eqLogic->getEqType_name(), $message);
            }
            $cmds = explode(('&&'), ConfigManager::byKey('alert::' . $_level . 'Cmd'));
            if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::' . $_level . 'Cmd')) != '') {
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

        if ($prevAlert != $maxAlert) {
            $status = array(
                'warning' => 0,
                'danger' => 0,
            );
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
        CacheManager::set('cmdCacheAttr' . $this->getId(), Utils::setJsonAttr(CacheManager::byKey('cmdCacheAttr' . $this->getId())->getValue(), $_key, $_value));
        return $this;
    }

    /**
     * @param $_value
     * @param null $_datetime
     * @param int $_loop
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function event($_value, $_datetime = null, $_loop = 1)
    {
        if ($_loop > 4 || $this->getType() != 'info') {
            return;
        }
        $eqLogic = $this->getEqLogicId();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() == 0) {
            return;
        }
        $value = $this->formatValue($_value);
        if ($this->getSubType() == 'numeric' && ($value > $this->getConfiguration('maxValue', $value) || $value < $this->getConfiguration('minValue', $value)) && strpos($value, 'error') == false) {
            LogHelper::add('cmd', 'info', __('La commande n\'est pas dans la plage de valeur autorisée : ') . $this->getHumanName() . ' => ' . $value);
            return;
        }
        if ($this->getConfiguration('denyValues') != '' && in_array($value, explode(';', $this->getConfiguration('denyValues')))) {
            return;
        }
        $oldValue = $this->execCmd();
        $repeat = ($oldValue == $value && $oldValue !== '' && $oldValue !== null);
        $this->setCollectDate(($_datetime !== null) ? $_datetime : date('Y-m-d H:i:s'));
        $this->setCache('collectDate', $this->getCollectDate());
        $this->setValueDate(($repeat) ? $this->getValueDate() : $this->getCollectDate());
        $eqLogic->setStatus(array('lastCommunication' => $this->getCollectDate(), 'timeout' => 0));
        $display_value = $value;
        if (method_exists($this, 'formatValueWidget')) {
            $display_value = $this->formatValueWidget($value);
        } else if ($this->getSubType() == 'binary' && $this->getDisplay('invertBinary') == 1) {
            $display_value = ($value == 1) ? 0 : 1;
        } else if ($this->getSubType() == 'numeric' && trim($value) == '') {
            $display_value = 0;
        } else if ($this->getSubType() == 'binary' && trim($value) == '') {
            $display_value = 0;
        }
        if ($repeat && $this->getConfiguration('repeatEventManagement', 'auto') == 'never') {
            $this->addHistoryValue($value, $this->getCollectDate());
            $eqLogic->emptyCacheWidget();
            EventManager::adds('cmd::update', array(array('cmd_id' => $this->getId(), 'value' => $value, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate())));
            return;
        }
        $_loop++;
        if ($repeat && ($this->getConfiguration('repeatEventManagement', 'auto') == 'always' || $this->getSubType() == 'binary')) {
            $repeat = false;
        }
        $message = __('Evènement sur la commande ') . $this->getHumanName() . __(' valeur : ') . $value;
        if ($repeat) {
            $message .= ' (répétition)';
        }
        LogHelper::add('event', 'info', $message);
        $events = array();
        if (!$repeat) {
            $this->setCache(array('value' => $value, 'valueDate' => $this->getValueDate()));
            ScenarioManager::check($this);
            $eqLogic->emptyCacheWidget();
            $level = $this->checkAlertLevel($value);
            $events[] = array('cmd_id' => $this->getId(), 'value' => $value, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate(), 'alertLevel' => $level);
            $foundInfo = false;
            $value_cmd = CmdManager::byValue($this->getId(), null, true);
            if (is_array($value_cmd) && count($value_cmd) > 0) {
                foreach ($value_cmd as $cmd) {
                    if ($cmd->getType() == 'action') {
                        if (!$repeat) {
                            $events[] = array('cmd_id' => $cmd->getId(), 'value' => $value, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate());
                        }
                    } else {
                        if ($_loop > 1) {
                            $cmd->event($cmd->execute(), null, $_loop);
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
            $events[] = array('cmd_id' => $this->getId(), 'value' => $value, 'display_value' => $display_value, 'valueDate' => $this->getValueDate(), 'collectDate' => $this->getCollectDate());
        }
        if (count($events) > 0) {
            EventManager::adds('cmd::update', $events);
        }
        if (!$repeat) {
            ListenerManager::check($this->getId(), $value, $this->getCollectDate());
            ObjectManager::checkSummaryUpdate($this->getId());
        }
        $this->addHistoryValue($value, $this->getCollectDate());
        $this->checkReturnState($value);
        if (!$repeat) {
            $this->checkCmdAlert($value);
            if (isset($level) && $level != $this->getCache('alertLevel')) {
                $this->actionAlertLevel($level, $value);
            }
            if ($this->getConfiguration('timeline::enable')) {
                TimeLineHelper::addTimelineEvent(array('type' => 'cmd', 'subtype' => 'info', 'cmdType' => $this->getSubType(), 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => $this->getValueDate(), 'value' => $value . $this->getUnite()));
            }
            $this->pushUrl($value);
        }
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
     * @return null
     * @throws CoreException
     */
    public function addHistoryValue($_value, $_datetime = '')
    {
        if ($this->getIsHistorized() == 1 && ($_value == null || ($_value !== '' && $this->getType() == 'info' && $_value <= $this->getConfiguration('maxValue', $_value) && $_value >= $this->getConfiguration('minValue', $_value)))) {
            $history = new History();
            $history->setCmd_id($this->getId());
            $history->setValue($_value);
            $history->setDatetime($_datetime);
            $history->save($this);
            return null;
        }
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
     * @throws \Exception
     */
    public function checkReturnState($_value)
    {
        if (is_numeric($this->getConfiguration('returnStateTime')) && $this->getConfiguration('returnStateTime') > 0 && $_value != $this->getConfiguration('returnStateValue') && trim($this->getConfiguration('returnStateValue')) != '') {
            $cron = CronManager::byClassAndFunction('cmd', 'returnState', array('cmd_id' => intval($this->getId())));
            if (!is_object($cron)) {
                $cron = new cron();
            }
            $cron->setClass('cmd');
            $cron->setFunction('returnState');
            $cron->setOnce(1);
            $cron->setOption(array('cmd_id' => intval($this->getId())));
            $next = strtotime('+ ' . ($this->getConfiguration('returnStateTime') + 1) . ' minutes ' . date('Y-m-d H:i:s'));
            $cron->setSchedule(CronManager::convertDateToCron($next));
            $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->save();
        }
    }

    /**
     * @param $_value
     * @throws CoreException
     */
    public function checkCmdAlert($_value)
    {
        if ($this->getConfiguration('nextdomCheckCmdOperator') == '' || $this->getConfiguration('nextdomCheckCmdTest') == '' || is_nan($this->getConfiguration('nextdomCheckCmdTime', 0))) {
            return;
        }
        $check = NextDomHelper::evaluateExpression($_value . $this->getConfiguration('nextdomCheckCmdOperator') . $this->getConfiguration('nextdomCheckCmdTest'));
        if ($check == 1 || $check || $check == '1') {
            if ($this->getConfiguration('nextdomCheckCmdTime', 0) == 0) {
                $this->executeAlertCmdAction();
                return;
            }
            $next = strtotime('+ ' . ($this->getConfiguration('nextdomCheckCmdTime') + 1) . ' minutes ' . date('Y-m-d H:i:s'));
            $cron = CronManager::byClassAndFunction('cmd', 'cmdAlert', array('cmd_id' => intval($this->getId())));
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
            $cron->setOption(array('cmd_id' => intval($this->getId())));
            $cron->setSchedule(CronManager::convertDateToCron($next));
            $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->save();
        } else {
            $cron = CronManager::byClassAndFunction('cmd', 'cmdAlert', array('cmd_id' => intval($this->getId())));
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
        if (!is_array($this->getConfiguration('actionCheckCmd'))) {
            return;
        }
        foreach ($this->getConfiguration('actionCheckCmd') as $action) {
            try {
                $options = array();
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
        $url = $this->getConfiguration('nextdomPushUrl');
        if ($url == '') {
            $url = ConfigManager::byKey('cmdPushUrl');
        }
        if ($url == '') {
            return;
        }
        $replace = array(
            '#value#' => urlencode($_value),
            '#cmd_name#' => urlencode($this->getName()),
            '#cmd_id#' => $this->getId(),
            '#humanname#' => urlencode($this->getHumanName()),
            '#eq_name#' => urlencode($this->getEqLogicId()->getName()),
        );
        $url = str_replace(array_keys($replace), $replace, $url);
        LogHelper::add('event', 'info', __('Appels de l\'URL de push pour la commande ') . $this->getHumanName() . ' : ' . $url);
        $http = new \com_http($url);
        $http->setLogError(false);
        try {
            $http->exec();
        } catch (\Exception $e) {
            LogHelper::addError('cmd', __('Erreur push sur : ') . $url . ' => ' . $e->getMessage());
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
        if ($this->getType() != 'info' || $this->getType() == 'string') {
            return array();
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

    /**
     * @return array|bool|mixed|null|string
     */
    public function getLastValue()
    {
        return $this->getConfiguration('lastCmdValue', null);
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
        $this->setCache(array('ask::count' => 0, 'ask::token' => $token));
        $return = NetworkHelper::getNetworkAccess($_network) . '/core/api/jeeApi.php?';
        $return .= 'type=ask';
        $return .= '&plugin=' . $_plugin;
        $return .= '&apikey=' . Api::getApiKey($_plugin);
        $return .= '&token=' . $token;
        $return .= '&response=' . urlencode($_response);
        $return .= '&cmd_id=' . $this->getId();
        return $return;
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
        $dataStore->setType('scenario');
        $dataStore->setKey($this->getCache('ask::variable', 'none'));
        $dataStore->setValue($_response);
        $dataStore->setLink_id(-1);
        $dataStore->save();
        $this->setCache(array('ask::variable' => 'none', 'ask::count' => 0, 'ask::token' => null, 'ask::endtime' => null));
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
        if ($this->getType() != 'info' || $this->getType() == 'string') {
            return array();
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
        $class = new \ReflectionClass($this->getEqType());
        $method_toHtml = $class->getMethod('toHtml');
        $return = array();
        if ($method_toHtml->class == EqLogic::class) {
            $return['custom'] = true;
        } else {
            $return['custom'] = false;
        }
        $class = new \ReflectionClass($this->getEqType() . 'Cmd');
        $method_toHtml = $class->getMethod('toHtml');
        if ($method_toHtml->class == Cmd::class) {
            $return['custom'] = true;
        } else {
            $return['custom'] = false;
        }
        $class = $this->getEqType() . 'Cmd';
        if (property_exists($class, '_widgetPossibility')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $return = $class::$_widgetPossibility;
            if ($_key != '') {
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
                if (is_array($return)) {
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
        $return = Utils::o2a($cmd);
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
        return $return;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDirectUrlAccess()
    {
        $url = '/core/api/jeeApi.php?apikey=' . ConfigManager::byKey('api') . '&type=cmd&id=' . $this->getId();
        if ($this->getType() == 'action') {
            switch ($this->getSubType()) {
                case 'slider':
                    $url .= '&slider=50';
                    break;
                case 'color':
                    $url .= '&color=#123456';
                    break;
                case 'message':
                    $url .= '&title=montitre&message=monmessage';
                    break;
                case 'select':
                    $url .= '&select=value';
                    break;
            }
        }
        return NetworkHelper::getNetworkAccess('external') . $url;
    }

    /**
     * @param $_code
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function checkAccessCode($_code)
    {
        if ($this->getType() != 'action' || trim($this->getConfiguration('actionCodeAccess')) == '') {
            return true;
        }
        if (sha1($_code) == $this->getConfiguration('actionCodeAccess')) {
            $this->setConfiguration('actionCodeAccess', Utils::sha512($_code));
            $this->save();
            return true;
        }
        if (Utils::sha512($_code) == $this->getConfiguration('actionCodeAccess')) {
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
        $return = Utils::o2a($this);
        $return['currentValue'] = ($this->getType() !== 'action') ? $this->execCmd(null, 2) : $this->getConfiguration('lastCmdValue', null);
        return $return;
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param null $_drill
     * @return array|null
     * @throws \ReflectionException
     */
    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = null)
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
        $icon = ($this->getType() == 'info') ? Utils::findCodeIcon('fa-eye') : Utils::findCodeIcon('fa-hand-paper-o');
        $_data['node']['cmd' . $this->getId()] = array(
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
        );
        $usedBy = $this->getUsedBy();
        $use = $this->getUse();
        Utils::addGraphLink($this, 'cmd', $usedBy['scenario'], 'scenario', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $usedBy['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $usedBy['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $usedBy['interactDef'], 'interactDef', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'cmd', $usedBy['plan'], 'plan', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'cmd', $usedBy['view'], 'view', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        Utils::addGraphLink($this, 'cmd', $use['scenario'], 'scenario', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $use['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $use['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $use['dataStore'], 'dataStore', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'cmd', $this->getEqLogicId(), 'eqLogic', $_data, $_level, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        return $_data;
    }

    /**
     * @param bool $_array
     * @return array
     * @throws \ReflectionException
     */
    public function getUsedBy($_array = false)
    {
        $return = array('cmd' => array(), 'eqLogic' => array(), 'scenario' => array(), 'plan' => array(), 'view' => array());
        $return['cmd'] = CmdManager::searchConfiguration('#' . $this->getId() . '#');
        $return['eqLogic'] = EqLogicManager::searchConfiguration('#' . $this->getId() . '#');
        $return['scenario'] = ScenarioManager::searchByUse(array(array('action' => '#' . $this->getId() . '#')));
        $return['interactDef'] = InteractDefManager::searchByUse('#' . $this->getId() . '#');
        $return['view'] = ViewManager::searchByUse('cmd', $this->getId());
        $return['plan'] = PlanHeaderManager::searchByUse('cmd', $this->getId());
        if ($_array) {
            foreach ($return as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $return;
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
     * @param null $_user
     * @return bool
     * @throws \Exception
     */
    public function hasRight($_user = null)
    {
        if ($this->getType() == 'action') {
            return $this->getEqLogicId()->hasRight('x', $_user);
        } else {
            return $this->getEqLogicId()->hasRight('r', $_user);
        }
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
     * @param Cmd $srcCmd
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
     * @return array
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