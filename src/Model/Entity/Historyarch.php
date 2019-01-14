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
 * Historyarch
 *
 * @ORM\Table(name="historyArch", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"cmd_id", "datetime"})}, indexes={@ORM\Index(name="cmd_id_index", columns={"cmd_id"})})
 * @ORM\Entity
 */
class Historyarch
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    private $datetime;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=127, nullable=true)
     */
    private $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \NextDom\Model\Entity\Cmd
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Cmd")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmd_id", referencedColumnName="id")
     * })
     */
    private $cmd;

    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCmd(): \NextDom\Model\Entity\Cmd
    {
        return $this->cmd;
    }

    public function setDatetime(\DateTime $datetime)
    {
        $this->datetime = $datetime;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setCmd(\NextDom\Model\Entity\Cmd $cmd)
    {
        $this->cmd = $cmd;
        return $this;
    }

}
