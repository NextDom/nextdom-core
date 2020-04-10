<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* This file is part of NextDom Software.
 *
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

namespace NextDom\Managers\Parents;

use NextDom\Helpers\DBHelper;

/**
 * Base manager with commonts functions
 *
 * @package NextDom\Managers
 */
abstract class BaseManager
{
    /** @var string Class of the entity */
    const CLASS_NAME = '';
    /** @var string Table name of the object in database */
    const DB_CLASS_NAME = '``';
    /** @var string[] Array for buffer */
    protected static $baseSQL = null;
    /** @var string[] Array for buffer */
    protected static $prefixedBaseSQL = null;

    /**
     * Get base of Select SQL
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected static function getBaseSQL() {
        if (!isset(static::$baseSQL[static::CLASS_NAME])) {
            static::$baseSQL[static::CLASS_NAME] = "SELECT " . DBHelper::buildField(static::CLASS_NAME) . " FROM " . static::DB_CLASS_NAME . " ";
        }
        return static::$baseSQL[static::CLASS_NAME];
    }

    /**
     * Get prefixed base of Select SQL
     *
     * @param string $prefix
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected static function getPrefixedBaseSQL($prefix) {
        if (!isset(static::$prefixedBaseSQL[static::CLASS_NAME])) {
            static::$prefixedBaseSQL[static::CLASS_NAME] = "SELECT " . DBHelper::buildField(static::CLASS_NAME, $prefix) . " FROM " . static::DB_CLASS_NAME . " $prefix ";
        }
        return static::$prefixedBaseSQL[static::CLASS_NAME];
    }
}