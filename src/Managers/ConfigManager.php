<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;

/**
 * Class ConfigManager
 * @package NextDom\Managers
 */
class ConfigManager
{

    const DB_CLASS_NAME = '`config`';

    /**
     * @var array Default configuration
     */
    private static $defaultConfiguration = array();
    /**
     * @var array Configuration cache
     */
    private static $cache = array();

    /**
     * Get default configuration from default.config.ini
     *
     * Configuration file is in /var/lib/nextdom/config/default.config.ini or
     * NEXTDOM_ROOT/plugins/PLUGIN_ID/core/config/PLUGIN_ID.config.ini
     *
     * @param string $pluginId Target configuration plugin or core
     *
     * @return mixed
     */
    public static function getDefaultConfiguration($pluginId = 'core')
    {
        if (!isset(self::$defaultConfiguration[$pluginId])) {
            if ($pluginId === 'core') {
                self::$defaultConfiguration[$pluginId] = parse_ini_file(NEXTDOM_DATA . '/config/default.config.ini', true);
                $customPath = sprintf("%s/custom/custom.config.ini", NEXTDOM_DATA);
                if (file_exists($customPath)) {
                    $custom = parse_ini_file($customPath, true);
                    self::$defaultConfiguration[$pluginId]['core'] = array_merge(self::$defaultConfiguration[$pluginId]['core'], $custom['core']);
                }
            } else {
                $filename = NEXTDOM_ROOT . '/plugins/' . $pluginId . '/core/config/' . $pluginId . '.config.ini';
                if (is_file($filename)) {
                    self::$defaultConfiguration[$pluginId] = parse_ini_file($filename, true);
                }
            }
        }
        if (!isset(self::$defaultConfiguration[$pluginId])) {
            self::$defaultConfiguration[$pluginId] = array();
        }
        return self::$defaultConfiguration[$pluginId];
    }

    /**
     * Save new configuration value in the database
     *
     * @param string $configKey Configuration key
     * @param string|object|array $configValue Configuration value
     * @param string $pluginId Plugin id or core
     *
     * @return boolean Always True (TODO: No return)
     * @throws \Exception
     */
    public static function save($configKey, $configValue, $pluginId = 'core')
    {
        if (is_object($configValue) || is_array($configValue)) {
            $configValue = json_encode($configValue, JSON_UNESCAPED_UNICODE);
        }
        if (isset(self::$cache[$pluginId . '::' . $configKey])) {
            unset(self::$cache[$pluginId . '::' . $configKey]);
        }
        $defaultConfiguration = self::getDefaultConfiguration($pluginId);
        // Remove configuration from the database if configValue is the same of the default configuration
        if (isset($defaultConfiguration[$pluginId][$configKey]) && $configValue == $defaultConfiguration[$pluginId][$configKey]) {
            self::remove($configKey, $pluginId);
            return true;
        }
        if ($pluginId == 'core') {
            $nextdomConfig = NextDomHelper::getConfiguration($configKey, true);
            if ($nextdomConfig != '' && $nextdomConfig == $configValue) {
                self::remove($configKey);
                return true;
            }
        }

        // Parse new value with preConfig methode
        $configClass = ($pluginId == 'core') ? 'ConfigManager' : $pluginId;
        $configMethod = 'preConfig_' . str_replace(array('::', ':'), '_', $configKey);
        if (method_exists($configClass, $configMethod)) {
            $configValue = $configClass::$configMethod($configValue);
        }
        // Save in database
        $values = array(
            'plugin' => $pluginId,
            'key' => $configKey,
            'value' => $configValue,
        );
        $sql = 'REPLACE ' . self::DB_CLASS_NAME . '
                SET `key` = :key,
                    `value` = :value,
                     `plugin` = :plugin';
        DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);

