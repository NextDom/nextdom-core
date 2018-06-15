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


class InteractDef
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
    private $enable;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $reply;

    /**
     * @var string
     */
    private $person;

    /**
     * @var string
     */
    private $options;

    /**
     * @var string
     */
    private $filtres;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $actions;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return InteractDef
     */
    public function setId(int $id): InteractDef
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
     * @return InteractDef
     */
    public function setName($name): InteractDef
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getEnable(): int
    {
        return $this->enable;
    }

    /**
     * @param int $enable
     * @return InteractDef
     */
    public function setEnable($enable): InteractDef
    {
        $this->enable = $enable;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return InteractDef
     */
    public function setQuery($query): InteractDef
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getReply(): string
    {
        return $this->reply;
    }

    /**
     * @param string $reply
     * @return InteractDef
     */
    public function setReply($reply): InteractDef
    {
        $this->reply = $reply;
        return $this;
    }

    /**
     * @return string
     */
    public function getPerson(): string
    {
        return $this->person;
    }

    /**
     * @param string $person
     * @return InteractDef
     */
    public function setPerson($person): InteractDef
    {
        $this->person = $person;
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
     * @return InteractDef
     */
    public function setOptions($options): InteractDef
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getFiltres(): string
    {
        return $this->filtres;
    }

    /**
     * @param string $filtres
     * @return InteractDef
     */
    public function setFiltres($filtres): InteractDef
    {
        $this->filtres = $filtres;
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
     * @return InteractDef
     */
    public function setGroup($group): InteractDef
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getActions(): string
    {
        return $this->actions;
    }

    /**
     * @param string $actions
     * @return InteractDef
     */
    public function setActions($actions): InteractDef
    {
        $this->actions = $actions;
        return $this;
    }


}