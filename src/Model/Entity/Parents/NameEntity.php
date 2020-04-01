<?php

namespace NextDom\Model\Entity\Parents;

trait NameEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    protected $name;

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
        $this->updateChangeState($this->name, $_name);
        $this->name = $_name;
        return $this;
    }
}