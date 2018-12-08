<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scenariosubelement
 *
 * @ORM\Table(name="scenarioSubElement", indexes={@ORM\Index(name="fk_scenarioSubElement_scenarioElement1_idx", columns={"scenarioElement_id"}), @ORM\Index(name="type", columns={"scenarioElement_id", "type"})})
 * @ORM\Entity
 */
class Scenariosubelement
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
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    private $name;

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
     * @var \NextDom\Model\Entity\Scenarioelement
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Scenarioelement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scenarioElement_id", referencedColumnName="id")
     * })
     */
    private $scenarioelement;

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

    public function getName()
    {
        return $this->name;
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

    public function getScenarioelement(): \NextDom\Model\Entity\Scenarioelement
    {
        return $this->scenarioelement;
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

    public function setName($name)
    {
        $this->name = $name;
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

    public function setScenarioelement(\NextDom\Model\Entity\Scenarioelement $scenarioelement)
    {
        $this->scenarioelement = $scenarioelement;
        return $this;
    }

}
