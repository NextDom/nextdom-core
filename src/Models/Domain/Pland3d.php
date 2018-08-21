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

class Plan3d
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
    private $plan3dHeaderId;

    /**
     *
     * @var string
     */
    private $linkType;

    /**
     *
     * @var string
     */
    private $linkId;

    /**
     *
     * @var string
     */
    private $position;

    /**
     *
     * @var string
     */
    private $display;

    /**
     *
     * @var string
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
     * @return int
     */
    public function getPlan3dHeaderId(): int
    {
        return $this->plan3dHeaderId;
    }

    /**
     * 
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * 
     * @return string
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * 
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * 
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
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
     * @return Plan3d
     */
    public function setId(int $id): Plan3d
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return Plan3d
     */
    public function setName($name): Plan3d
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 
     * @param int $plan3dHeaderId
     * @return Plan3d
     */
    public function setPlan3dHeaderId(int $plan3dHeaderId): Plan3d
    {
        $this->plan3dHeaderId = $plan3dHeaderId;
        return $this;
    }

    /**
     * 
     * @param string $linkType
     * @return Plan3d  
     */
    public function setLinkType($linkType): Plan3d
    {
        $this->linkType = $linkType;
        return $this;
    }

    /**
     * 
     * @param string $linkId
     * @return Plan3d  
     */
    public function setLinkId($linkId): Plan3d
    {
        $this->linkId = $linkId;
        return $this;
    }

    /**
     * 
     * @param string $position
     * @return Plan3d
     */
    public function setPosition($position): Plan3d
    {
        $this->position = $position;
        return $this;
    }

    /**
     * 
     * @param string $display
     * @return Plan3d
     */
    public function setDisplay($display): Plan3d
    {
        $this->display = $display;
        return $this;
    }

    /**
     * 
     * @param string $configuration
     * @return Plan3d
     */
    public function setConfiguration($configuration): Plan3d
    {
        $this->configuration = $configuration;
        return $this;
    }

}
