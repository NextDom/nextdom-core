<?php

/* This file is part of NextDom.
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\src\Domaine;

class EqLogic
{

    /**
     *
     * @var int
     */
    private $id;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $genericType;

    /**
     *
     * @var string
     */
    private $logicalId;

    /**
     *
     * @var int
     */
    private $objectId;

    /**
     *
     * @var string
     */
    private $eqTypeName;

    /**
     *
     * @var string
     */
    private $configuration;

    /**
     *
     * @var bool 
     */
    private $isVisible;

    /**
     *
     * @var int 
     */
    private $eqRealId;

    /**
     *
     * @var int
     */
    private $isEnable;

    /**
     *
     * @var string
     */
    private $status;

    /**
     *
     * @var int 
     */
    private $timeout;

    /**
     *
     * @var string
     */
    private $category;

    /**
     *
     * @var string
     */
    private $display;

    /**
     *
     * @var int
     */
    private $order;

    /**
     *
     * @var string
     */
    private $comment;

    public function getId() 
    {
        return $this->id;
    }

    public function getName() 
    {
        return $this->name;
    }

    public function getGenericType()
    {
        return $this->genericType;
    }

    public function getLogicalId()
    {
        return $this->logicalId;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function getEqTypeName()
    {
        return $this->eqTypeName;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getIsVisible()
    {
        return $this->isVisible;
    }

    public function getEqRealId()
    {
        return $this->eqRealId;
    }

    public function getIsEnable()
    {
        return $this->isEnable;
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

    public function setId( int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName( string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setGenericType($genericType)
    {
        $this->genericType = $genericType;
        return $this;
    }

    public function setLogicalId($logicalId)
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    public function setEqTypeName( string $eqTypeName)
    {
        $this->eqTypeName = $eqTypeName;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function setEqRealId($eqRealId)
    {
        $this->eqRealId = $eqRealId;
        return $this;
    }

    public function setIsEnable($isEnable)
    {
        $this->isEnable = $isEnable;
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

}
