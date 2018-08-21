<?php
/* This file is part of NextDom.
 *
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
 
namespace NextDom\Models\Domain;

class Plan3dHeader
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
     * @var int
     */
    private $configuration;
 
    /**
     * 
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * 
     * @param int $id
     * @return Plan3dHeader
     */
    public function setId(int $id): Plan3dHeader
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return Plan3dHeader
     */
    public function setName($name): Plan3dHeader
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 
     * @param string $configuration
     * @return Plan3dHeader
     */
    public function setConfiguration($configuration): Plan3dHeader
    {
        $this->configuration = $configuration;
        return $this;
    }

}
