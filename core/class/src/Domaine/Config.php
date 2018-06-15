<?php

/* This file is part of NextDom.
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\src\Domaine;


class Config
{
    /**
     *
     * @var string
     */
    private $plugin;
    
    /**
     *
     * @var string
     */
    private $key;
    
    /**
     *
     * @var string
     */
    private $value;
    
    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setPlugin( string $plugin) : Config
    {
        $this->plugin = $plugin;
        return $this;
    }

    public function setKey( string $key) : Config
    {
        $this->key = $key;
        return $this;
    }

    public function setValue($value) : Config
    {
        $this->value = $value;
        return $this;
    }

    
}
