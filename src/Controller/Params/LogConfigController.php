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

namespace NextDom\Controller\Params;

use NextDom\Controller\BaseController;
use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;

/**
 * Class LogConfigController
 * @package NextDom\Controller\Params
 */
class LogConfigController extends BaseController
{
    /**
     * Render logConfig page
     *
     * @param array $pageData Page data
     *
     * @return string Content of log config page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        global $NEXTDOM_INTERNAL_CONFIG;
        $pageData['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageData['adminOthersLogs'] = ['scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event'];
        $pageData['adminPluginsList'] = [];
        $pluginsList = PluginManager::listPlugin(true);
        foreach ($pluginsList as $plugin) {
            $pluginApi = ConfigManager::byKey('api', $plugin->getId());
            $pluginData = [];
            $pluginData['api'] = $pluginApi;
            $pluginData['plugin'] = $plugin;
            $pageData['adminPluginsList'][] = $pluginData;
        }
        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/log_config.js';

        return Render::getInstance()->get('/desktop/params/log_config.html.twig', $pageData);
    }
}
