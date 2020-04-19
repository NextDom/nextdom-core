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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\CacheManager;

class cache extends \NextDom\Model\Entity\Cache {
    public static function getFolder() {
        return CacheManager::getFolder();
    }

    public static function set($_key, $_value, $_lifetime = 0, $_options = null) {
        return CacheManager::set($_key, $_value, $_lifetime, $_options);
    }

    public static function delete($_key) {
        CacheManager::delete($_key);
    }

    public static function stats($_details = false) {
        return CacheManager::stats($_details);
    }

    public static function getCache() {
        return CacheManager::getCache();
    }

    public static function byKey($_key) {
        return CacheManager::byKey($_key);
    }

    public static function exist($_key) {
        return CacheManager::exists($_key);
    }

    public static function flush() {
        CacheManager::flush();
    }

    public static function search() {
        return CacheManager::search();
    }

    public static function persist() {
        CacheManager::persist();
    }

    public static function isPersistOk() {
        return CacheManager::isPersistOk();
    }

    public static function restore() {
        CacheManager::restore();
    }

    public static function clean() {
        CacheManager::clean();
    }
    
    public static function flushWidget() {
        CacheManager::flushWidget();
    }
}
