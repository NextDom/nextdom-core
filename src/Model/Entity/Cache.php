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

use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;

class Cache
{
    protected $key;
    protected $value = null;
    protected $lifetime = 0;
    protected $datetime;
    protected $options = null;

    public function save()
    {
        $this->setDatetime(date('Y-m-d H:i:s'));
        if ($this->getLifetime() == 0) {
            return CacheManager::getCache()->save($this->getKey(), $this);
        } else {
            return CacheManager::getCache()->save($this->getKey(), $this, $this->getLifetime());
        }
    }

    public function remove()
    {
        try {
            CacheManager::getCache()->delete($this->getKey());
        } catch (\Exception $e) {

        }
    }

    public function hasExpired()
    {
        return true;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getValue($_default = '')
    {
        return (empty($this->value) || (is_string($this->value) && empty(trim($this->value)))) ? $_default : $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getLifetime()
    {
        return $this->lifetime;
    }

    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
        return $this;
    }

    public function getOptions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->options, $_key, $_default);
    }

    public function setOptions($_key, $_value = null)
    {
        $this->options = Utils::setJsonAttr($this->options, $_key, $_value);
        return $this;
    }

    public function setOptionsFromJson($options)
    {
        $this->options = json_encode($options, JSON_UNESCAPED_UNICODE);
    }
}
