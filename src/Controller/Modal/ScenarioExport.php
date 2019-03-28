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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Managers\ScenarioManager;

class ScenarioExport extends BaseAbstractModal
{
    /**
     * Render scenario export modal
     *
     * @return string
     * @throws CoreException
     */
    public static function get(): string
    {

        $scenario = ScenarioManager::byId(init('scenario_id'));

        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario introuvable'));
        }

        $pageData = [];
        $pageData['scenarioExportData'] = $scenario->export();

        return Render::getInstance()->get('/modals/scenario.export.html.twig', $pageData);
    }

}
