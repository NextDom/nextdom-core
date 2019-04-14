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
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\Plan3dHeaderManager;

class Plan3dHeaderConfigure extends BaseAbstractModal
{
    /**
     * Render plan 3d header configure modal
     *
     * @return string
     * @throws CoreException
     */
    public static function get(): string
    {

        $pageData = [];
        $plan3dHeader = Plan3dHeaderManager::byId(Utils::init('plan3dHeader_id'));
        if (!is_object($plan3dHeader)) {
            throw new CoreException('Impossible de trouver le plan');
        }
        Utils::sendVarsToJS([
            'id' => $plan3dHeader->getId(),
            'plan3dHeader' => Utils::o2a($plan3dHeader)
        ]);
        $pageData['plan3dList'] = [];
        foreach ($plan3dHeader->getPlan3d() as $plan3d) {
            $plan3dData = [];
            $plan3dData['id'] = $plan3d->getId();
            $plan3dData['name'] = $plan3d->getName();
            $plan3dData['linkType'] = $plan3d->getLink_type();
            $plan3dData['humanReadable'] = NextDomHelper::toHumanReadable($plan3d->getLink_type());
            $pageData['plan3dList'][] = $plan3dData;
        }
        return Render::getInstance()->get('/modals/plan3dHeader.configure.html.twig', $pageData);
    }

}
