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

use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
use NextDom\Enums\Common;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\Plan3dManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\Parents\BasePlan;
use NextDom\Model\Entity\Parents\NameEntity;

/**
 * Plan3d
 *
 * @ORM\Table(name="plan3d", indexes={@ORM\Index(name="name", columns={"name"}), @ORM\Index(name="link_type_link_id", columns={"link_type", "link_id"}), @ORM\Index(name="fk_plan3d_plan3dHeader1_idx", columns={"plan3dHeader_id"})})
 * @ORM\Entity
 */
class Plan3d extends BaseEntity
{
    const TABLE_NAME = NextDomObj::PLAN3D;

    use NameEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=127, nullable=true)
     */
    protected $link_type;

    /**
     * @var string
     *
     * @ORM\Column(name="link_id", type="string", length=127, nullable=true)
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

    /**
     * @var \NextDom\Model\Entity\Plan3dHeader
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Plan3dheader")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan3dHeader_id", referencedColumnName="id")
     * })
     */
    protected $plan3dHeader_id;

    protected $css;

    protected $_changed = false;

    public function preInsert()
    {
        if (in_array($this->getLink_type(), ['eqLogic', 'cmd', 'scenario'])) {
            Plan3dManager::removeByLinkTypeLinkId3dHeaderId($this->getLink_type(), $this->getLink_id(), $this->getPlan3dHeader_id());
        }
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
     * @return string
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
     * @return Plan3dHeader
     */
    public function getPlan3dHeader_id()
    {
        return $this->plan3dHeader_id;
    }

    /**
     * @param $_plan3dHeader_id
     * @return $this
     */
    public function setPlan3dHeader_id($_plan3dHeader_id)
    {
        $this->updateChangeState($this->plan3dHeader_id, $_plan3dHeader_id);
        $this->plan3dHeader_id = $_plan3dHeader_id;
        return $this;
    }

    public function preSave()
    {
        $default = [
            '3d::widget::light::power' => 6,
            '3d::widget::text::fontsize' => 24,
            '3d::widget::text::backgroundcolor' => '#ff6464',
            '3d::widget::text::backgroundtransparency' => 0.8,
            '3d::widget::text::bordercolor' => '#ff0000',
            '3d::widget::text::bordertransparency' => 1,
            '3d::widget::text::textcolor' => '#000000',
            '3d::widget::text::texttransparency' => 1,
            '3d::widget::text::space::z' => 10,
            '3d::widget::door::shutterclose' => '#0000ff',
            '3d::widget::door::windowclose' => '#ff0000',
            '3d::widget::door::windowopen' => '#00ff00',
            '3d::widget::door::rotate::0' => 'left',
            '3d::widget::door::rotate::1' => 'front',
            '3d::widget::door::rotate::way' => 1,
            '3d::widget::door::rotate' => 0,
            '3d::widget::door::windowopen::enableColor' => 0,
            '3d::widget::door::windowclose::enableColor' => 0,
            '3d::widget::door::shutterclose::enableColor' => 0,
            '3d::widget::door::translate' => 0,
            '3d::widget::door::translate::repeat' => 1,
        ];
        foreach ($default as $key => $value) {
            $this->setConfiguration($key, $this->getConfiguration($key, $value));
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

    public function save()
    {
        DBHelper::save($this);
    }

    public function remove()
    {
        DBHelper::remove($this);
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
        if (in_array($this->getLink_type(), ['eqLogic', 'cmd', 'scenario'])) {
            $link = $this->getLink();
            if (!is_object($link)) {
                return null;
            }
            return [
                '3d' => Utils::o2a($this),
                'html' => $link->toHtml($_version),
            ];
        }
        return null;
    }

    /**
     * @return bool|Cmd|EqLogic|JeeObject|Scenario|null
     * @throws \Exception
     */
    public function getLink()
    {
        if ($this->getLink_type() == 'eqLogic') {
            $eqLogic = EqLogicManager::byId(str_replace(['#', 'eqLogic'], '', $this->getLink_id()));
            return $eqLogic;
        } else if ($this->getLink_type() == 'scenario') {
            $scenario = ScenarioManager::byId($this->getLink_id());
            return $scenario;
        } else if ($this->getLink_type() == 'cmd') {
            $cmd = CmdManager::byId($this->getLink_id());
            return $cmd;
        } else if ($this->getLink_type() == Common::SUMMARY) {
            $linkedObject = JeeObjectManager::byId($this->getLink_id());
            return $linkedObject;
        }
        return null;
    }

    /**
     * @return array
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function additionalData()
    {
        $return = [];
        $return['cmd_id'] = str_replace('#', '', $this->getConfiguration('cmd::state'));
        $cmd = CmdManager::byId($return['cmd_id']);
        if (is_object($cmd) && $cmd->isType(CmdType::INFO)) {
            $return['state'] = $cmd->execCmd();
            $return['subType'] = $cmd->getSubType();
        }
        if ($this->getLink_type() == 'eqLogic') {
            if ($this->getConfiguration('3d::widget') == 'text') {
                $return['text'] = ScenarioExpressionManager::setTags($this->getConfiguration('3d::widget::text::text'));
                preg_match_all("/#([0-9]*)#/", $this->getConfiguration('3d::widget::text::text'), $matches);
                $return['cmds'] = $matches[1];
            }
            if ($this->getConfiguration('3d::widget') == 'door') {
                $return['cmds'] = [str_replace('#', '', $this->getConfiguration('3d::widget::door::window')), str_replace('#', '', $this->getConfiguration('3d::widget::door::shutter'))];
                $return['state'] = 0;
                $cmd = CmdManager::byId(str_replace('#', '', $this->getConfiguration('3d::widget::door::window')));
                if (is_object($cmd) && $cmd->isType(CmdType::INFO)) {
                    $cmd_value = $cmd->execCmd();
                    if ($this->isSubType(CmdSubType::BINARY) && $cmd->getDisplay('invertBinary') == 1) {
                        $cmd_value = ($cmd_value == 1) ? 0 : 1;
                    }
                    $return['state'] = $cmd_value;
                }
                if ($return['state'] > 0) {
                    $cmd = CmdManager::byId(str_replace('#', '', $this->getConfiguration('3d::widget::door::shutter')));
                    if (is_object($cmd) && $cmd->isType(CmdType::INFO)) {
                        $cmd_value = $cmd->execCmd();
                        if ($this->isSubType(CmdSubType::BINARY) && $cmd->getDisplay('invertBinary') == 1) {
                            $cmd_value = ($cmd_value == 1) ? 0 : 1;
                        }
                        if ($cmd_value) {
                            $return['state'] = 2;
                        }
                    }
                }
            }
            if ($this->getConfiguration('3d::widget') == 'conditionalColor') {
                $return['color'] = '';
                $return['cmds'] = [];
                $conditions = $this->getConfiguration('3d::widget::conditionalColor::condition');
                if (!is_array($conditions) || count($conditions) == 0) {
                    return $return;
                }
                foreach ($conditions as $condition) {
                    if (!isset($condition['color'])) {
                        continue;
                    }
                    if (!isset($condition['cmd'])) {
                        continue;
                    }
                    preg_match_all("/#([0-9]*)#/", $condition['cmd'], $matches);
                    foreach ($matches[1] as $cmd_id) {
                        $return['cmds'][] = $cmd_id;
                    }
                }
                foreach ($conditions as $condition) {
                    if (!isset($condition['color'])) {
                        continue;
                    }
                    if (!isset($condition['cmd'])) {
                        continue;
                    }
                    if (NextDomHelper::evaluateExpression($condition['cmd'])) {
                        $return['color'] = $condition['color'];
                        return $return;
                    }
                }
            }
        } elseif ($this->getLink_type() == 'scenario') {

        } elseif ($this->getLink_type() == 'cmd') {

        } elseif ($this->getLink_type() == Common::SUMMARY) {

        }
        return $return;
    }

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getPlan3dHeader()
    {
        return Plan3dHeaderManager::byId($this->getPlan3dHeader_id());
    }
}
