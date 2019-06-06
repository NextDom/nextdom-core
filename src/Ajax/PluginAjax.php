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
        $plugin = PluginManager::byId(Utils::init('id'));
        $update = UpdateManager::byLogicalId(Utils::init('id'));
        $return = Utils::o2a($plugin);
        $return['activate'] = $plugin->isActive();
        $return['configurationPath'] = $plugin->getPathToConfigurationById();
        $return['checkVersion'] = version_compare(NextDomHelper::getJeedomVersion(), $plugin->getRequire());
        if (is_object($update)) {
            $repoClass = UpdateManager::getRepoDataFromName($update->getSource())['phpClass'];
            if (method_exists($repoClass, 'getInfo')) {
                $return['status'] = $repoClass::getInfo(array('logicalId' => $plugin->getId(), 'type' => 'plugin'));
            }
            if (!isset($return['status'])) {
                $return['status'] = array();
            }
            if (!isset($return['status']['owner'])) {
                $return['status']['owner'] = array();
            }
            foreach (UpdateManager::listRepo() as $key => $repo) {
                if (!isset($repo['scope']['sendPlugin']) || !$repo['scope']['sendPlugin']) {
                    continue;
                }
                if ($update->getSource() != $key) {
                    $return['status']['owner'][$key] = 0;
                    $repoClass = UpdateManager::getRepoDataFromName($key)['phpClass'];
                    if (ConfigManager::byKey($key . '::enable')) {
                        $info = $repoClass::getInfo(array('logicalId' => $plugin->getId(), 'type' => 'plugin'));
                        if (isset($info['owner']) && isset($info['owner'][$key])) {
                            $return['status']['owner'][$key] = $info['owner'][$key];
                        }
                    }
                }
            }
        }

        $return['update'] = Utils::o2a($update);
        $return['logs'] = array();
        $return['logs'][-1] = array('id' => -1, 'name' => 'local', 'log' => $plugin->getLogList());
        $return['icon'] = $plugin->getPathImgIcon();
        AjaxHelper::success($return);
    }

    public function toggle()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plugin = PluginManager::byId(Utils::init('id'));
        if (!is_object($plugin)) {
            throw new CoreException(__('Plugin introuvable : ') . Utils::init('id'));
        }
        $plugin->setIsEnable(Utils::init('state'));
        AjaxHelper::success();
    }

    public function all()
    {
        if (!isConnect()) {
            throw new CoreException(__('401 - Accès non autorisé'));
        }
        AjaxHelper::success(Utils::o2a(PluginManager::listPlugin()));
    }

    public function getDependancyInfo()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $return = array('state' => 'nok', 'log' => 'nok');
        $plugin = PluginManager::byId(Utils::init('id'));
        if (is_object($plugin)) {
            $return = $plugin->getDependencyInfo();
        }
        AjaxHelper::success($return);
    }

    public function dependancyInstall()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plugin = PluginManager::byId(Utils::init('id'));
        if (!is_object($plugin)) {
            AjaxHelper::success();
        }
        AjaxHelper::success($plugin->dependancy_install());
    }

    public function getDeamonInfo()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $pluginId = Utils::init('id');
        $return = array('launchable_message' => '', 'launchable' => 'nok', 'state' => 'nok', 'log' => 'nok', 'auto' => 0);
        $plugin = PluginManager::byId($pluginId);
        if (is_object($plugin)) {
            $return = $plugin->deamon_info();
        }
        $return['plugin'] = Utils::o2a($plugin);
        AjaxHelper::success($return);
    }

    public function deamonStart()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $pluginId = Utils::init('id');
        $plugin = PluginManager::byId($pluginId);
        if (!is_object($plugin)) {
            AjaxHelper::success();
        }
        $plugin->deamon_start(Utils::init('forceRestart', 0));
        AjaxHelper::success();
    }

    public function deamonStop()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plugin = PluginManager::byId(Utils::init('id'));
        if (!is_object($plugin)) {
            AjaxHelper::success();
        }
        $plugin->deamon_stop();
        AjaxHelper::success();
    }

    public function deamonChangeAutoMode()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plugin = PluginManager::byId(Utils::init('id'));
        if (!is_object($plugin)) {
            AjaxHelper::success();
        }
        $plugin->deamon_changeAutoMode(Utils::init('mode'));
        AjaxHelper::success();
    }

}