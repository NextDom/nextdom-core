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

/**
 * Scenarioexpression
 *
 * @ORM\Table(name="scenarioExpression", indexes={@ORM\Index(name="fk_scenarioExpression_scenarioSubElement1_idx", columns={"scenarioSubElement_id"})})
 * @ORM\Entity
 */
class Scenarioexpression
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="subtype", type="string", length=127, nullable=true)
     */
    private $subtype;

    /**
     * @var string
     *
     * @ORM\Column(name="expression", type="text", length=65535, nullable=true)
     */
    private $expression;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="log", type="text", length=65535, nullable=true)
     */
    private $log;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\Scenariosubelement
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Scenariosubelement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scenarioSubElement_id", referencedColumnName="id")
     * })
     */
    private $scenariosubelement;

    public function getOrder()
    {
        return $this->order;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubtype()
    {
        return $this->subtype;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getScenariosubelement(): \NextDom\Model\Entity\Scenariosubelement
    {
        return $this->scenariosubelement;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
        return $this;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setScenariosubelement(\NextDom\Model\Entity\Scenariosubelement $scenariosubelement)
    {
        $this->scenariosubelement = $scenariosubelement;
        return $this;
    }

}
