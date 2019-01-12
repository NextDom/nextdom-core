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

namespace NextDom\Controller\Modale;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\PluginManager;

class PluginDaemon extends BaseAbstractModale
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render plugin daemon modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public static function get(Render $render)
    {

        $pluginId = init('plugin_id');
        if (!class_exists($pluginId)) {
            die();
        }
        $plugin     = PluginManager::byId($pluginId);
        $daemonInfo = $plugin->deamon_info();
        if (count($daemonInfo) == 0) {
            die();
        }
        $refresh                                    = array();
        $refresh[0]                                 = 0;
        $pageContent['daemonInfoState']             = $daemonInfo['state'];
        $pageContent['daemonInfoLaunchable']        = $daemonInfo['launchable'];
        $pageContent['daemonInfoLaunchableMessage'] = '';
        if (isset($daemonInfo['launchable_message'])) {
            $pageContent['daemonInfoLaunchableMessage'] = $daemonInfo['launchable_message'];
        }
        $pageContent['daemonInfoAuto'] = 1;
        if (isset($daemonInfo['auto'])) {
            $pageContent['daemonInfoAuto'] = $daemonInfo['auto'];
        }
        if (isset($daemonInfo['last_launch'])) {
            $pageContent['daemonInfoLastLaunch'] = $daemonInfo['last_launch'];
        }
        Utils::sendVarsToJs(['plugin_id' => $pluginId, 'refresh_deamon_info' => $refresh]);

        return $render->get('/modals/plugin.daemon.html.twig');
    }

}
