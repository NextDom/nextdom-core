<?php

namespace NextDom\Model\Entity\Parents;

trait PositionEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $_position
     * @return $this
     */
    public function setPosition($_position)
    {
        $this->updateChangeState($this->position, $_position);
        $this->position = $_position;
        return $this;
    }
}