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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ScenarioManager;

class ScenarioJsonEdit extends BaseAbstractModal
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render scenario json edit configure modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render): string
    {
        $scenarioId  = Utils::init('id');
        $pageContent = [];
        $scenario    = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            throw new CoreException(__('Aucun scénario ne correspondant à : ') . $scenarioId);
        }
        Utils::sendVarToJs('scenarioJsonEdit_scenario_id', $scenarioId);
        $json = array();
        foreach ($scenario->getElement() as $element) {
            $json[] = $element->getAjaxElement();
        }
        $pageContent['scenarioJson'] = json_encode($json, JSON_PRETTY_PRINT);

        return $render->get('/modals/scenario.jsonEdit.html.twig', $pageContent);
    }

}
