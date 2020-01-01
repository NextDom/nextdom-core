<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\Utils;

trait OptionsEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getOptions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->options, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setOptions($_key, $_value)
    {
        $options = Utils::setJsonAttr($this->options, $_key, $_value);
        $this->updateChangeState($this->options, $options);
        $this->options = $options;
        return $this;
    }
}