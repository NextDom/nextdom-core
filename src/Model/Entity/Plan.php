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

use NextDom\Enums\PlanDisplayType;
use NextDom\Enums\PlanLinkType;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;

/**
 * Plan
 *
 * @ORM\Table(name="plan", indexes={@ORM\Index(name="unique", columns={"link_type", "link_id"}), @ORM\Index(name="fk_plan_planHeader1_idx", columns={"planHeader_id"})})
 * @ORM\Entity
 */
class Plan implements EntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=127, nullable=true)
     */
    protected $link_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=true)
     */
    protected $link_id;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="text", length=65535, nullable=true)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;

    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", length=65535, nullable=true)
     */
    protected $css;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    protected $_changed = false;

    /**
     * @var \NextDom\Model\Entity\PlanHeader
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Planheader")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="planHeader_id", referencedColumnName="id")
     * })
     */
    protected $planHeader_id;

    public function preInsert()
    {
        if ($this->getCss('z-index') == '') {
            $this->setCss('z-index', 1000);
        }
        if (in_array($this->getLink_type(), ['eqLogic', 'cmd', 'scenario'])) {
            PlanManager::removeByLinkTypeLinkIdPlanHedaerId($this->getLink_type(), $this->getLink_id(), $this->getPlanHeader_id());
        }
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getCss($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->css, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setCss($_key, $_value)
    {
        $css = Utils::setJsonAttr($this->css, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->css, $css);
        $this->css = $css;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink_type()
    {
        return $this->link_type;
    }

    /**
     * @param $_link_type
     * @return $this
     */
    public function setLink_type($_link_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_type, $_link_type);
        $this->link_type = $_link_type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLink_id()
    {
        return $this->link_id;
    }

    /**
     * @param $_link_id
     * @return $this
     */
    public function setLink_id($_link_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    /**
     * @return PlanHeader
     */
    public function getPlanHeader_id()
    {
        return $this->planHeader_id;
    }

    /**
     * @param $_planHeader_id
     * @return $this
     */
    public function setPlanHeader_id($_planHeader_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->planHeader_id, $_planHeader_id);
        $this->planHeader_id = $_planHeader_id;
        return $this;
    }

    public function preSave()
    {
        if ($this->getCss('zoom') != '' && (!is_numeric($this->getCss('zoom')) || $this->getCss('zoom')) < 0.1) {
            $this->setCss('zoom', 1);
        }
        if ($this->getLink_id() == '') {
            $this->setLink_id(mt_rand(0, 99999999) + 9999);
        }
    }

    public function remove()
    {
        DBHelper::remove($this);
    }

    /**
     * @return Plan
     */
    public function copy(): Plan
    {
        $planCopy = clone $this;
        $planCopy->setId('')
            ->setLink_id('')
            ->setPosition('top', '')
            ->setPosition('left', '');
        $planCopy->save();
        return $planCopy;
    }

    public function save()
    {
        DBHelper::save($this);
    }

    public function execute()
    {
        if ($this->getLink_type() != PlanLinkType::ZONE) {
            return;
        }
        if ($this->getConfiguration('zone_mode', 'simple') == 'simple') {
            $this->doAction('other');
        } elseif ($this->getConfiguration('zone_mode', 'simple') == 'binary') {
            $result = NextDomHelper::evaluateExpression($this->getConfiguration('binary_info', 0));
            if ($result) {
                $this->doAction('off');
            } else {
                $this->doAction('on');
            }
        }
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
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @param $_action
     * @throws \Exception
     */
    public function doAction($_action)
    {
        foreach ($this->getConfiguration('action_' . $_action) as $action) {
            try {
                $cmd = CmdManager::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = [];
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
            } catch (\Exception $e) {
                LogHelper::addError('design', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Détails : ') . $e->getMessage());
            }
        }
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
     * @param string $_version
     * @return array|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     * @throws \NextDom\Exceptions\OperatingSystemException
     */
    public function getHtml($_version = 'dplan')
    {
        switch ($this->getLink_type()) {
            case PlanLinkType::EQLOGIC:
            case PlanLinkType::CMD:
            case PlanLinkType::SCENARIO:
                $link = $this->getLink();
                if (!is_object($link)) {
                    return null;
                }
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $link->toHtml($_version),
                ];
                break;
            case PlanLinkType::PLAN:
                $html = '<span class="cursor plan-link-widget" data-link_id="' . $this->getLink_id() . '" data-offsetX="' . $this->getDisplay(PlanDisplayType::OFFSET_X) . '" data-offsetY="' . $this->getDisplay(PlanDisplayType::OFFSET_Y) . '">';
                $html .= '<a style="color:' . $this->getCss('color', 'black') . ';text-decoration:none;font-size : 1.5em;">';
                $html .= $this->getDisplay(PlanDisplayType::ICON) . ' ' . $this->getDisplay(PlanDisplayType::NAME);
                $html .= '</a>';
                $html .= '</span>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
                break;
            case PlanLinkType::VIEW:
                $link = 'index.php?p=view&view_id=' . $this->getLink_id();
                $html = '<span href="' . $link . '" class="cursor view-link-widget" data-link_id="' . $this->getLink_id() . '" >';
                $html .= '<a href="' . $link . '" class="noOnePageLoad" style="color:' . $this->getCss('color', 'black') . ';text-decoration:none;font-size : 1.5em;">';
                $html .= $this->getDisplay(PlanDisplayType::ICON) . ' ' . $this->getDisplay(PlanDisplayType::NAME);
                $html .= '</a>';
                $html .= '</span>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
                break;
            case PlanLinkType::GRAPH:
                $background_color = 'background-color : white;';
                if ($this->getDisplay(PlanDisplayType::TRANSPARENT_BACKGROUND, false)) {
                    $background_color = '';
                }
                $html = '<div class="graph-widget" data-graph_id="' . $this->getLink_id() . '" style="' . $background_color . 'border : solid 1px black;min-height:50px;min-width:50px;">';
                $html .= '<span class="graphOptions" style="display:none;">' . json_encode($this->getDisplay(PlanDisplayType::GRAPH, [])) . '</span>';
                $html .= '<div class="graph" id="graph' . $this->getLink_id() . '" style="width : 100%;height : 100%;"></div>';
                $html .= '</div>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
            case PlanLinkType::TEXT:
                $html = '<div class="text-widget" data-text_id="' . $this->getLink_id() . '" style="color:' . $this->getCss('color', 'black') . ';">';
                if ($this->getDisplay(PlanDisplayType::NAME) != '' || $this->getDisplay(PlanDisplayType::ICON) != '') {
                    $html .= $this->getDisplay(PlanDisplayType::ICON) . ' ' . $this->getDisplay(PlanDisplayType::TEXT);
                } else {
                    $html .= $this->getDisplay(PlanDisplayType::TEXT);
                }
                $html .= '</div>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
                break;
            case PlanLinkType::IMAGE:
                $html = '<div class="image-widget" data-image_id="' . $this->getLink_id() . '" style="min-width:10px;min-height:10px;">';
                if ($this->getConfiguration('display_mode', 'image') == 'image') {
                    $html .= '<img style="width:100%;height:100%" src="' . $this->getDisplay(PlanDisplayType::PATH, 'public/img/NextDom_NoPicture_Gray.png') . '"/>';
                } else {
                    $camera = EqLogicManager::byId(str_replace(['#', 'eqLogic'], ['', ''], $this->getConfiguration('camera')));
                    if (is_object($camera)) {
                        $html .= $camera->toHtml($_version, true);
                    }
                }
                $html .= '</div>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
                break;
            case PlanLinkType::ZONE:
                if ($this->getConfiguration('zone_mode', 'simple') == 'widget') {
                    $cssClass = '';
                    if ($this->getConfiguration('showOnFly') == 1) {
                        $cssClass .= 'zoneEqLogicOnFly ';
                    }
                    if ($this->getConfiguration('showOnClic') == 1) {
                        $cssClass .= 'zoneEqLogicOnClic ';
                    }
                    $html = '<div class="zone-widget cursor zoneEqLogic ' . $cssClass . '" data-position="' . $this->getConfiguration('position') . '" data-eqLogic_id="' . str_replace(['#', 'eqLogic'], ['', ''], $this->getConfiguration('eqLogic')) . '" data-zone_id="' . $this->getLink_id() . '" style="min-width:20px;min-height:20px;"></div>';
                } else {
                    $html = '<div class="zone-widget cursor" data-zone_id="' . $this->getLink_id() . '" style="min-width:20px;min-height:20px;"></div>';
                }
                return [
                    'plan' => NextDomHelper::toHumanReadable(Utils::o2a($this)),
                    'html' => $html,
                ];
                break;
            case PlanLinkType::SUMMARY:
                $background_color = 'background-color : ' . $this->getCss('background-color', 'black') . ';';
                if ($this->getDisplay(PlanDisplayType::BACKGROUND_DEFAULT, false)) {
                    $background_color = 'background-color : black;';
                }
                if ($this->getDisplay(PlanDisplayType::TRANSPARENT_BACKGROUND, false)) {
                    $background_color = '';
                }
                $color = 'color : ' . $this->getCss('color', 'black') . ';';
                if ($this->getDisplay(PlanDisplayType::COLOR_DEFAULT, false)) {
                    $color = '';
                }
                $html = '<div class="summary-widget" data-summary_id="' . $this->getLink_id() . '" style="' . $background_color . $color . ';min-width:10px;min-height:10px;">';
                $summary = '';
                if ($this->getLink_id() == 0) {
                    $summary = JeeObjectManager::getGlobalHtmlSummary($_version);
                } else {
                    $linkedObject = $this->getLink();
                    if (is_object($linkedObject)) {
                        $summary = $linkedObject->getHtmlSummary($_version);
                    }
                }
                if ($summary == '') {
                    $html .= __('Non configuré');
                } else {
                    $html .= $summary;
                }
                $html .= '</div>';
                return [
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                ];
                break;
        }
        return null;
    }

    /**
     * @return bool|Cmd|EqLogic|JeeObject|Scenario|null
     * @throws \Exception
     */
    public function getLink()
    {
        if ($this->getLink_type() == PlanLinkType::EQLOGIC) {
            $eqLogic = EqLogicManager::byId($this->getLink_id());
            return $eqLogic;
        } elseif ($this->getLink_type() == PlanLinkType::SCENARIO) {
            $scenario = ScenarioManager::byId($this->getLink_id());
            return $scenario;
        } elseif ($this->getLink_type() == PlanLinkType::CMD) {
            $cmd = CmdManager::byId($this->getLink_id());
            return $cmd;
        } elseif ($this->getLink_type() == PlanLinkType::SUMMARY) {
            $linkedObject = JeeObjectManager::byId($this->getLink_id());
            return $linkedObject;
        }
        return null;
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
        $display = Utils::setJsonAttr($this->display, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->display, $display);
        $this->display = $display;
        return $this;
    }

    /**
     * @return PlanHeader|null
     * @throws \Exception
     */
    public function getPlanHeader()
    {
        return PlanHeaderManager::byId($this->getPlanHeader_id());
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getPosition($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->position, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setPosition($_key, $_value)
    {
        $position = Utils::setJsonAttr($this->position, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->position, $position);
        $this->position = $position;
        return $this;
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
     * @return string
     */
    public function getTableName()
    {
        return 'plan';
    }

}
