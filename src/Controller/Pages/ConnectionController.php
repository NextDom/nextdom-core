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
use NextDom\Enums\ControllerData;
use NextDom\Helpers\ClientHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

/**
 * Class ConnectionController
 * @package NextDom\Controller
 */
class ConnectionController extends BaseController
{
    /**
     *
     * @param array $pageData
     * @return string
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $coreUpdate = UpdateManager::byType('core');
        $pageData[ControllerData::JS_VARS]['nextdom_waitSpinner'] = ConfigManager::byKey('nextdom::waitSpinner');
        $pageData[ControllerData::JS_VARS]['serverTZoffsetMin'] = Utils::getTZoffsetMin();
        $pageData[ControllerData::JS_END_POOL] = [];
        $pageData[ControllerData::TITLE] = 'Connexion';
        $pageData['NEXTDOM_ROOT'] = NEXTDOM_ROOT;
        $pageData[ControllerData::IS_MOBILE] = ClientHelper::isMobile();
        $pageData['MOBILE_INSTALLED'] = is_dir(NEXTDOM_ROOT . '/mobile');
        if (count($coreUpdate) > 0) {
            $pageData['INSTALL_TYPE'] = $coreUpdate[0]->getSource();
        }
        $pageData[ControllerData::CSS_POOL][] = '/public/css/pages/connection.css';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/admin-lte/dist/js/adminlte.min.js';
        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/pages/connection.js';


        return Render::getInstance()->get('desktop/pages/connection.html.twig', $pageData);
    }

}
