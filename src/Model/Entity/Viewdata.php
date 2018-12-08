<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Viewdata
 *
 * @ORM\Table(name="viewData", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"viewZone_id", "link_id", "type"})}, indexes={@ORM\Index(name="fk_data_zone1_idx", columns={"viewZone_id"}), @ORM\Index(name="order", columns={"order", "viewZone_id"})})
 * @ORM\Entity
 */
class Viewdata
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=true)
     */
    private $linkId;

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
     * @var \NextDom\Model\Entity\Viewzone
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Viewzone")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="viewZone_id", referencedColumnName="id")
     * })
     */
    private $viewzone;

    public function getOrder()
    {
        return $this->order;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getViewzone(): \NextDom\Model\Entity\Viewzone
    {
        return $this->viewzone;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
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

    public function setViewzone(\NextDom\Model\Entity\Viewzone $viewzone)
    {
        $this->viewzone = $viewzone;
        return $this;
    }

}
