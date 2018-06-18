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

namespace NextDom\src\Models\Domaine;


class ViewZone
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $viewId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ViewZone
     */
    public function setId(int $id): ViewZone
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewId(): int
    {
        return $this->viewId;
    }

    /**
     * @param int $viewId
     * @return ViewZone
     */
    public function setViewId(int $viewId): ViewZone
    {
        $this->viewId = $viewId;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ViewZone
     */
    public function setType($type): ViewZone
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ViewZone
     */
    public function setName($name): ViewZone
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     * @return ViewZone
     */
    public function setPosition($position): ViewZone
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     * @return ViewZone
     */
    public function setConfiguration($configuration): ViewZone
    {
        $this->configuration = $configuration;
        return $this;
    }



}