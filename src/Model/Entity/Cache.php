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

use NextDom\Enums\DateFormat;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;

/**
 * Class Cache
 * @package NextDom\Model\Entity
 */
class Cache
{
    protected $key;
    protected $value = null;
    protected $lifetime = 0;
    protected $datetime;
    protected $options = null;

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        $this->setDatetime(date(DateFormat::FULL));
        if ($this->getLifetime() == 0) {
            return CacheManager::getCache()->save($this->getKey(), $this);
        } else {
            return CacheManager::getCache()->save($this->getKey(), $this, $this->getLifetime());
        }
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param $lifetime
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function remove()
    {
        try {
            CacheManager::getCache()->delete($this->getKey());
        } catch (\Exception $e) {

        }
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        return true;
    }

    /**
     * @param string $_default
     * @return null|string
     */
    public function getValue($_default = '')
    {
        return ($this->value === null || (is_string($this->value) && trim($this->value) === '')) ? $_default : $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param $datetime
     * @return $this
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
        return $this;
    }

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
     * @param null $_value
     * @return $this
     */
    public function setOptions($_key, $_value = null)
    {
        $this->options = Utils::setJsonAttr($this->options, $_key, $_value);
        return $this;
    }

    /**
     * @param $options
     */
    public function setOptionsFromJson($options)
    {
        $this->options = json_encode($options, JSON_UNESCAPED_UNICODE);
    }
}
