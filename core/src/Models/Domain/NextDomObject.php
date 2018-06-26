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


class NextDomObject
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $fatherId;

    /**
     * @var int
     */
    private $isVisible = 1;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $display;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return NextDomObject
     */
    public function setId(int $id): NextDomObject
    {
        $this->id = $id;
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
     * @return NextDomObject
     */
    public function setName(string $name): NextDomObject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getFatherId(): int
    {
        return $this->fatherId;
    }

    /**
     * @param int $fatherId
     * @return NextDomObject
     */
    public function setFatherId($fatherId): NextDomObject
    {
        $this->fatherId = $fatherId;
        return $this;
    }

    /**
     * @return int
     */
    public function getisVisible(): int
    {
        return $this->isVisible;
    }

    /**
     * @param int $isVisible
     * @return NextDomObject
     */
    public function setIsVisible($isVisible): NextDomObject
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return NextDomObject
     */
    public function setPosition($position): NextDomObject
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
     * @return NextDomObject
     */
    public function setConfiguration($configuration): NextDomObject
    {
        $this->configuration = $configuration;
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
     * @return NextDomObject
     */
    public function setDisplay($display): NextDomObject
    {
        $this->display = $display;
        return $this;
    }


}