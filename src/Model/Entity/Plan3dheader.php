<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plan3dheader
 *
 * @ORM\Table(name="plan3dHeader")
 * @ORM\Entity
 */
class Plan3dheader
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getName()
    {
        return $this->name;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