        // Execute postConfig method
        $configMethod = 'postConfig_' . str_replace(array('::', ':'), '_', $configKey);
        if (method_exists($configClass, $configMethod)) {
            $configClass::$configMethod($configValue);
        }
        return true;
    }

    /**
     * Remove key from the database
     *
     * @param string $configKey Config key to remove (* for all config from plugin)
     * @param string $pluginId Plugin id or core
     *
     * @return boolean Always True
     * @throws \Exception
     */
    public static function remove($configKey, $pluginId = 'core')
    {
        if ($configKey == "*" && $pluginId != 'core') {
            $values = array(
                'plugin' => $pluginId,
            );
            $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                    WHERE `plugin` = :plugin';
            return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
        } else {
            $values = array(
                'plugin' => $pluginId,
                'key' => $configKey,
            );
            $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                    WHERE `key` = :key
                        AND `plugin` = :plugin';
            DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
            if (isset(self::$cache[$pluginId . '::' . $configKey])) {
                unset(self::$cache[$pluginId . '::' . $configKey]);
            }
        }
        return null;
    }

    /**
     * Get configuration by key
     *
     * @param string $configKey nom de la clef dont on veut la valeur
     * @param string $pluginId Plugin id or core
     * @param string $defaultValue Default value if config key is not found
     * @param bool $forceRefresh Force to refresh the value in the cache
     *
     * @return mixed Key value
     * @throws \Exception
     */
    public static function byKey($configKey, $pluginId = 'core', $defaultValue = '', $forceRefresh = false)
    {
        if (!$forceRefresh && isset(self::$cache[$pluginId . '::' . $configKey])) {
            return self::$cache[$pluginId . '::' . $configKey];
        }
        $values = array(
            'plugin' => $pluginId,
            'key' => $configKey,
        );
        $sql = 'SELECT `value`
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `key` = :key
                AND `plugin` = :plugin';
        $value = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
        if ($value['value'] === '' || $value['value'] === null) {
            if ($defaultValue !== '') {
                self::$cache[$pluginId . '::' . $configKey] = $defaultValue;
            } else {
                $defaultConfiguration = self::getDefaultConfiguration($pluginId);
                if (isset($defaultConfiguration[$pluginId][$configKey])) {
                    self::$cache[$pluginId . '::' . $configKey] = $defaultConfiguration[$pluginId][$configKey];
                }
            }
        } else {
            self::$cache[$pluginId . '::' . $configKey] = Utils::isJson($value['value'], $value['value']);
        }
        return isset(self::$cache[$pluginId . '::' . $configKey]) ? self::$cache[$pluginId . '::' . $configKey] : '';
    }

    /**
     * Get configuration by multiple keys
     *
     * @param $configKeys
     * @param string $pluginId Plugin id or core
     * @param string $defaultValue Default value if config key is not found
     *
     * @return array Keys values
     * @throws \Exception
     */
    public static function byKeys($configKeys, $pluginId = 'core', $defaultValue = '')
    {
        if (!is_array($configKeys) || count($configKeys) == 0) {
            return array();
        }
        $values = array(
            'plugin' => $pluginId,
        );
        $keys = '(\'' . implode('\',\'', $configKeys) . '\')';
        $sql = 'SELECT `key`,`value`
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `key` IN ' . $keys . '
                    AND plugin=:plugin';
        $values = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
        $result = array();
        foreach ($values as $value) {
            $result[$value['key']] = $value['value'];
        }
        $defaultConfiguration = self::getDefaultConfiguration($pluginId);
        foreach ($configKeys as $key) {
            if (isset($result[$key])) {
                $result[$key] = Utils::isJson($result[$key], $result[$key]);
            } elseif (isset($defaultConfiguration[$pluginId][$key])) {
                $result[$key] = $defaultConfiguration[$pluginId][$key];
            } else {
                if (is_array($defaultValue)) {
                    if (isset($defaultValue[$key])) {
                        $result[$key] = $defaultValue[$key];
                    } else {
                        $result[$key] = '';
                    }
                } else {
                    $result[$key] = $defaultValue;
                }
            }
            self::$cache[$pluginId . '::' . $key] = $result[$key];
        }
        return $result;
    }

    /**
     * Find config key in database
     *
     * @param string $configKey nom de la clef dont on veut la valeur
     * @param string $pluginId Plugin id or core
     * @return mixed
     * @throws \Exception
     */
    public static function searchKey($configKey, $pluginId = 'core')
    {
        $values = array(
            'plugin' => $pluginId,
            'key' => '%' . $configKey . '%',
        );
        $sql = 'SELECT *
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `key` LIKE :key
                AND `plugin`= :plugin';
        $results = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
        foreach ($results as &$result) {
            $result['value'] = Utils::isJson($result['value'], $result['value']);
        }
        return $results;
    }

    /**
     * Generate Key with letters and numbers
     *
     * @param int $nbCharacters Number of characters of the key
     *
     * @return string Key with $nbCharacters
     *
     * @throws \Exception
     */
    public static function genKey($nbCharacters = 32)
    {
        $key = '';
        $availableCharacters = "abcdefghijklmnpqrstuvwxy1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for ($i = 0; $i < $nbCharacters; $i++) {
            if (function_exists('random_int')) {
                $key .= $availableCharacters[random_int(0, strlen($availableCharacters) - 1)];
            } else {
                $key .= $availableCharacters[rand(0, strlen($availableCharacters) - 1)];
            }
        }
        return $key;
    }

    /**
     * Get enabled plugins
     *
     * @deprecated Use getEnabledPlugins
     *
     * @return array List of enabled plugins
     * @throws \Exception
     */
    public static function getPluginEnable()
    {
        trigger_error('The function getEnabledPlugins is deprecated, use getEnabledPlugins', E_USER_DEPRECATED);
        return self::getEnabledPlugins();
    }

    /**
     * Get enabled plugins
     *
     * @return array List of enabled plugins
     * @throws \Exception
     */
    public static function getEnabledPlugins()
    {
        $sql = 'SELECT `value`,`plugin`
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `key` = \'active\'';
        $values = DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL);
        $result = array();
        foreach ($values as $value) {
            $result[$value['plugin']] = $value['value'];
        }
        return $result;
    }

    /**
     * Get log level for all plugins
     *
     * @return array Log level of all plugins
     * @throws \Exception
     */
    public static function getLogLevelPlugin()
    {
        $sql = 'SELECT `value`,`key`
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `key` LIKE \'log::level::%\'';
        $values = DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL);
        $return = array();
        foreach ($values as $value) {
            $return[$value['key']] = Utils::isJson($value['value'], $value['value']);
        }
        return $return;
    }

    /**
     * Method called on core::allowDns config change
     *
     * @param mixed $newValue New value of core::allowDns
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function postConfig_market_allowDNS($newValue)
    {
        if ($newValue == 1) {
            if (!NetworkHelper::dnsRun()) {
                NetworkHelper::dnsStart();
            }
        } else {
            if (NetworkHelper::dnsRun()) {
                NetworkHelper::dnsStop();
            }
        }
    }

    /**
     * Method called on core::market_password config change (hash the password)
     *
     * @param mixed $newValue New market password
     *
     * @return string Password hash
     */
    public static function preConfig_market_password($newValue)
    {
        if (!Utils::isSha1($newValue)) {
            return sha1($newValue);
        }
        return $newValue;
    }
}
