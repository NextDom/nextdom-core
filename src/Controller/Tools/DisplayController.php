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

namespace NextDom\Controller\Tools;

use NextDom\Controller\BaseController;
use NextDom\Enums\ControllerData;
use NextDom\Helpers\Render;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;

/**
 * Class DisplayController
 * @package NextDom\Controller\Tools
 */
class DisplayController extends BaseController
{
    /**
     * Render display page
     *
     * @param array $pageData Page data
     *
     * @return string Content of display page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $nbEqlogics   = 0;
        $nbCmds       = 0;
        $jeeObjects   = JeeObjectManager::all();
        $eqLogics     = [];
        $cmds         = [];
        $eqLogics[-1] = EqLogicManager::byObjectId(null, false);

        foreach ($eqLogics[-1] as $eqLogic) {
            $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
            $nbCmds += count($cmds[$eqLogic->getId()]);
        }
        $nbEqlogics += count($eqLogics[-1]);

        foreach ($jeeObjects as $jeeObject) {
            $eqLogics[$jeeObject->getId()] = $jeeObject->getEqLogic(false, false);
            foreach ($eqLogics[$jeeObject->getId()] as $eqLogic) {
                $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
                $nbCmds += count($cmds[$eqLogic->getId()]);
            }
            $nbEqlogics += count($eqLogics[$jeeObject->getId()]);
        }

        $pageData['displayObjects']    = $jeeObjects;
        $pageData['displayNbEqLogics'] = $nbEqlogics;
        $pageData['displayNbCmds']     = $nbCmds;
        $pageData['displayEqLogics']   = $eqLogics;
        $pageData['displayCmds']       = $cmds;

        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/tools/display.js';

        return Render::getInstance()->get('/desktop/tools/display.html.twig', $pageData);
    }

}
