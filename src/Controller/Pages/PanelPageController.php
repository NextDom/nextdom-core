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

namespace NextDom\Controller\Pages;

use NextDom\Controller\BaseController;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\PluginManager;

/**
 * Class PanelPageController
 * @package NextDom\Controller\Pages
 */
class PanelPageController extends BaseController
{
    /**
     * @TODO this methode are used ?
     *
     * @param array $pageData
     * @return string
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        echo '<section class="content">';
        FileSystemHelper::includeFile('desktop', $page, 'php', $plugin->getId(), true);
        echo '</section>';

        return ob_get_clean();
    }

}
