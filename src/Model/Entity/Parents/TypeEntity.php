<?php

namespace NextDom\Model\Entity\Parents;

trait TypeEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type;

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
        $this->updateChangeState($this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    public function isType($typeToTest)
    {
        return $this->type === $typeToTest;
    }
}