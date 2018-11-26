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

use NextDom\Helpers\Render;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;

 
class ScenarioController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

     /**
     * Render scenario page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of scenario page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['scenarios'] = array();
        // TODO: A supprimé pour éviter la requête inutile
        $pageContent['scenarioCount'] = count(ScenarioManager::all());
        $pageContent['scenarios'][-1] = ScenarioManager::all(null);
        $pageContent['scenarioListGroup'] = ScenarioManager::listGroup();

        if (is_array($pageContent['scenarioListGroup'])) {
            foreach ($pageContent['scenarioListGroup'] as $group) {
                $pageContent['scenarios'][$group['group']] = ScenarioManager::all($group['group']);
            }
        }
        $pageContent['scenarioInactiveStyle'] = \nextdom::getConfiguration('eqLogic:style:noactive');
        $pageContent['scenarioEnabled'] = \config::byKey('enableScenario');
        $pageContent['scenarioAllObjects'] = JeeObjectManager::all();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/tools/scenario.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/jquery.sew/jquery.caretposition.js';
        $pageContent['JS_END_POOL'][] = '/assets/3rdparty/jquery.sew/jquery.sew.min.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';


        return $render->get('/desktop/tools/scenario.html.twig', $pageContent);
    }

}
