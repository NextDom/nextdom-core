<?php

namespace NextDom\Model\Entity\Parents;

trait IsVisibleEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    protected $isVisible = 1;

    /**
     *
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getIsVisible($defaultValue = 0)
    {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $defaultValue;
        }
        return $this->isVisible;
    }

    public function isVisible()
    {
        return intval($this->isVisible) == 1;
    }

    /**
     * @param $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
        return $this;
    }
}