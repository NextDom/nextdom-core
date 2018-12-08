<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
{

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=255)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="plugin", type="string", length=127)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $plugin;

    public function getValue()
    {
        return $this->value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

}
