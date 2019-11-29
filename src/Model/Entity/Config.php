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
 * Config object
 * Used for store core configuration
 *
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
{

    /**
     * @var string Value to store
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=true)
     */
    protected $value;

    /**
     * @var string Key for storage
     *
     * @ORM\Column(name="key", type="string", length=255)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $key;

    /**
     * @var string Linked plugin or 'core'
     *
     * @ORM\Column(name="plugin", type="string", length=127)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $plugin;

    /**
     * Get the value of the config
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value for the config
     *
     * @param $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the key of the config
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set the key of the config
     *
     * @param $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Get linked plugin
     *
     * @return string
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set linked plugin
     *
     * @param $plugin
     *
     * @return $this
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

}
