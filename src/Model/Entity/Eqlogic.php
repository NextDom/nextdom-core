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

use Doctrine\ORM\Mapping as ORM;

/**
 * Eqlogic
 *
 * @ORM\Table(name="eqLogic", uniqueConstraints={
 *  @ORM\UniqueConstraint(name="unique", columns={"name", "object_id"})}, indexes={
 *  @ORM\Index(name="eqTypeName", columns={"eqType_name"}), 
 *  @ORM\Index(name="name", columns={"name"}), @ORM\Index(name="logical_id", columns={"logicalId"}), 
 *  @ORM\Index(name="generic_type", columns={"generic_type"}), 
 *  @ORM\Index(name="logica_id_eqTypeName", columns={"logicalId", "eqType_name"}), 
 *  @ORM\Index(name="object_id", columns={"object_id"}), 
 *  @ORM\Index(name="timeout", columns={"timeout"}),
 *  @ORM\Index(name="eqReal_id", columns={"eqReal_id"}), 
 *  @ORM\Index(name="tags", columns={"tags"})
 * })
 * @ORM\Entity
 */
class Eqlogic
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="generic_type", type="string", length=255, nullable=true)
     */
    private $genericType;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    private $logicalid;

    /**
     * @var string
     *
     * @ORM\Column(name="eqType_name", type="string", length=127, nullable=false)
     */
    private $eqtypeName;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    private $isvisible;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isEnable", type="boolean", nullable=true)
     */
    private $isenable;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="text", length=65535, nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    private $timeout;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="text", length=65535, nullable=true)
     */
    private $category;

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
    private $order = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="string", length=255, nullable=true)
     */
    private $tags;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\Eqreal
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Eqreal")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eqReal_id", referencedColumnName="id")
     * })
     */
    private $eqreal;

    /**
     * @var \NextDom\Model\Entity\Object
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    private $object;

    public function getName()
    {
        return $this->name;
    }

    public function getGenericType()
    {
        return $this->genericType;
    }

    public function getLogicalid()
    {
        return $this->logicalid;
    }

    public function getEqtypeName()
    {
        return $this->eqtypeName;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getIsvisible()
    {
        return $this->isvisible;
    }

    public function getIsenable()
    {
        return $this->isenable;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEqreal(): \NextDom\Model\Entity\Eqreal
    {
        return $this->eqreal;
    }

    public function getObject(): \NextDom\Model\Entity\Object
    {
        return $this->object;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setGenericType($genericType)
    {
        $this->genericType = $genericType;
        return $this;
    }

    public function setLogicalid($logicalid)
    {
        $this->logicalid = $logicalid;
        return $this;
    }

    public function setEqtypeName($eqtypeName)
    {
        $this->eqtypeName = $eqtypeName;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setIsvisible($isvisible)
    {
        $this->isvisible = $isvisible;
        return $this;
    }

    public function setIsenable($isenable)
    {
        $this->isenable = $isenable;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setCategory($category)
    {
        $this->category = $category;
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

    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setEqreal(\NextDom\Model\Entity\Eqreal $eqreal)
    {
        $this->eqreal = $eqreal;
        return $this;
    }

    public function setObject(\NextDom\Model\Entity\Object $object)
    {
        $this->object = $object;
        return $this;
    }

}
