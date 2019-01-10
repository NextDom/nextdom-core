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

use NextDom\Model\Entity\EqLogic;
/**
 * Cmd
 *
 * @ORM\Table(name="cmd", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique", columns={"eqLogic_id", "name"})}, indexes={
 *          @ORM\Index(name="isHistorized", columns={"isHistorized"}), 
 *          @ORM\Index(name="type", columns={"type"}), 
 *          @ORM\Index(name="name", columns={"name"}), 
 *          @ORM\Index(name="subtype", columns={"subType"}), 
 *          @ORM\Index(name="eqLogic_id", columns={"eqLogic_id"}), 
 *          @ORM\Index(name="value", columns={"value"}), 
 *          @ORM\Index(name="order", columns={"order"}), 
 *          @ORM\Index(name="logicalID", columns={"logicalId"}), 
 *          @ORM\Index(name="logicalId_eqLogicID", columns={"eqLogic_id", "logicalId"}), 
 *          @ORM\Index(name="genericType_eqLogicID", columns={"eqLogic_id", "generic_type"})
 *      })
 * @ORM\Entity
 */
class Cmd
{

    /**
     * @var string
     *
     * @ORM\Column(name="eqType", type="string", length=127, nullable=true)
     */
    private $eqtype;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    private $logicalid;

    /**
     * @var string
     *
     * @ORM\Column(name="generic_type", type="string", length=255, nullable=true)
     */
    private $genericType;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="text", length=65535, nullable=true)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="isHistorized", type="string", length=45, nullable=false)
     */
    private $ishistorized;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="subType", type="string", length=45, nullable=true)
     */
    private $subtype;

    /**
     * @var string
     *
     * @ORM\Column(name="unite", type="string", length=45, nullable=true)
     */
    private $unite;

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    private $display;

    /**
     * @var integer
     *
     * @ORM\Column(name="isVisible", type="integer", nullable=true)
     */
    private $isvisible = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="html", type="text", length=16777215, nullable=true)
     */
    private $html;

    /**
     * @var string
     *
     * @ORM\Column(name="alert", type="text", length=65535, nullable=true)
     */
    private $alert;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var EqLogic
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Eqlogic")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eqLogic_id", referencedColumnName="id")
     * })
     */
    private $eqLogic;

    public function getEqtype()
    {
        return $this->eqtype;
    }

    public function getLogicalid()
    {
        return $this->logicalid;
    }

    public function getGenericType()
    {
        return $this->genericType;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getIshistorized()
    {
        return $this->ishistorized;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubtype()
    {
        return $this->subtype;
    }

    public function getUnite()
    {
        return $this->unite;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getIsvisible()
    {
        return $this->isvisible;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function getAlert()
    {
        return $this->alert;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return EqLogic
     */
    public function getEqLogic(): EqLogic
    {
        return $this->eqLogic;
    }

    public function setEqtype($eqtype)
    {
        $this->eqtype = $eqtype;
        return $this;
    }

    public function setLogicalid($logicalid)
    {
        $this->logicalid = $logicalid;
        return $this;
    }

    public function setGenericType($genericType)
    {
        $this->genericType = $genericType;
        return $this;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
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

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setIshistorized($ishistorized)
    {
        $this->ishistorized = $ishistorized;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
        return $this;
    }

    public function setUnite($unite)
    {
        $this->unite = $unite;
        return $this;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    public function setIsvisible($isvisible)
    {
        $this->isvisible = $isvisible;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    public function setAlert($alert)
    {
        $this->alert = $alert;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param EqLogic $eqLogic
     * @return $this
     */
    public function setEqLogic(EqLogic $eqLogic)
    {
        $this->eqLogic = $eqLogic;
        return $this;
    }

}
