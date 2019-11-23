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

use NextDom\Enums\AjaxParams;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;

/**
 * Class ConfigAjax
 * @package NextDom\Ajax
 */
class ConfigAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function genApiKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        if (Utils::init(AjaxParams::PLUGIN) == 'core') {
            ConfigManager::save('api', ConfigManager::genKey());
            $this->ajax->success(ConfigManager::byKey('api'));
        } else if (Utils::init(AjaxParams::PLUGIN) == 'pro') {
            ConfigManager::save('apipro', ConfigManager::genKey());
            $this->ajax->success(ConfigManager::byKey('apipro'));
        } else {
            $plugin = Utils::init(AjaxParams::PLUGIN);
            ConfigManager::save('api', ConfigManager::genKey(), $plugin);
            $this->ajax->success(ConfigManager::byKey('api', $plugin));
        }
    }

    public function getKey()
    {
        $keys = Utils::init(AjaxParams::KEY);
        if ($keys == '') {
            throw new CoreException(__('Aucune clé demandée'));
        }
        if (is_json($keys)) {
            $keys = json_decode($keys, true);
            $return = ConfigManager::byKeys(array_keys($keys), Utils::init(AjaxParams::PLUGIN, 'core'));
            if (Utils::init('convertToHumanReadable', 0)) {
                $return = NextDomHelper::toHumanReadable($return);
            }
            $this->ajax->success($return);
        } else {
            $return = ConfigManager::byKey($keys, Utils::init(AjaxParams::PLUGIN, 'core'));
            if (Utils::init('convertToHumanReadable', 0)) {
                $return = NextDomHelper::toHumanReadable($return);
            }
            $this->ajax->success($return);
        }
    }

    public function addKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $values = json_decode(Utils::init('value'), true);
        foreach ($values as $key => $value) {
            ConfigManager::save($key, NextDomHelper::fromHumanReadable($value), Utils::init('plugin', 'core'));
        }
        $this->ajax->success();
    }

    public function removeKey()
    {
        $keys = Utils::init(AjaxParams::KEY);
        if ($keys == '') {
            throw new CoreException(__('Aucune clé demandée'));
        }
        if (is_json($keys)) {
            $keys = json_decode($keys, true);
            foreach ($keys as $key => $value) {
                ConfigManager::remove($key, Utils::init(AjaxParams::PLUGIN, 'core'));
            }
        } else {
            ConfigManager::remove(Utils::init(AjaxParams::KEY), Utils::init(AjaxParams::PLUGIN, 'core'));
        }
        $this->ajax->success();
    }
}
