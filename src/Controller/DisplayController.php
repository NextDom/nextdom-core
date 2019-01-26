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
use NextDom\Helpers\Status;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\EqLogicManager;

class DisplayController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render display page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of display page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/tools/display.js';

        $nbEqlogics = 0;
        $nbCmds = 0;
        $objects = JeeObjectManager::all();
        $eqLogics = [];
        $cmds = [];
        $eqLogics[-1] = EqLogicManager::byObjectId(null, false);

        foreach ($eqLogics[-1] as $eqLogic) {
            $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
            $nbCmds += count($cmds[$eqLogic->getId()]);
        }
        $nbEqlogics += count($eqLogics[-1]);

        foreach ($objects as $object) {
            $eqLogics[$object->getId()] = $object->getEqLogic(false, false);
            foreach ($eqLogics[$object->getId()] as $eqLogic) {
                $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
                $nbCmds += count($cmds[$eqLogic->getId()]);
            }
            $nbEqlogics += count($eqLogics[$object->getId()]);
        }

        $pageContent['displayObjects']    = $objects;
        $pageContent['displayNbEqLogics'] = $nbEqlogics;
        $pageContent['displayNbCmds']     = $nbCmds;
        $pageContent['displayEqLogics']   = $eqLogics;
        $pageContent['displayCmds']       = $cmds;

        return $render->get('/desktop/tools/display.html.twig', $pageContent);
    }

}
