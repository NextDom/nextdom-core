<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * View
 *
 * @ORM\Table(name="view", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})})
 * @ORM\Entity
 */
class View
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
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    private $display;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    private $image;

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

    public function getDisplay()
    {
        return $this->display;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getImage()
    {
        return $this->image;
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

    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function setImage($image)
    {
        $this->image = $image;
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
