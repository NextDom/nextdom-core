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


class Scenario
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
     * @var string
     */
    private $group = '';

    /**
     * @var int
     */
    private $isActive;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $schedule;

    /**
     * @var string
     */
    private $scenarioElement;

    /**
     * @var string
     */
    private $trigger;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var int
     */
    private $isVisible;

    /**
     * @var string
     */
    private $display;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $type;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Scenario
     */
    public function setId(int $id): Scenario
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
     * @return Scenario
     */
    public function setName($name): Scenario
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return Scenario
     */
    public function setGroup($group): Scenario
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return int
     */
    public function getisActive(): int
    {
        return $this->isActive;
    }

    /**
     * @param int $isActive
     * @return Scenario
     */
    public function setIsActive($isActive): Scenario
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return Scenario
     */
    public function setMode($mode): Scenario
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchedule(): string
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     * @return Scenario
     */
    public function setSchedule($schedule): Scenario
    {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * @return string
     */
    public function getScenarioElement(): string
    {
        return $this->scenarioElement;
    }

    /**
     * @param string $scenarioElement
     * @return Scenario
     */
    public function setScenarioElement($scenarioElement): Scenario
    {
        $this->scenarioElement = $scenarioElement;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * @param string $trigger
     * @return Scenario
     */
    public function setTrigger($trigger): Scenario
    {
        $this->trigger = $trigger;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Scenario
     */
    public function setTimeout($timeout): Scenario
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     * @return Scenario
     */
    public function setObjectId($objectId): Scenario
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsVisible(): int
    {
        return $this->isVisible;
    }

    /**
     * @param int $isVisible
     * @return Scenario
     */
    public function setIsVisible($isVisible): Scenario
    {
        $this->isVisible = $isVisible;
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
     * @return Scenario
     */
    public function setDisplay($display): Scenario
    {
        $this->display = $display;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Scenario
     */
    public function setDescription($description): Scenario
    {
        $this->description = $description;
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
     * @return Scenario
     */
    public function setConfiguration($configuration): Scenario
    {
        $this->configuration = $configuration;
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
     * @return Scenario
     */
    public function setType($type): Scenario
    {
        $this->type = $type;
        return $this;
    }

}