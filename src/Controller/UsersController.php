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
use NextDom\Helpers\SessionHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;

class UsersController extends BaseController
{
    /** Render summary page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of users page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        $pageData['userLdapEnabled'] = ConfigManager::byKey('ldap::enable');
        if ($pageData['userLdapEnabled'] != '1') {
            $user = UserManager::byLogin('nextdom_support');
            $pageData['userSupportExists'] = is_object($user);
        }
        $pageData['userSessionsList'] = SessionHelper::getSessionsList();
        $pageData['usersList'] = UserManager::all();
        $pageData['JS_VARS']['ldapEnable'] = $pageData['userLdapEnabled'];
        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/user.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/users.html.twig', $pageData);
    }


}
