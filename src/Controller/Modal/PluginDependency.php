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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Managers\PluginManager;
use NextDom\Exceptions\CoreException;

class PluginDependency extends BaseAbstractModal
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
    public function get(Render $render): string
    {
        $pageContent = [];
        $pluginId = init('plugin_id');
        Utils::sendVarToJs('plugin_id', $pluginId);
        if (!class_exists($pluginId)) {
            die();
        }
        $plugin = PluginManager::byId($pluginId);
        $pageContent['dependencyInfo'] = $plugin->getDependencyInfo();

        return $render->get('/modals/plugin.dependency.html.twig', $pageContent);
    }

}
