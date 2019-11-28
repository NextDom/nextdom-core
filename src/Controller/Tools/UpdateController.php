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
use NextDom\Helpers\Render;
use NextDom\Managers\UpdateManager;

/**
 * Class UpdateController
 * @package NextDom\Controller\Tools
 */
class UpdateController extends BaseController
{
    /**
     * Render update page
     *
     * @param array $pageData Page data
     *
     * @return string Content of objects page
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $updates = [];
        foreach (UpdateManager::listCoreUpdate() as $udpate) {
            $updates[str_replace(['.php', '.sql'], '', $udpate)] = str_replace(['.php', '.sql'], '', $udpate);
        }
        usort($updates, 'version_compare');
        $pageData['numberOfUpdates'] = UpdateManager::nbNeedUpdate();
        $pageData['numberOfUpdatesCore'] = UpdateManager::nbNeedUpdate('core');
        $pageData['numberOfUpdatesPlugins'] = UpdateManager::nbNeedUpdate('plugin');
        $pageData['numberOfUpdatesWidgets'] = UpdateManager::nbNeedUpdate('widget');
        $pageData['numberOfUpdatesScripts'] = UpdateManager::nbNeedUpdate('script');
        $pageData['numberOfUpdatesOthers'] = $pageData['numberOfUpdates'] - ($pageData['numberOfUpdatesCore'] + $pageData['numberOfUpdatesPlugins'] + $pageData['numberOfUpdatesWidgets'] + $pageData['numberOfUpdatesScripts']);
        $pageData['updatesList'] = array_reverse($updates);

        $pageData['JS_END_POOL'][] = '/public/js/desktop/tools/update.js';

        return Render::getInstance()->get('/desktop/tools/update-view.html.twig', $pageData);
    }


}
