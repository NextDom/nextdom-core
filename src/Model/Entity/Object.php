<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Object
 *
 * @ORM\Table(name="object", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})}, indexes={@ORM\Index(name="fk_object_object1_idx1", columns={"father_id"}), @ORM\Index(name="position", columns={"position"})})
 * @ORM\Entity
 */
class Object
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    private $isvisible;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    private $display;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\Object
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="father_id", referencedColumnName="id")
     * })
     */
    private $father;

    public function getName()
    {
        return $this->name;
    }

    public function getIsvisible()
    {
        return $this->isvisible;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFather(): \NextDom\Model\Entity\Object
    {
        return $this->father;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIsvisible($isvisible)
    {
        $this->isvisible = $isvisible;
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setFather(\NextDom\Model\Entity\Object $father)
    {
        $this->father = $father;
        return $this;
    }

}
