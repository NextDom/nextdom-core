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
use NextDom\Helpers\Utils;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioExpressionManager;

/**
 * Scenariosubelement
 *
 * ORM\Table(name="scenarioSubElement", indexes={@ORM\Index(name="fk_scenarioSubElement_scenarioElement1_idx", columns={"scenarioElement_id"}), @ORM\Index(name="type", columns={"scenarioElement_id", "type"})})
 * ORM\Entity
 */
class ScenarioSubElement implements EntityInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="subtype", type="string", length=127, nullable=true)
     */
    protected $subtype;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \NextDom\Model\Entity\ScenarioElement
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Scenarioelement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scenarioElement_id", referencedColumnName="id")
     * })
     */
    protected $scenarioElement_id;

    protected $_expression;

    protected $_changed = false;

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
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    /**
     * @return ScenarioElement
     * @throws \Exception
     */
    public function getElement()
    {
        return ScenarioElementManager::byId($this->getScenarioElement_id());
    }

    /**
     * @return ScenarioElement
     */
    public function getScenarioElement_id()
    {
        return $this->scenarioElement_id;
    }

    /**
     * @param $_scenarioElement_id
     * @return $this
     */
    public function setScenarioElement_id($_scenarioElement_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->scenarioElement_id, $_scenarioElement_id);
        $this->scenarioElement_id = $_scenarioElement_id;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getOptions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->options, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setOptions($_key, $_value)
    {
        $options = Utils::setJsonAttr($this->options, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->options, $options);
        $this->options = $options;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
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
     * @param Scenario $_scenario
     * @return bool|null
     * @throws \Exception
     */
    public function execute(&$_scenario = null)
    {
        if ($_scenario != null && !$_scenario->getDo()) {
            return null;
        }
        if ($this->getSubtype() == 'action') {
            $_scenario->setLog(__('Exécution du sous-élément de type [action] : ') . $this->getType());
            $return = true;
            foreach ($this->getExpression() as $expression) {
                $return = $expression->execute($_scenario);
            }
            return $return;
        }
        if ($this->getSubtype() == 'condition') {
            $_scenario->setLog(__('Exécution du sous-élément de type [condition] : ') . $this->getType());
            foreach ($this->getExpression() as $expression) {
                return $expression->execute($_scenario);
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * @param $_subtype
     * @return $this
     */
    public function setSubtype($_subtype)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->subtype, $_subtype);
        $this->subtype = $_subtype;
        return $this;
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
     * @return ScenarioExpression|ScenarioExpression[]|null
     * @throws \Exception
     */
    public function getExpression()
    {
        if (is_array($this->_expression) && count($this->_expression) > 0) {
            return $this->_expression;
        }
        $this->_expression = ScenarioExpressionManager::byscenarioSubElementId($this->getId());
        return $this->_expression;
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

    public function remove()
    {
        foreach ($this->getExpression() as $expression) {
            $expression->remove();
        }
        DBHelper::remove($this);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllId()
    {
        $return = [
            'element' => [],
            'subelement' => [$this->getId()],
            'expression' => [],
        ];
        foreach ($this->getExpression() as $expression) {
            $result = $expression->getAllId();
            $return['element'] = array_merge($return['element'], $result['element']);
            $return['subelement'] = array_merge($return['subelement'], $result['subelement']);
            $return['expression'] = array_merge($return['expression'], $result['expression']);
        }
        return $return;
    }

    /**
     * @param $_scenarioElement_id
     * @return int
     * @throws \Exception
     */
    public function copy($_scenarioElement_id)
    {
        $subElementCopy = clone $this;
        $subElementCopy->setId('');
        $subElementCopy->setScenarioElement_id($_scenarioElement_id);
        $subElementCopy->save();
        foreach ($this->getExpression() as $expression) {
            $expression->copy($subElementCopy->getId());
        }
        return $subElementCopy->getId();
    }

    public function save()
    {
        DBHelper::save($this);
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'scenarioSubElement';
    }

}
