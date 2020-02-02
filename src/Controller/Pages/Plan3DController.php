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
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\UserManager;

/**
 * Class Plan3DController
 * @package NextDom\Controller\Pages
 */
class Plan3DController extends BaseController
{
    /**
     * Render 3d plan page
     *
     * @param array $pageData Page data
     *
     * @return string Content of 3d plan page
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function get(&$pageData): string
    {
        $plan3dHeader = null;
        $list_plan3dHeader = Plan3dHeaderManager::all();
        if (Utils::init('plan3d_id') == '') {
            if (UserManager::getStoredUser()->getOptions('defaultDesktopPlan3d') != '') {
                $plan3dHeader = Plan3dHeaderManager::byId(UserManager::getStoredUser()->getOptions('defaultDesktopPlan3d'));
            }
            if (!is_object($plan3dHeader) && count($list_plan3dHeader) > 0) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        } else {
            $plan3dHeader = Plan3dHeaderManager::byId(Utils::init('plan3d_id'));
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        }
        if (is_object($plan3dHeader)) {
            $pageData[ControllerData::JS_VARS]['plan3dHeader_id'] = $plan3dHeader->getId();
            $pageData['plan3dCurrentHeaderId'] = $plan3dHeader->getId();
        } else {
            $pageData[ControllerData::JS_VARS]['plan3dHeader_id'] = -1;
        }
        $pageData['plan3dHeader'] = Plan3dHeaderManager::all();
        $pageData['plan3dFullScreen'] = Utils::init('fullscreen') == 1;

        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/build/three.min.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/loaders/OBJLoader.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/loaders/MTLLoader.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/controls/TrackballControls.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/controls/OrbitControls.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/renderers/Projector.js';
        $pageData[ControllerData::JS_END_POOL][] = '/vendor/node_modules/three/examples/js/objects/Sky.js';
        $pageData[ControllerData::JS_END_POOL][] = '/assets/js/core/plan3d.class.js';
        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/pages/plan3d.js';

        return Render::getInstance()->get('/desktop/pages/plan3d.html.twig', $pageData);
    }


}
