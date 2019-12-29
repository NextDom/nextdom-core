<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\Utils;

trait AccessCodeConfigurationEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    use ConfigurationEntity {
        setConfiguration as basicSetConfiguration;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setConfiguration($_key, $_value)
    {
        if ($_key == 'accessCode' && $_value != '' && !Utils::isSha512($_value)) {
            $_value = Utils::sha512($_value);
        }
        return $this->basicSetConfiguration($_key, $_value);
    }
}