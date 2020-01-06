<?php

namespace NextDom\Model\Entity\Parents;

trait EnableEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    protected $enable = 1;

    /**
     * @return int
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * @param $_enable
     * @return $this
     */
    public function setEnable($_enable)
    {
        $this->updateChangeState($this->enable, $_enable);
        $this->enable = $_enable;
        return $this;
    }

    /**
     * Get bool enabled state
     *
     * @return bool True is task is enabled
     */
    public function isEnabled()
    {
        return $this->enable == 1;
    }
}