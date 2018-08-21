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


class ScenarioSubElement
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
    private $scenarioElementId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $subtype;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $options;

    /**
     * @var string
     */
    private $log;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ScenarioSubElement
     */
    public function setId(int $id): ScenarioSubElement
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
     * @return ScenarioSubElement
     */
    public function setOrder($order): ScenarioSubElement
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return int
     */
    public function getScenarioElementId(): int
    {
        return $this->scenarioElementId;
    }

    /**
     * @param int $scenarioElementId
     * @return ScenarioSubElement
     */
    public function setScenarioElementId(int $scenarioElementId): ScenarioSubElement
    {
        $this->scenarioElementId = $scenarioElementId;
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
     * @return ScenarioSubElement
     */
    public function setType($type): ScenarioSubElement
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }

    /**
     * @param string $subtype
     * @return ScenarioSubElement
     */
    public function setSubtype($subtype): ScenarioSubElement
    {
        $this->subtype = $subtype;
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
     * @return ScenarioSubElement
     */
    public function setName($name): ScenarioSubElement
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOptions(): string
    {
        return $this->options;
    }

    /**
     * @param string $options
     * @return ScenarioSubElement
     */
    public function setOptions($options): ScenarioSubElement
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * @param string $log
     * @return ScenarioSubElement
     */
    public function setLog($log): ScenarioSubElement
    {
        $this->log = $log;
        return $this;
    }


}