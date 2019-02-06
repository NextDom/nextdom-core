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

namespace NextDom\Controller;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;

class LogAdminController extends BaseController
{
    /**
     * Render logAdmin page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of log_admin page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {

        global $NEXTDOM_INTERNAL_CONFIG;
        $pageData['adminIsRescueMode'] = Status::isRescueMode();

        if (!$pageData['adminIsRescueMode']) {
            $pageData['adminPluginsList'] = [];
            $pluginsList = PluginManager::listPlugin(true);
            foreach ($pluginsList as $plugin) {
                $pluginApi = ConfigManager::byKey('api', $plugin->getId());
                $pluginData = [];
                $pluginData['api'] = $pluginApi;
                $pluginData['plugin'] = $plugin;
                $pageData['adminPluginsList'][] = $pluginData;
            }
        }
        $pageData['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageData['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/log_admin.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/params/log_admin.html.twig', $pageData);
    }
}
