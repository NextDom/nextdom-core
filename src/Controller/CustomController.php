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
use NextDom\Helpers\Status;
use NextDom\Helpers\Render;

class CustomController extends PagesController
{
    public function __construct()
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
    }
    
    /**
     * Render custom page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of custom page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function custom(Render $render, array &$pageContent): string
    {

        global $NEXTDOM_INTERNAL_CONFIG;
        // TODO: Regrouper les config::byKey
        $pageContent['customDarkThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-dark'];
        $pageContent['customLightThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-light'];
        $pageContent['adminCategories'] = \nextdom::getConfiguration('eqLogic:category');
        $pageContent['Theme'] = \nextdom::getConfiguration('theme');
        $pageContent['customProductName'] = \config::byKey('product_name');
        $pageContent['customTheme'] = \config::byKey('theme');
        $pageContent['customEnableCustomCss'] = \config::byKey('enableCustomCss');
        $pageContent['customJS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.js')) {
            $pageContent['customJS'] = trim(file_get_contents(NEXTDOM_ROOT . '/desktop/custom/custom.js'));
        }
        $pageContent['customCSS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.css')) {
            $pageContent['customCSS'] = trim(file_get_contents(NEXTDOM_ROOT . '/desktop/custom/custom.css'));
        }
        $pageContent['customMobileJS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/mobile/custom/custom.js')) {
            $pageContent['customMobileJS'] = trim(file_get_contents(NEXTDOM_ROOT . '/mobile/custom/custom.js'));
        }
        $pageContent['customMobileCSS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/mobile/custom/custom.css')) {
            $pageContent['customMobileCSS'] = trim(file_get_contents(NEXTDOM_ROOT . '/mobile/custom/custom.css'));
        }

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/params/custom.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/params/custom.html.twig', $pageContent);
    }
}
