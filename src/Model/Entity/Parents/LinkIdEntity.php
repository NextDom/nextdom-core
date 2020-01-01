<?php

namespace NextDom\Model\Entity\Parents;

trait LinkIdEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var integer
     *
     * ORM\Column(name="link_id", type="integer", nullable=true)
     */
    protected $link_id;

    /**
     * @return int
     */
    public function getLink_id()
    {
        return $this->link_id;
    }

    /**
     * @param $_link_id
     * @return $this
     */
    public function setLink_id($_link_id)
    {
        $this->updateChangeState($this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function getLinkObject()
    {
        $type = $this->getType();
        if (class_exists($type)) {
            return $type::byId($this->getLink_id());
        }
        return false;
    }
}