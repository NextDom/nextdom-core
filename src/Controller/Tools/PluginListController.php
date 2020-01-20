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

namespace NextDom\Controller\Tools;

use NextDom\Controller\BaseController;
use NextDom\Enums\ControllerData;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;

/**
 * Class PluginListController
 * @package NextDom\Controller\Tools
 */
class PluginListController extends BaseController
{
    /**
     * Render plugin page
     *
     * @param array $pageData Page data
     *
     * @return string Content of plugin page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        $pageData[ControllerData::JS_VARS]['sel_plugin_id'] = Utils::init('id', '-1');
        $pageData['pluginsList'] = PluginManager::listPlugin();
        $pageData['pluginsListActive'] = PluginManager::listPlugin(true);
        $pageData['pluginReposList'] = [];

        $updateManagerListRepo = UpdateManager::listRepo();
        foreach ($updateManagerListRepo as $repoCode => $repoData) {
            if ($repoData['enable'] && isset($repoData['scope']['hasStore']) && $repoData['scope']['hasStore']) {
                $pageData['pluginReposList'][$repoCode] = $repoData;
            }
        }
        $pageData['pluginInactiveOpacity'] = NextDomHelper::getConfiguration('eqLogic:style:noactive');
        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/tools/plugin.js';

        return Render::getInstance()->get('/desktop/tools/plugin.html.twig', $pageData);
    }
}
