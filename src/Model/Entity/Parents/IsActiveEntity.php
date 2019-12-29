<?php

namespace NextDom\Model\Entity\Parents;

trait IsActiveEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var boolean
     *
     * @ORM\Column(name="isActive", type="boolean", nullable=true)
     */
    protected $isActive = 1;

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Get active state of the scenario
     *
     * @return bool Active state
     */
    public function isActive(): bool
    {
        return $this->isActive == 1;
    }

    /**
     *
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        if ($isActive != $this->getIsActive()) {
            $this->_changeState = true;
            $this->_changed = true;
        }
        $this->isActive = $isActive;
        return $this;
    }
}