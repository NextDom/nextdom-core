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

namespace NextDom\Controller\Modals;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanHeaderManager;

class PlanHeaderConfigure extends BaseAbstractModal
{
    /**
     * Render plan header configure modal
     *
     * @return string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function get(): string
    {


        $planHeader = PlanHeaderManager::byId(Utils::init('planHeader_id'));
        if (!is_object($planHeader)) {
            throw new CoreException('Impossible de trouver le plan');
        }
        $pageData = [];
        $pageData['plansList'] = $planHeader->getPlan();
        Utils::sendVarsToJS([
            'id' => $planHeader->getId(),
            'planHeader' => Utils::o2a($planHeader)
        ]);

        return Render::getInstance()->get('/modals/planHeader.configure.html.twig', $pageData);
    }

}
