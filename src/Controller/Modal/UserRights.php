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
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ScenarioManager;

class UserRights extends BaseAbstractModal
{
    /**
     * Render user rights modal
     *
     * @param Render $render Render engine
     *
     * @return string
     * @throws CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render): string
    {
        $userId = Utils::init('id');
        $user = \user::byId($userId);

        if (!is_object($user)) {
            throw new CoreException(__('Impossible de trouver l\'utilisateur : ') . $userId);
        }
        Utils::sendVarToJs('user_rights', Utils::o2a($user));

        $pageData = [];

        $pageData['restrictedUser'] = true;
        if ($user->getProfils() != 'restrict') {
            $pageData['restrictedUser'] = false;
        }
        $pageData['eqLogics'] = EqLogicManager::all();
        $pageData['scenarios'] = ScenarioManager::all();

        return $render->get('/modals/user.rights.html.twig', $pageData);
    }

}
