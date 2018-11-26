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

class UsersController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /** Render summary page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of users page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['userLdapEnabled'] = \config::byKey('ldap::enable');
        if ($pageContent['userLdapEnabled'] != '1') {
            $user = \user::byLogin('nextdom_support');
            $pageContent['userSupportExists'] = is_object($user);
        }
        $pageContent['userSessionsList'] = \listSession();
        $pageContent['usersList'] = \user::all();
        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['userLdapEnabled'];
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/admin/user.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/users.html.twig', $pageContent);
    }


}
