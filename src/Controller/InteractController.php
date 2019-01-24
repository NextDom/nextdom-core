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

use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Status;
use NextDom\Helpers\Render;
use NextDom\Managers\CmdManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\EqLogicManager;

class InteractController extends BaseController
{

    
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render interact page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of interact page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {
        $interacts = array();
        $pageContent['interactTotal'] = InteractDefManager::all();
        $interacts[-1] = InteractDefManager::all(null);
        $interactListGroup = InteractDefManager::listGroup();
        if (is_array($interactListGroup)) {
            foreach ($interactListGroup as $group) {
                $interacts[$group['group']] = InteractDefManager::all($group['group']);
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/tools/interact.js';
        $pageContent['interactsList'] = $interacts;
        $pageContent['interactsListGroup'] = $interactListGroup;
        $pageContent['interactDisabledOpacity'] = NextDomHelper::getConfiguration('eqLogic:style:noactive');
        $pageContent['interactCmdType'] = NextDomHelper::getConfiguration('cmd:type');
        $pageContent['interactAllUnite'] = CmdManager::allUnite();
        $pageContent['interactJeeObjects'] = JeeObjectManager::all();
        $pageContent['interactEqLogicTypes'] = EqLogicManager::allType();
        $pageContent['interactEqLogics'] = EqLogicManager::all();
        $pageContent['interactEqLogicCategories'] = NextDomHelper::getConfiguration('eqLogic:category');
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/interact.html.twig', $pageContent);
    }
}
