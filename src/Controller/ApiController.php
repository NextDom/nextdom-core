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

use NextDom\Helpers\PagesController;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class ApiController extends PagesController
{
    public function __construct()
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
    }
    
    /**
     * Render API page
     *
     * @param Render Api Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of API page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function Api(Render $render, array &$pageContent): string
    {

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('api', 'apipro', 'apimarket');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['adminConfigs'] = \config::byKeys($keys);
        $pageContent['adminIsRescueMode'] = Status::isRescueMode();
        if (!$pageContent['adminIsRescueMode']) {
            $pageContent['adminPluginsList'] = [];
            $pluginsList = PluginManager::listPlugin(true);
            foreach ($pluginsList as $plugin) {
                $pluginApi = \config::byKey('api', $plugin->getId());

                if ($pluginApi !== '') {
                    $pluginData = [];
                    $pluginData['api'] = $pluginApi;
                    $pluginData['plugin'] = $plugin;
                    $pageContent['adminPluginsList'][] = $pluginData;
                }
            }
        }

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/admin/api.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/api.html.twig', $pageContent);
    }
}
