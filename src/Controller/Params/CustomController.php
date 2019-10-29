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
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;

/**
 * Class CustomController
 * @package NextDom\Controller\Params
 */
class CustomController extends BaseController
{
    /**
     * Render custom page
     *
     * @param array $pageData Page data
     *
     * @return string Content of custom page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $pageData['PRODUCT_NAME'] = ConfigManager::byKey('product_name');
        $themesBases = FileSystemHelper::ls('public/css/themes/', '*nextdom.css');
        $pageData['customThemesBases'] = [];
        foreach ($themesBases as $themeBase) {
            $pageData['customThemesBases'][] = substr($themeBase, 0, -12);
        }
        $themesIdentities = FileSystemHelper::ls('public/css/themes/', 'dark*.css');
        $pageData['customThemesIdentities'] = [];
        foreach ($themesIdentities as $themeIdentity) {
            $pageData['customThemesIdentities'][] = substr($themeIdentity, 5, -4);
        }
        $pageData['customThemeChoice'] = ConfigManager::byKey('nextdom::user-theme');
        $pageData['adminCategories'] = NextDomHelper::getConfiguration('eqLogic:category');
        $pageData['Theme'] = NextDomHelper::getConfiguration('theme');
        $pageData['useCustomTheme'] = false;
        $themeChoice = ConfigManager::byKey('nextdom::theme');
        if (isset($themeChoice['custom']) && $themeChoice['custom'] == 1) {
            $pageData['useCustomTheme'] = true;
        }
        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/custom.js';

        return Render::getInstance()->get('/desktop/params/custom.html.twig', $pageData);
    }
}
