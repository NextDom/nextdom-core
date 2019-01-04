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
require_once NEXTDOM_ROOT.'/core/php/core.inc.php';

use NextDom\Managers\ConfigManager;

/**
 * Jeedom config class interface to ConfigManager
 */
class config {
    public static function getDefaultConfiguration($_plugin = 'core') {
        return ConfigManager::getDefaultConfiguration($_plugin);
    }

    public static function save($_key, $_value, $_plugin = 'core') {
        return ConfigManager::save($_key, $_value, $_plugin);
    }

    public static function remove($_key, $_plugin = 'core') {
        return ConfigManager::remove($_key, $_plugin);
    }

    public static function byKey($_key, $_plugin = 'core', $_default = '', $_forceFresh = false) {
        return ConfigManager::byKey($_key, $_plugin, $_default, $_forceFresh);
    }

    public static function byKeys($_keys, $_plugin = 'core', $_default = '') {
        return ConfigManager::byKeys($_keys, $_plugin, $_default);
    }

    public static function searchKey($_key, $_plugin = 'core') {
        return ConfigManager::searchKey($_key, $_plugin);
    }

    public static function genKey($_car = 32) {
        return ConfigManager::genKey($_car);
    }

    public static function getPluginEnable() {
        return ConfigManager::getEnabledPlugins();
    }

    public static function getLogLevelPlugin() {
        return ConfigManager::getLogLevelPlugin();
    }

    public static function postConfig_market_allowDNS($_value) {
        ConfigManager::postConfig_market_allowDNS($_value);
    }

    public static function preConfig_market_password($_value) {
        return ConfigManager::preConfig_market_password($_value);
    }
}
