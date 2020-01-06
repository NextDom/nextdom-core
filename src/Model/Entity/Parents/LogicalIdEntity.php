<?php

namespace NextDom\Model\Entity\Parents;

trait LogicalIdEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param $_logicalId
     * @return $this
     */
    public function setLogicalId($_logicalId)
    {
        $this->updateChangeState($this->logicalId, $_logicalId);
        $this->logicalId = $_logicalId;
        return $this;
    }
}