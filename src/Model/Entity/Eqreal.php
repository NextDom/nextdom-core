<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Eqreal
 *
 * @ORM\Table(name="eqReal", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})}, indexes={@ORM\Index(name="logicalId", columns={"logicalId"}), @ORM\Index(name="type", columns={"type"}), @ORM\Index(name="logicalId_Type", columns={"logicalId", "type"}), @ORM\Index(name="name", columns={"name"})})
 * @ORM\Entity
 */
class Eqreal
{

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=45, nullable=true)
     */
    private $logicalid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="cat", type="string", length=45, nullable=true)
     */
    private $cat;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getLogicalid()
    {
        return $this->logicalid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getCat()
    {
        return $this->cat;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLogicalid($logicalid)
    {
        $this->logicalid = $logicalid;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setCat($cat)
    {
        $this->cat = $cat;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
