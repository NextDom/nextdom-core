<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table(name="message", indexes={@ORM\Index(name="plugin_logicalID", columns={"plugin", "logicalId"})})
 * @ORM\Entity
 */
class Message
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    private $logicalid;

    /**
     * @var string
     *
     * @ORM\Column(name="plugin", type="string", length=127, nullable=false)
     */
    private $plugin;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="text", length=65535, nullable=true)
     */
    private $action;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getLogicalid()
    {
        return $this->logicalid;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    public function setLogicalid($logicalid)
    {
        $this->logicalid = $logicalid;
        return $this;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
