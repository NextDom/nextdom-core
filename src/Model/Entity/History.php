<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 *
 * @ORM\Table(name="history", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"datetime", "cmd_id"})}, indexes={@ORM\Index(name="fk_history5min_commands1_idx", columns={"cmd_id"})})
 * @ORM\Entity
 */
class History
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

