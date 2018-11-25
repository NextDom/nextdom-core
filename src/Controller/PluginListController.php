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

use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Helpers\Utils;
use NextDom\Helpers\PagesController;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class PluginListController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render plugin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of plugin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plugin.js';
        $pageContent['JS_VARS']['sel_plugin_id'] = Utils::init('id', '-1');
        $pageContent['pluginsList'] = PluginManager::listPlugin();
        $pageContent['pluginReposList'] = [];

        $updateManagerListRepo = UpdateManager::listRepo();
        foreach ($updateManagerListRepo as $repoCode => $repoData) {
            if ($repoData['enable'] && isset($repoData['scope']['hasStore']) && $repoData['scope']['hasStore']) {
                $pageContent['pluginReposList'][$repoCode] = $repoData;
            }
        }
        $pageContent['pluginInactiveOpacity'] = \nextdom::getConfiguration('eqLogic:style:noactive');
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plugin.html.twig', $pageContent);
    }
}
