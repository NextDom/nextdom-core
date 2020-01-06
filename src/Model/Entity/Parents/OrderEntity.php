<?php

namespace NextDom\Model\Entity\Parents;

trait OrderEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

    /**
     * @return int
     */
    public function getOrder()
    {
        if (empty($this->order)) {
            return 0;
        }
        return $this->order;
    }

    /**
     * @param $_order
     * @return $this
     */
    public function setOrder($_order)
    {
        $this->updateChangeState($this->order, $_order);
        $this->order = $_order;
        return $this;
    }
}