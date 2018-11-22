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
use NextDom\Helpers\Utils;
use NextDom\Helpers\Render;

class PlanController extends PagesController
{
    public function __construct()
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
    }
    
    /**
     * Render plan page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of plan page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function plan(Render $render, array &$pageContent): string
    {

        $planHeader = null;
        $planHeaders = \planHeader::all();
        $planHeadersSendToJS = array();
        foreach ($planHeaders as $planHeader_select) {
            $planHeadersSendToJS[] = array('id' => $planHeader_select->getId(), 'name' => $planHeader_select->getName());
        }
        $pageContent['JS_VARS_RAW']['planHeader'] = Utils::getArrayToJQueryJson($planHeadersSendToJS);
        if (Utils::init('plan_id') == '') {
            foreach ($planHeaders as $planHeader_select) {
                if ($planHeader_select->getId() == $_SESSION['user']->getOptions('defaultDashboardPlan')) {
                    $planHeader = $planHeader_select;
                    break;
                }
            }
        } else {
            foreach ($planHeaders as $planHeader_select) {
                if ($planHeader_select->getId() == Utils::init('plan_id')) {
                    $planHeader = $planHeader_select;
                    break;
                }
            }
        }
        if (!is_object($planHeader) && count($planHeaders) > 0) {
            $planHeader = $planHeaders[0];
        }
        if (!is_object($planHeader)) {
            $pageContent['planHeaderError'] = true;
            $pageContent['JS_VARS']['planHeader_id'] = -1;
        } else {
            $pageContent['planHeaderError'] = false;
            $pageContent['JS_VARS']['planHeader_id'] = $planHeader->getId();
        }

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plan.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plan.html.twig', $pageContent);
    }

    /**
     * Render 3d plan page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of 3d plan page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function plan3d(Render $render, array &$pageContent): string
    {

        $plan3dHeader = null;
        $list_plan3dHeader = \plan3dHeader::all();
        if (Utils::init('plan3d_id') == '') {
            if ($_SESSION['user']->getOptions('defaultDesktopPlan3d') != '') {
                $plan3dHeader = \plan3dHeader::byId($_SESSION['user']->getOptions('defaultDesktopPlan3d'));
            }
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        } else {
            $plan3dHeader = \plan3dHeader::byId(Utils::init('plan3d_id'));
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        }
        if (is_object($plan3dHeader)) {
            $pageContent['JS_VARS']['plan3dHeader_id'] = $plan3dHeader->getId();
            $pageContent['plan3dCurrentHeaderId'] = $plan3dHeader->getId();
        } else {
            $pageContent['JS_VARS']['plan3dHeader_id'] = -1;
        }
        $pageContent['plan3dHeader'] = \plan3dHeader::all();
        $pageContent['plan3dFullScreen'] = Utils::init('fullscreen') == 1;

        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/three.min.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/LoaderSupport.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/OBJLoader.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/loaders/MTLLoader.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/controls/TrackballControls.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/controls/OrbitControls.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/renderers/Projector.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/three.js/objects/Sky.js';
        $pageContent['JS_END_POOL'][] = '/core/js/plan3d.class.js';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plan3d.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plan3d.html.twig', $pageContent);
    }


 
}
