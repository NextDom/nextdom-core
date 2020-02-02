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
use NextDom\Enums\Common;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;

/**
 * Class PluginAjax
 * @package NextDom\Ajax
 */
class PluginAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function getConf()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        $update = UpdateManager::byLogicalId(Utils::init(AjaxParams::ID));
        $result = Utils::o2a($plugin);
        $result['activate'] = $plugin->isActive();
        $result['configurationPath'] = $plugin->getPathToConfiguration();
        $result['checkVersion'] = version_compare(NextDomHelper::getJeedomVersion(), $plugin->getRequire());
        $result['update'] = Utils::o2a($update);
        $result['logs'] = [];
        $result['logs'][-1] = ['id' => -1, 'name' => 'local', 'log' => $plugin->getLogList()];
        $result['icon'] = $plugin->getPathImgIcon();
        $this->ajax->success($result);
    }

    public function toggle()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plugin)) {
            throw new CoreException(__('Plugin introuvable : ') . Utils::init(AjaxParams::ID));
        }
        $plugin->setIsEnable(Utils::init(AjaxParams::STATE));
        $this->ajax->success();
    }

    public function all()
    {
        if (!AuthentificationHelper::isConnectedAsAdminOrFail()) {
            throw new CoreException(__('401 - Accès non autorisé'));
        }
        $this->ajax->success(Utils::o2a(PluginManager::listPlugin()));
    }

    public function getDependancyInfo()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $return = ['state' => 'nok', 'log' => 'nok'];
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        if (is_object($plugin)) {
            $return = $plugin->getDependencyInfo();
        }
        $this->ajax->success($return);
    }

    public function dependancyInstall()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plugin)) {
            $this->ajax->success();
        }
        $this->ajax->success($plugin->dependancy_install());
    }

    public function getDeamonInfo()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $pluginId = Utils::init(AjaxParams::ID);
        $result = ['launchable_message' => '', 'launchable' => 'nok', 'state' => 'nok', 'log' => 'nok', 'auto' => 0];
        $plugin = PluginManager::byId($pluginId);
        if (is_object($plugin)) {
            $result = $plugin->deamon_info();
        }
        $result['plugin'] = Utils::o2a($plugin);
        $this->ajax->success($result);
    }

    public function deamonStart()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $pluginId = Utils::init(AjaxParams::ID);
        $plugin = PluginManager::byId($pluginId);
        if (!is_object($plugin)) {
            $this->ajax->success();
        }
        $plugin->deamon_start(Utils::init(AjaxParams::FORCE_RESTART, 0));
        $this->ajax->success();
    }

    public function deamonStop()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plugin)) {
            $this->ajax->success();
        }
        $plugin->deamon_stop();
        $this->ajax->success();
    }

    public function deamonChangeAutoMode()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plugin = PluginManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plugin)) {
            $this->ajax->success();
        }
        $plugin->deamon_changeAutoMode(Utils::init(AjaxParams::MODE));
        $this->ajax->success();
    }

}