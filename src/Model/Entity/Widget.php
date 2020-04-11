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

use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\WidgetManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\DisplayEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\SubTypeEntity;
use NextDom\Model\Entity\Parents\TypeEntity;
use ReflectionException;

/**
 * Widget
 *
 * @ORM\Table(name="Widget")
 * @ORM\Entity
 */
class Widget extends BaseEntity {

    const TABLE_NAME = NextDomObj::WIDGET;

    use NameEntity,
        TypeEntity,
        SubTypeEntity,
        DisplayEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\Column(name="replace", type="text", length=65535, nullable=true)
     */
    protected $replace;

    /**
     * @var string
     *
     * @ORM\Column(name="test", type="text", length=65535, nullable=true)
     */
    protected $test;

    /**
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * @param $_template
     * @return $this
     */
    public function setTemplate($_template) {
        $this->updateChangeState($this->template, $_template);
        $this->template = $_template;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplace() {
        return $this->replace;
    }

    /**
     * @param $_replace
     * @return $this
     */
    public function setReplace($_replace) {
        $this->updateChangeState($this->replace, $_replace);
        $this->replace = $_replace;
        return $this;
    }

    /**
     * @return string
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * @param $_test
     * @return $this
     */
    public function setTest($_test) {
        $this->updateChangeState($this->test, $_test);
        $this->test = $_test;
        return $this;
    }

    public function preInsert() {
        
    }

    public function preSave() {
        if ($this->getType() == '') {
            $this->setType('action');
        }
    }

    public function preUpdate() {
        $widget = WidgetManager::byId($this->getId());
        if ($widget->getName() != $this->getName()) {
            $usedBy = $widget->getUsedBy();
            if (is_array($usedBy) && count($usedBy) > 0) {
                foreach ($usedBy as $cmd) {
                    if ($cmd->getTemplate('dashboard') == 'custom::' . $widget->getName()) {
                        $cmd->setTemplate('dashboard', 'custom::' . $this->getName());
                    }
                    if ($cmd->getTemplate('mobile') == 'custom::' . $widget->getName()) {
                        $cmd->setTemplate('mobile', 'custom::' . $this->getName());
                    }
                    $cmd->save(true);
                }
            }
        }
        if ($widget->getType() != $this->getType() || $widget->getSubType() != $this->getSubType()) {
            $usedBy = $widget->getUsedBy();
            if (is_array($usedBy) && count($usedBy) > 0) {
                foreach ($usedBy as $cmd) {
                    if ($cmd->getTemplate('dashboard') == 'custom::' . $widget->getName()) {
                        $cmd->setTemplate('dashboard', 'default');
                    }
                    if ($cmd->getTemplate('mobile') == 'custom::' . $widget->getName()) {
                        $cmd->setTemplate('mobile', 'default');
                    }
                    $cmd->save(true);
                }
            }
        }
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getFiltres($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->filtres, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setFiltres($_key, $_value) {
        $filtres = Utils::setJsonAttr($this->filtres, $_key, $_value);
        $this->updateChangeState($this->filtres, $filtres);
        $this->filtres = $filtres;
        return $this;
    }

    /**
     * @return bool
     * @throws CoreException
     * @throws ReflectionException
     */
    public function save() {
        DBHelper::save($this);
        return true;
    }

    public function postSave() {
        $usedBy = $this->getUsedBy();
        if (is_array($usedBy) && count($usedBy) > 0) {
            foreach ($usedBy as $cmd) {
                $eqLogic = $cmd->getEqLogic();
                if (is_object($eqLogic)) {
                    $eqLogic->emptyCacheWidget();
                }
            }
        }
    }

    public function preRemove() {
        
    }

    public function remove() {
        $usedBy = $this->getUsedBy();
        if (is_array($usedBy) && count($usedBy) > 0) {
            foreach ($usedBy as $cmd) {
                if ($cmd->getTemplate('dashboard') == 'custom::' . $this->getName()) {
                    $cmd->setTemplate('dashboard', 'default');
                }
                if ($cmd->getTemplate('mobile') == 'custom::' . $this->getName()) {
                    $cmd->setTemplate('mobile', 'default');
                }
                $cmd->save(true);
            }
        }
        DBHelper::remove($this);
    }

    public function getUsedBy() {
        return array_merge(
            CmdManager::searchTemplate('dashboard":"custom::' . $this->getName() . '"'),
            CmdManager::searchTemplate('mobile":"custom::' . $this->getName() . '"')
        );
    }

    public function postRemove() {
        //WidgetManager::cleanWidget();
    }

    public function emptyTest() {
        $this->test = null;
    }

    /**
     * @return string
     */
    public function getLinkToConfiguration() {
        return 'index.php?v=d&p=widget&id=' . $this->getId();
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param int $_drill
     * @return array|null
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = 3) {
        if (isset($_data['node']['widget' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = Utils::findCodeIcon('fa-comments-o');
        $_data['node']['widget' . $this->getId()] = [
            'id' => 'widget' . $this->getId(),
            'name' => substr($this->getHumanName(), 0, 20),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=widget&id=' . $this->getId(),
        ];
        return null;
    }

    /**
     * @return string
     */
    public function getHumanName() {
        return $this->getName();
    }

}
