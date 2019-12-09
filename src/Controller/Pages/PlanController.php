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
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\UserManager;

/**
 * Class PlanController
 * @package NextDom\Controller\Pages
 */
class PlanController extends BaseController
{
    /**
     * Render plan page
     *
     * @param array $pageData Page data
     *
     * @return string Content of plan page
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $planHeader = null;
        $planHeaders = PlanHeaderManager::all();
        $planHeadersSendToJS = [];
        foreach ($planHeaders as $planHeader_select) {
            $planHeadersSendToJS[] = ['id' => $planHeader_select->getId(), 'name' => $planHeader_select->getName()];
        }
        $pageData['JS_VARS_RAW']['planHeader'] = Utils::getArrayToJQueryJson($planHeadersSendToJS);
        if (Utils::init('plan_id') == '') {
            foreach ($planHeaders as $planHeader_select) {
                if ($planHeader_select->getId() == UserManager::getStoredUser()->getOptions('defaultDashboardPlan')) {
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
            $pageData['planHeaderError'] = true;
            $pageData['JS_VARS']['planHeader_id'] = -1;
        } else {
            $pageData['planHeaderError'] = false;
            $pageData['JS_VARS']['planHeader_id'] = $planHeader->getId();
        }

        $pageData['CSS_POOL'][] = '/public/css/pages/plan.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/pages/plan.js';

        return Render::getInstance()->get('/desktop/pages/plan.html.twig', $pageData);
    }

}
