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
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modals;

use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\PluginManager;

/**
 * Class PluginDaemon
 * @package NextDom\Controller\Modals
 */
class PluginDaemon extends BaseAbstractModal
{
    /**
     * Render plugin daemon modal
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $pluginId = Utils::init('plugin_id');
        if (!class_exists($pluginId)) {
            die();
        }
        $plugin = PluginManager::byId($pluginId);
        $daemonInfo = $plugin->deamon_info();
        if (count($daemonInfo) == 0) {
            die();
        }
        $refresh = [];
        $refresh[0] = 0;
        $pageData = [];
        $pageData['daemonInfoState'] = $daemonInfo['state'];
        $pageData['daemonInfoLaunchable'] = $daemonInfo['launchable'];
        $pageData['daemonInfoLaunchableMessage'] = '';
        if (isset($daemonInfo['launchable_message'])) {
            $pageData['daemonInfoLaunchableMessage'] = $daemonInfo['launchable_message'];
        }
        $pageData['daemonInfoAuto'] = 1;
        if (isset($daemonInfo['auto'])) {
            $pageData['daemonInfoAuto'] = $daemonInfo['auto'];
        }
        if (isset($daemonInfo['last_launch'])) {
            $pageData['daemonInfoLastLaunch'] = $daemonInfo['last_launch'];
        }
        Utils::sendVarsToJs(['plugin_id' => $pluginId, 'refresh_deamon_info' => $refresh]);

        return Render::getInstance()->get('/modals/plugin.daemon.html.twig', $pageData);
    }

}
