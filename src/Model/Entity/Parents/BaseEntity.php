<?php

namespace NextDom\Model\Entity\Parents;

abstract class BaseEntity extends BaseModel
{
    const TABLE_NAME = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->updateChangeState($this->id, $_id);
        $this->id = $_id;
        return $this;
    }
}