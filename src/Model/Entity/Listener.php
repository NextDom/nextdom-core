<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

/**
 * Listener
 *
 * @ORM\Table(name="listener", indexes={@ORM\Index(name="event", columns={"event"})})
 * @ORM\Entity
 */
class Listener
{

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=127, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=127, nullable=true)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255, nullable=true)
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="option", type="text", length=65535, nullable=true)
     */
    private $option;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getClass()
    {
        return $this->class;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
