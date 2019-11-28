<?php
/* This file is part of NextDom.
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

namespace NextDom\Helpers;

use NextDom\Enums\ApiMode;
use NextDom\Enums\LogTarget;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;

/**
 * Class Api
 * @package NextDom\Helpers
 */
class Api
{

    /**
     * Get API access with key
     *
     * @param string $defaultApiKey
     * @param string $plugin
     * @return bool
     * @throws \Exception
     */
    public static function apiAccess(string $defaultApiKey = '', string $plugin = 'core')
    {
        $defaultApiKey = trim($defaultApiKey);
        if ($defaultApiKey == '') {
            return false;
        }
        if ($plugin != 'core' && self::apiAccess($defaultApiKey)) {
            return true;
        }
        if ($plugin != 'core' && $plugin != 'proapi' && !self::apiModeResult(ConfigManager::byKey('api::' . $plugin . '::mode', 'core', 'enable'))) {
            return false;
        }
        $apikey = self::getApiKey($plugin);
        if ($defaultApiKey != '' && $apikey == $defaultApiKey) {
            return true;
        }
        $user = UserManager::byHash($defaultApiKey);
        if (is_object($user)) {
            if ($user->getOptions('localOnly', 0) == 1 && !self::apiModeResult('whiteip')) {
                return false;
            }
            GLOBAL $_USER_GLOBAL;
            $_USER_GLOBAL = $user;
            LogHelper::addInfo(LogTarget::CONNECTION, __('core.api-connection') . $user->getLogin());
            return true;
        }
        return false;
    }

    /**
     * Test if api are are enabled
     *
     * @param string $mode
     *
     * @return bool
     * @throws \Exception
     */
    public static function apiModeResult(string $mode = ApiMode::API_ENABLE): bool
    {
        $result = true;
        switch ($mode) {
            case ApiMode::API_DISABLE:
                $result = false;
                break;
            case ApiMode::API_WHITEIP:
                $ip = NetworkHelper::getClientIp();
                $find = false;
                $whiteIps = explode(';', ConfigManager::byKey('security::whiteips'));
                if (ConfigManager::byKey('security::whiteips') != '' && count($whiteIps) > 0) {
                    foreach ($whiteIps as $whiteIp) {
                        if (NetworkHelper::netMatch($whiteIp, $ip)) {
                            $find = true;
                        }
                    }
                    if (!$find) {
                        $result = false;
                    }
                }
                break;
            case ApiMode::API_LOCALHOST:
                if (getClientIp() != '127.0.0.1') {
                    $result = false;
                }
                break;
        }
        return $result;
    }

    /**
     * Get API key from core or plugin
     *
     * @param string $plugin Plugin id
     *
     * @return string API key
     * @throws \Exception
     */
    public static function getApiKey(string $plugin = 'core'): string
    {
        if ($plugin == 'apipro') {
            if (ConfigManager::byKey('apipro') == '') {
                ConfigManager::save('apipro', ConfigManager::genKey());
            }
            return ConfigManager::byKey('apipro');
        }
        if ($plugin == 'apimarket') {
            if (ConfigManager::byKey('apimarket') == '') {
                ConfigManager::save('apimarket', ConfigManager::genKey());
            }
            return ConfigManager::byKey('apimarket');
        }
        if (ConfigManager::byKey('api', $plugin) == '') {
            ConfigManager::save('api', ConfigManager::genKey(), $plugin);
        }
        return ConfigManager::byKey('api', $plugin);
    }
}
