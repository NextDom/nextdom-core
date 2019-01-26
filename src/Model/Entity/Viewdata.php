<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

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
