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
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\DesignerComponentManager;

class DesignerController extends BaseController
{
    /**
     * Render designer page
     *
     * @param array $pageData Page data
     *
     * @return string Content of plan page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function get(&$pageData): string
    {
        $pageData['CSS_POOL'][] = '/public/css/designer.css';
        $pageData['JS_END_POOL'][] = '/assets/js/desktop/designer.js';
        $pageData['JS_END_POOL'][] = '/vendor/node_modules/magnet/magnet.min.js';

        $pageData['jeeObjects'] = [];
        foreach (JeeObjectManager::all() as $jeeObject) {
            $currentObject = [];
            $currentObject['data'] = $jeeObject;
            $currentObject['eqLogics'] = [];
            foreach (EqLogicManager::byObjectId($jeeObject->getId()) as $eqLogic) {
                $currentEqLogic = [];
                $currentEqLogic['data'] = $eqLogic;
                $currentEqLogic['cmds'] = [];
                foreach (CmdManager::byEqLogicId($eqLogic->getId()) as $cmd) {
                    $currentEqLogic['cmds'][] = $cmd;
                }
                if (count($currentEqLogic['cmds']) > 0) {
                    $currentObject['eqLogics'][] = $currentEqLogic;
                }
            }
            if (count($currentObject['eqLogics']) > 0) {
                $pageData['jeeObjects'][] = $currentObject;
            }
        }
        foreach (DesignerComponentManager::all() as $designerComponent) {
            $currentComponent = [];
            $currentComponent['component'] = $designerComponent;
            if (count($currentComponent['component']) > 0) {
                $pageData['designerComponents'][] = $currentComponent;
            }
        }
        return Render::getInstance()->get('/desktop/designer.html.twig', $pageData);
    }

}
