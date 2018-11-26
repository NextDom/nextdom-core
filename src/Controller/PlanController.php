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

use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Helpers\Render;

class PlanController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
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
    public function get(Render $render, array &$pageContent): string
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
 
}
