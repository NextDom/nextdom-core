<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plan
 *
 * @ORM\Table(name="plan", indexes={@ORM\Index(name="unique", columns={"link_type", "link_id"}), @ORM\Index(name="fk_plan_planHeader1_idx", columns={"planHeader_id"})})
 * @ORM\Entity
 */
class Plan
{

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=127, nullable=true)
     */
    private $linkType;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=true)
     */
    private $linkId;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="text", length=65535, nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    private $display;

    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", length=65535, nullable=true)
     */
    private $css;

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
     * @var \NextDom\Model\Entity\Planheader
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Planheader")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="planHeader_id", referencedColumnName="id")
     * })
     */
    private $planheader;

    public function getLinkType()
    {
        return $this->linkType;
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPlanheader(): \NextDom\Model\Entity\Planheader
    {
        return $this->planheader;
    }

    public function setLinkType($linkType)
    {
        $this->linkType = $linkType;
        return $this;
    }

    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    public function setCss($css)
    {
        $this->css = $css;
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

    public function setPlanheader(\NextDom\Model\Entity\Planheader $planheader)
    {
        $this->planheader = $planheader;
        return $this;
    }

}
