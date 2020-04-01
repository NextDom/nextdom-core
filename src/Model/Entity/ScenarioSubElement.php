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
use NextDom\Enums\ScenarioSubElementType;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\OptionsEntity;
use NextDom\Model\Entity\Parents\OrderEntity;
use NextDom\Model\Entity\Parents\SubTypeEntity;
use NextDom\Model\Entity\Parents\TypeEntity;

/**
 * Scenariosubelement
 *
 * ORM\Table(name="scenarioSubElement", indexes={@ORM\Index(name="fk_scenarioSubElement_scenarioElement1_idx", columns={"scenarioElement_id"}), @ORM\Index(name="type", columns={"scenarioElement_id", "type"})})
 * ORM\Entity
 */
class ScenarioSubElement extends BaseEntity
{
    const TABLE_NAME = NextDomObj::SCENARIO_SUB_ELEMENT;

    use OptionsEntity, NameEntity, TypeEntity, SubTypeEntity, OrderEntity;

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
        $this->updateChangeState($this->scenarioElement_id, $_scenarioElement_id);
        $this->scenarioElement_id = $_scenarioElement_id;
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
        if ($this->isSubtype(ScenarioSubElementType::ACTION)) {
            $_scenario->setLog(__('Exécution du sous-élément de type [action] : ') . $this->getType());
            $result = true;
            foreach ($this->getExpression() as $expression) {
                $result = $expression->execute($_scenario);
            }
            return $result;
        }
        if ($this->isSubtype(ScenarioSubElementType::CONDITION)) {
            $_scenario->setLog(__('Exécution du sous-élément de type [condition] : ') . $this->getType());
            foreach ($this->getExpression() as $expression) {
                return $expression->execute($_scenario);
            }
        }
        return null;
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

    public function remove()
    {
        foreach ($this->getExpression() as $expression) {
            $expression->remove();
        }
        return parent::remove();
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
}
