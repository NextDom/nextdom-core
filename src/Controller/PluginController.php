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

class PluginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render for all plugins pages
     *
     * @param Render $render Render engine (unused)
     * @param array $pageContent Page data (unused)
     * @return string Plugin page
     * @throws \Exception
     */
    public function get(Render $render, array &$pageContent): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        \include_file('desktop', $page, 'php', $plugin->getId(), true);
        return ob_get_clean();
    }
}
