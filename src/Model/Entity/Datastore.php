<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Datastore
 *
 * @ORM\Table(name="dataStore", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE", columns={"type", "link_id", "key"})})
 * @ORM\Entity
 */
class Datastore
{

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=false)
     */
    private $linkId;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=127, nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=true)
     */
    private $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getType()
    {
        return $this->type;
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
