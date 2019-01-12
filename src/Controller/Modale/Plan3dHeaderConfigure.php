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

namespace NextDom\Controller\Modale;

use NextDom\Helpers\Utils;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Exceptions\CoreException;

class Plan3dHeaderConfigure extends BaseAbstractModale
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render plan 3d header configure modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render): string
    {
        $pageContent  = [];
        $plan3dHeader = \plan3dHeader::byId(Utils::init('plan3dHeader_id'));
        if (!is_object($plan3dHeader)) {
            throw new CoreException('Impossible de trouver le plan');
        }
        Utils::sendVarsToJS([
            'id'           => $plan3dHeader->getId(),
            'plan3dHeader' => \utils::o2a($plan3dHeader)
                ]);
        return $render->get('/modals/plan3dHeader.configure.html.twig', $pageContent);
    }

}
