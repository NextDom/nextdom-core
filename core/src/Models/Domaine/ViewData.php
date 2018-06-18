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


class ViewData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $order;

    /**
     * @var int
     */
    private $viewZoneId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $linkId;

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
     * @return ViewData
     */
    public function setId(int $id): ViewData
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return ViewData
     */
    public function setOrder($order): ViewData
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewZoneId(): int
    {
        return $this->viewZoneId;
    }

    /**
     * @param int $viewZoneId
     * @return ViewData
     */
    public function setViewZoneId(int $viewZoneId): ViewData
    {
        $this->viewZoneId = $viewZoneId;
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
     * @return ViewData
     */
    public function setType($type): ViewData
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLinkId(): int
    {
        return $this->linkId;
    }

    /**
     * @param int $linkId
     * @return ViewData
     */
    public function setLinkId($linkId): ViewData
    {
        $this->linkId = $linkId;
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
     * @return ViewData
     */
    public function setConfiguration($configuration): ViewData
    {
        $this->configuration = $configuration;
        return $this;
    }


}