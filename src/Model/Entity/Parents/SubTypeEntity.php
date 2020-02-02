<?php

namespace NextDom\Model\Entity\Parents;

trait SubTypeEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="subtype", type="string", length=127, nullable=true)
     */
    protected $subtype;

    /**
     * @return string
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * @param $_subtype
     * @return $this
     */
    public function setSubtype($_subtype)
    {
        $this->updateChangeState($this->subtype, $_subtype);
        $this->subtype = $_subtype;
        return $this;
    }

    /**
     * Test sub type of the command
     *
     * @param string $cmdSubType Subype to test
     *
     * @return bool True on good type
     */
    public function isSubType(string $cmdSubType)
    {
        return $this->subtype === $cmdSubType;
    }
}