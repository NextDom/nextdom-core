<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cron
 *
 * @ORM\Table(name="cron", uniqueConstraints={@ORM\UniqueConstraint(name="class_function_option", columns={"class", "function", "option"})}, indexes={@ORM\Index(name="type", columns={"class"}), @ORM\Index(name="logicalId_Type", columns={"class"}), @ORM\Index(name="deamon", columns={"deamon"})})
 * @ORM\Entity
 */
class Cron
{

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    private $enable;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=127, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=127, nullable=false)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="string", length=127, nullable=true)
     */
    private $schedule;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    private $timeout;

    /**
     * @var integer
     *
     * @ORM\Column(name="deamon", type="integer", nullable=true)
     */
    private $deamon = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="deamonSleepTime", type="integer", nullable=true)
     */
    private $deamonsleeptime;

    /**
     * @var string
     *
     * @ORM\Column(name="option", type="string", length=255, nullable=true)
     */
    private $option;

    /**
     * @var integer
     *
     * @ORM\Column(name="once", type="integer", nullable=true)
     */
    private $once;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getEnable()
    {
        return $this->enable;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getDeamon()
    {
        return $this->deamon;
    }

    public function getDeamonsleeptime()
    {
        return $this->deamonsleeptime;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getOnce()
    {
        return $this->once;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setEnable($enable)
    {
        $this->enable = $enable;
        return $this;
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

    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setDeamon($deamon)
    {
        $this->deamon = $deamon;
        return $this;
    }

    public function setDeamonsleeptime($deamonsleeptime)
    {
        $this->deamonsleeptime = $deamonsleeptime;
        return $this;
    }

    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function setOnce($once)
    {
        $this->once = $once;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
