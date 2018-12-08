<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Listener
 *
 * @ORM\Table(name="listener", indexes={@ORM\Index(name="event", columns={"event"})})
 * @ORM\Entity
 */
class Listener
{

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=127, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=127, nullable=true)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255, nullable=true)
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="option", type="text", length=65535, nullable=true)
     */
    private $option;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getClass()
    {
        return $this->class;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
