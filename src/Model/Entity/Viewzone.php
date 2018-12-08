<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Viewzone
 *
 * @ORM\Table(name="viewZone", indexes={@ORM\Index(name="fk_zone_view1", columns={"view_id"})})
 * @ORM\Entity
 */
class Viewzone
{

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    private $name;

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\View
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\View")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     * })
     */
    private $view;

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getView(): \NextDom\Model\Entity\View
    {
        return $this->view;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setView(\NextDom\Model\Entity\View $view)
    {
        $this->view = $view;
        return $this;
    }

}
