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

namespace NextDom\Controller\Admin;

use NextDom\Controller\BaseController;
use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;

/**
 * Class ApiController
 * @package NextDom\Controller\Admin
 */
class ApiController extends BaseController
{
    /**
     * Render API page
     *
     * @param array $pageData Page data
     *
     * @return string Content of API page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $keys = ['api', 'apipro', 'apimarket'];
        foreach (UpdateManager::listRepo() as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageData['adminConfigs'] = ConfigManager::byKeys($keys);
        $pageData['adminPluginsList'] = [];
        $pluginsList = PluginManager::listPlugin(true);
        foreach ($pluginsList as $plugin) {
            $pluginApi = ConfigManager::byKey('api', $plugin->getId());

            if ($pluginApi !== '') {
                $pluginData = [];
                $pluginData['api'] = $pluginApi;
                $pluginData['plugin'] = $plugin;
                $pageData['adminPluginsList'][] = $pluginData;
            }
        }
        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/api.js';

        return Render::getInstance()->get('/desktop/admin/api.html.twig', $pageData);
    }
}
