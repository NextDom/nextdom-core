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

use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;

class Plan3DController extends BaseController
{
    /**
     * Render 3d plan page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of 3d plan page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        $plan3dHeader = null;
        $list_plan3dHeader = \plan3dHeader::all();
        if (Utils::init('plan3d_id') == '') {
            if ($_SESSION['user']->getOptions('defaultDesktopPlan3d') != '') {
                $plan3dHeader = \plan3dHeader::byId($_SESSION['user']->getOptions('defaultDesktopPlan3d'));
            }
            if (!is_object($plan3dHeader) && count($list_plan3dHeader) > 0) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        } else {
            $plan3dHeader = \plan3dHeader::byId(Utils::init('plan3d_id'));
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        }
        if (is_object($plan3dHeader)) {
            $pageData['JS_VARS']['plan3dHeader_id'] = $plan3dHeader->getId();
            $pageData['plan3dCurrentHeaderId'] = $plan3dHeader->getId();
        } else {
            $pageData['JS_VARS']['plan3dHeader_id'] = -1;
        }
        $pageData['plan3dHeader'] = \plan3dHeader::all();
        $pageData['plan3dFullScreen'] = Utils::init('fullscreen') == 1;

        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/three.min.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/LoaderSupport.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/OBJLoader.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/MTLLoader.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/controls/TrackballControls.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/controls/OrbitControls.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/renderers/Projector.js';
        $pageData['JS_END_POOL'][] = '/assets/3rdparty/three.js/objects/Sky.js';
        $pageData['JS_END_POOL'][] = '/core/js/plan3d.class.js';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/plan3d.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plan3d.html.twig', $pageData);
    }


}
