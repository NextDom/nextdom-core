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
        global $NEXTDOM_INTERNAL_CONFIG;
        // TODO: Regrouper les config::byKey
        $pageData['customDarkThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-dark'];
        $pageData['customLightThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-light'];
        $pageData['adminCategories'] = NextDomHelper::getConfiguration('eqLogic:category');
        $pageData['Theme'] = NextDomHelper::getConfiguration('theme');
        $pageData['customProductName'] = ConfigManager::byKey('product_name');
        $pageData['customTheme'] = ConfigManager::byKey('theme');
        $pageData['customEnableCustomCss'] = ConfigManager::byKey('enableCustomCss');
        $pageData['customJS'] = '';
        if (file_exists(NEXTDOM_DATA . '/custom/desktop/custom.js')) {
            $pageData['customJS'] = trim(file_get_contents(NEXTDOM_DATA . '/custom/desktop/custom.js'));
        }
        $pageData['customCSS'] = '';
        if (file_exists(NEXTDOM_DATA . '/custom/desktop/custom.css')) {
            $pageData['customCSS'] = trim(file_get_contents(NEXTDOM_DATA . '/custom/desktop/custom.css'));
        }

        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/custom.js';

        return Render::getInstance()->get('/desktop/params/custom.html.twig', $pageData);
    }
}
