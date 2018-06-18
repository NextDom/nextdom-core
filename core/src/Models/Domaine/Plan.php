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


class Plan
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $planHeaderId;

    /**
     * @var string
     */
    private $linkType;

    /**
     * @var int
     */
    private $linkId;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $display;

    /**
     * @var string
     */
    private $css;

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
     * @return Plan
     */
    public function setId(int $id): Plan
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlanHeaderId(): int
    {
        return $this->planHeaderId;
    }

    /**
     * @param int $planHeaderId
     * @return Plan
     */
    public function setPlanHeaderId(int $planHeaderId): Plan
    {
        $this->planHeaderId = $planHeaderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkType(): string
    {
        return $this->linkType;
    }

    /**
     * @param string $linkType
     * @return Plan
     */
    public function setLinkType($linkType): Plan
    {
        $this->linkType = $linkType;
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
     * @return Plan
     */
    public function setLinkId($linkId): Plan
    {
        $this->linkId = $linkId;
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
     * @return Plan
     */
    public function setPosition($position): Plan
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplay(): string
    {
        return $this->display;
    }

    /**
     * @param string $display
     * @return Plan
     */
    public function setDisplay($display): Plan
    {
        $this->display = $display;
        return $this;
    }

    /**
     * @return string
     */
    public function getCss(): string
    {
        return $this->css;
    }

    /**
     * @param string $css
     * @return Plan
     */
    public function setCss($css): Plan
    {
        $this->css = $css;
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
     * @return Plan
     */
    public function setConfiguration($configuration): Plan
    {
        $this->configuration = $configuration;
        return $this;
    }


}