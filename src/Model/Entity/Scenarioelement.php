<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scenarioelement
 *
 * @ORM\Table(name="scenarioElement")
 * @ORM\Entity
 */
class Scenarioelement
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
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

    public function getOrder()
    {
        return $this->order;
    }

    public function getType()
    {
        return $this->type;
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

}
