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

namespace NextDom\Ajax;

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;

class ConfigAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function genApiKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        if (Utils::init('plugin') == 'core') {
            ConfigManager::save('api', ConfigManager::genKey());
            AjaxHelper::success(ConfigManager::byKey('api'));
        } else if (Utils::init('plugin') == 'pro') {
            ConfigManager::save('apipro', ConfigManager::genKey());
            AjaxHelper::success(ConfigManager::byKey('apipro'));
        } else {
            $plugin = Utils::init('plugin');
            ConfigManager::save('api', ConfigManager::genKey(), $plugin);
            AjaxHelper::success(ConfigManager::byKey('api', $plugin));
        }
    }

    public function getKey()
    {
        $keys = Utils::init('key');
        if ($keys == '') {
            throw new CoreException(__('Aucune clef demandée'));
        }
        if (is_json($keys)) {
            $keys = json_decode($keys, true);
            $return = ConfigManager::byKeys(array_keys($keys), Utils::init('plugin', 'core'));
            if (Utils::init('convertToHumanReadable', 0)) {
                $return = NextDomHelper::toHumanReadable($return);
            }
            AjaxHelper::success($return);
        } else {
            $return = ConfigManager::byKey($keys, Utils::init('plugin', 'core'));
            if (Utils::init('convertToHumanReadable', 0)) {
                $return = NextDomHelper::toHumanReadable($return);
            }
            AjaxHelper::success($return);
        }
    }

    public function addKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $values = json_decode(Utils::init('value'), true);
        foreach ($values as $key => $value) {
            ConfigManager::save($key, NextDomHelper::fromHumanReadable($value), Utils::init('plugin', 'core'));
        }
        AjaxHelper::success();
    }

    public function updateTheme()
    {
        $path = NEXTDOM_DATA . '/public/css/theme.css';
        if (true === file_exists($path)) {
            unlink($path);
        }
        AjaxHelper::success();
    }

    public function removeKey()
    {
        Utils::unautorizedInDemo();
        $keys = Utils::init('key');
        if ($keys == '') {
            throw new CoreException(__('Aucune clef demandée'));
        }
        if (is_json($keys)) {
            $keys = json_decode($keys, true);
            foreach ($keys as $key => $value) {
                ConfigManager::remove($key, Utils::init('plugin', 'core'));
            }
        } else {
            ConfigManager::remove(Utils::init('key'), Utils::init('plugin', 'core'));
        }
        AjaxHelper::success();
    }
}
