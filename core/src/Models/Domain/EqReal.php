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

namespace NextDom\src\Models\Domaine;

class EqReal
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
    private $logicalId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $cat;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EqReal
     */
    public function setId( int $id) : EqReal
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param string $logicalId
     * @return EqReal
     */
    public function setLogicalId($logicalId) : EqReal
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EqReal
     */
    public function setName($name) : EqReal
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return EqReal
     */
    public function setType($type) : EqReal
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     * @return EqReal
     */
    public function setConfiguration(string $configuration) : EqReal
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getCat()
    {
        return $this->cat;
    }

    /**
     * @param string $cat
     * @return EqReal
     */
    public function setCat($cat) : EqReal
    {
        $this->cat = $cat;
        return $this;
    }


}
