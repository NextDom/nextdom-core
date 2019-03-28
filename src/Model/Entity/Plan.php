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
class Plan
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
        if (in_array($this->getLink_type(), array('eqLogic', 'cmd', 'scenario'))) {
            PlanManager::removeByLinkTypeLinkIdPlanHedaerId($this->getLink_type(), $this->getLink_id(), $this->getPlanHeader_id());
        }
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

    public function save()
    {
        \DB::save($this);
    }

    public function remove()
    {
        \DB::remove($this);
    }

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

    public function getLink()
    {
        if ($this->getLink_type() == 'eqLogic') {
            $eqLogic = EqLogicManager::byId($this->getLink_id());
            return $eqLogic;
        } elseif ($this->getLink_type() == 'scenario') {
            $scenario = ScenarioManager::byId($this->getLink_id());
            return $scenario;
        } elseif ($this->getLink_type() == 'cmd') {
            $cmd = CmdManager::byId($this->getLink_id());
            return $cmd;
        } elseif ($this->getLink_type() == 'summary') {
            $object = JeeObjectManager::byId($this->getLink_id());
            return $object;
        }
        return null;
    }

    public function execute()
    {
        if ($this->getLink_type() != 'zone') {
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

    public function doAction($_action)
    {
        foreach ($this->getConfiguration('action_' . $_action) as $action) {
            try {
                $cmd = CmdManager::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
            } catch (\Exception $e) {
                LogHelper::addError('design', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Détails : ') . $e->getMessage());
            }
        }
    }

    public function getHtml($_version = 'dplan')
    {
        switch ($this->getLink_type()) {
            case 'eqLogic':
            case 'cmd':
            case 'scenario':
                $link = $this->getLink();
                if (!is_object($link)) {
                    return null;
                }
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $link->toHtml($_version),
                );
                break;
            case 'plan':
                $html = '<span class="cursor plan-link-widget" data-link_id="' . $this->getLink_id() . '" data-offsetX="' . $this->getDisplay('offsetX') . '" data-offsetY="' . $this->getDisplay('offsetY') . '">';
                $html .= '<a style="color:' . $this->getCss('color', 'black') . ';text-decoration:none;font-size : 1.5em;">';
                $html .= $this->getDisplay('icon') . ' ' . $this->getDisplay('name');
                $html .= '</a>';
                $html .= '</span>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
            case 'view':
                $link = 'index.php?p=view&view_id=' . $this->getLink_id();
                $html = '<span href="' . $link . '" class="cursor view-link-widget" data-link_id="' . $this->getLink_id() . '" >';
                $html .= '<a href="' . $link . '" class="noOnePageLoad" style="color:' . $this->getCss('color', 'black') . ';text-decoration:none;font-size : 1.5em;">';
                $html .= $this->getDisplay('icon') . ' ' . $this->getDisplay('name');
                $html .= '</a>';
                $html .= '</span>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
            case 'graph':
                $background_color = 'background-color : white;';
                if ($this->getDisplay('transparentBackground', false)) {
                    $background_color = '';
                }
                $html = '<div class="graph-widget" data-graph_id="' . $this->getLink_id() . '" style="' . $background_color . 'border : solid 1px black;min-height:50px;min-width:50px;">';
                $html .= '<span class="graphOptions" style="display:none;">' . json_encode($this->getDisplay('graph', array())) . '</span>';
                $html .= '<div class="graph" id="graph' . $this->getLink_id() . '" style="width : 100%;height : 100%;"></div>';
                $html .= '</div>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
            case 'text':
                $html = '<div class="text-widget" data-text_id="' . $this->getLink_id() . '" style="color:' . $this->getCss('color', 'black') . ';">';
                if ($this->getDisplay('name') != '' || $this->getDisplay('icon') != '') {
                    $html .= $this->getDisplay('icon') . ' ' . $this->getDisplay('text');
                } else {
                    $html .= $this->getDisplay('text');
                }
                $html .= '</div>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
            case 'image':
                $html = '<div class="image-widget" data-image_id="' . $this->getLink_id() . '" style="min-width:10px;min-height:10px;">';
                if ($this->getConfiguration('display_mode', 'image') == 'image') {
                    $html .= '<img style="width:100%;height:100%" src="' . $this->getDisplay('path', 'public/img/NextDom_NoPicture.png') . '"/>';
                } else {
                    $camera = EqLogicManager::byId(str_replace(array('#', 'eqLogic'), array('', ''), $this->getConfiguration('camera')));
                    if (is_object($camera)) {
                        $html .= $camera->toHtml($_version);
                    }
                }
                $html .= '</div>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
            case 'zone':
                if ($this->getConfiguration('zone_mode', 'simple') == 'widget') {
                    $class = '';
                    if ($this->getConfiguration('showOnFly') == 1) {
                        $class .= 'zoneEqLogicOnFly ';
                    }
                    if ($this->getConfiguration('showOnClic') == 1) {
                        $class .= 'zoneEqLogicOnClic ';
                    }
                    $html = '<div class="zone-widget cursor zoneEqLogic ' . $class . '" data-position="' . $this->getConfiguration('position') . '" data-eqLogic_id="' . str_replace(array('#', 'eqLogic'), array('', ''), $this->getConfiguration('eqLogic')) . '" data-zone_id="' . $this->getLink_id() . '" style="min-width:20px;min-height:20px;"></div>';
                } else {
                    $html = '<div class="zone-widget cursor" data-zone_id="' . $this->getLink_id() . '" style="min-width:20px;min-height:20px;"></div>';
                }
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
            case 'summary':
                $background_color = 'background-color : ' . $this->getCss('background-color', 'black') . ';';
                if ($this->getDisplay('background-defaut', false)) {
                    $background_color = 'background-color : black;';
                }
                if ($this->getDisplay('background-transparent', false)) {
                    $background_color = '';
                }
                $color = 'color : ' . $this->getCss('color', 'black') . ';';
                if ($this->getDisplay('color-defaut', false)) {
                    $color = '';
                }
                $html = '<div class="summary-widget" data-summary_id="' . $this->getLink_id() . '" style="' . $background_color . $color . ';min-width:10px;min-height:10px;">';
                $summary = '';
                if ($this->getLink_id() == 0) {
                    $summary = JeeObjectManager::getGlobalHtmlSummary($_version);
                } else {
                    $object = $this->getLink();
                    if (is_object($object)) {
                        $summary = $object->getHtmlSummary($_version);
                    }
                }
                if ($summary == '') {
                    $html .= __('Non configuré');
                } else {
                    $html .= $summary;
                }
                $html .= '</div>';
                return array(
                    'plan' => Utils::o2a($this),
                    'html' => $html,
                );
                break;
        }
        return null;
    }

    public function getPlanHeader()
    {
        return PlanHeaderManager::byId($this->getPlanHeader_id());
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLink_type()
    {
        return $this->link_type;
    }

    public function getLink_id()
    {
        return $this->link_id;
    }

    public function getPosition($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->position, $_key, $_default);
    }

    public function getDisplay($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->display, $_key, $_default);
    }

    public function getCss($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->css, $_key, $_default);
    }

    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    public function setLink_type($_link_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_type, $_link_type);
        $this->link_type = $_link_type;
        return $this;
    }

    public function setLink_id($_link_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    public function setPosition($_key, $_value)
    {
        $position = Utils::setJsonAttr($this->position, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->position, $position);
        $this->position = $position;
        return $this;
    }

    public function setDisplay($_key, $_value)
    {
        $display = Utils::setJsonAttr($this->display, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->display, $display);
        $this->display = $display;
        return $this;
    }

    public function setCss($_key, $_value)
    {
        $css = Utils::setJsonAttr($this->css, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->css, $css);
        $this->css = $css;
        return $this;
    }

    public function getPlanHeader_id()
    {
        return $this->planHeader_id;
    }

    public function setPlanHeader_id($_planHeader_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->planHeader_id, $_planHeader_id);
        $this->planHeader_id = $_planHeader_id;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value)
    {
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    public function getTableName()
    {
        return 'plan';
    }

}
