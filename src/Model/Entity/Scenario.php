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

use Doctrine\ORM\Mapping as ORM;

/**
 * Scenario
 *
 * @ORM\Table(name="scenario", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"group", "object_id", "name"})}, indexes={@ORM\Index(name="group", columns={"group"}), @ORM\Index(name="fk_scenario_object1_idx", columns={"object_id"}), @ORM\Index(name="trigger", columns={"trigger"}), @ORM\Index(name="mode", columns={"mode"}), @ORM\Index(name="modeTriger", columns={"mode", "trigger"})})
 * @ORM\Entity
 */
class Scenario
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=127, nullable=true)
     */
    private $group;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isActive", type="boolean", nullable=true)
     */
    private $isactive = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=127, nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="text", length=65535, nullable=true)
     */
    private $schedule;

    /**
     * @var string
     *
     * @ORM\Column(name="scenarioElement", type="text", length=65535, nullable=true)
     */
    private $scenarioelement;

    /**
     * @var string
     *
     * @ORM\Column(name="trigger", type="string", length=255, nullable=true)
     */
    private $trigger;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    private $timeout;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    private $isvisible = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    private $display;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type = 'expert';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\Object
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    private $object;

    public function getName()
    {
        return $this->name;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getIsactive()
    {
        return $this->isactive;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }

    public function getScenarioelement()
    {
        return $this->scenarioelement;
    }

    public function getTrigger()
    {
        return $this->trigger;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getIsvisible()
    {
        return $this->isvisible;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObject(): \NextDom\Model\Entity\Object
    {
        return $this->object;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setIsactive($isactive)
    {
        $this->isactive = $isactive;
        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function setScenarioelement($scenarioelement)
    {
        $this->scenarioelement = $scenarioelement;
        return $this;
    }

    public function setTrigger($trigger)
    {
        $this->trigger = $trigger;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setIsvisible($isvisible)
    {
        $this->isvisible = $isvisible;
        return $this;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setObject(\NextDom\Model\Entity\Object $object)
    {
        $this->object = $object;
        return $this;
    }

}
