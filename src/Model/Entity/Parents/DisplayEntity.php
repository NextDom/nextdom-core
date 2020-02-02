<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\Utils;

trait DisplayEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;

    /**
     * Get display information by key
     *
     * @param string $key Name of the information
     * @param mixed $default Value of this information
     *
     * @return mixed Value of the asked information or $default
     */
    public function getDisplay(string $key = '', $default = '')
    {
        return Utils::getJsonAttr($this->display, $key, $default);
    }

    /**
     * Set display information by key
     *
     * @param string $key Name of the information
     * @param mixed $value value of this information
     *
     * @return $this
     */
    public function setDisplay(string $key, $value)
    {
        $display = Utils::setJsonAttr($this->display, $key, $value);
        $this->updateChangeState($this->display, $display);
        $this->display = $display;
        return $this;
    }

}