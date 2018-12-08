<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Interactdef
 *
 * @ORM\Table(name="interactDef")
 * @ORM\Entity
 */
class Interactdef
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    private $enable = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", length=65535, nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="reply", type="text", length=65535, nullable=true)
     */
    private $reply;

    /**
     * @var string
     *
     * @ORM\Column(name="person", type="string", length=255, nullable=true)
     */
    private $person;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="filtres", type="text", length=65535, nullable=true)
     */
    private $filtres;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=127, nullable=true)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="actions", type="text", length=65535, nullable=true)
     */
    private $actions;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getName()
    {
        return $this->name;
    }

    public function getEnable()
    {
        return $this->enable;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getReply()
    {
        return $this->reply;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getFiltres()
    {
        return $this->filtres;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setEnable($enable)
    {
        $this->enable = $enable;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function setReply($reply)
    {
        $this->reply = $reply;
        return $this;
    }

    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function setFiltres($filtres)
    {
        $this->filtres = $filtres;
        return $this;
    }

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
