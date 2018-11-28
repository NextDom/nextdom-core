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

use NextDom\Managers\PluginManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class ProfilsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }
    
     /**
     * Render profils page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of profils page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        @session_start();
        $_SESSION['user']->refresh();
        @session_write_close();
        $pageContent['profilsHomePage'] = array(
            'core::dashboard' => \__('Dashboard'),
            'core::view' => \__('Vue'),
            'core::plan' => \__('Design'),
        );

        $pluginManagerList = PluginManager::listPlugin();
        foreach ($pluginManagerList as $pluginList) {
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '') {
                $pageContent['profilsHome'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
        }
        $pageContent['profilsUser'] = $_SESSION['user'];
        $pageContent['profilsSessionsList'] = listSession();

        $lsCssThemes = ls(NEXTDOM_ROOT . '/public/themes/');
        $pageContent['profilsMobileThemes'] = [];
        foreach ($lsCssThemes as $dir) {
            if (is_dir(NEXTDOM_ROOT . '/public/themes/' . $dir . '/mobile')) {
                $pageContent['profilsMobileThemes'][] = trim($dir, '/');
            }
        }
        $pageContent['profilsAvatars'] = [];
        $lsAvatars = ls(NEXTDOM_ROOT . '/public/img/profils/');
        foreach ($lsAvatars as $avatarFile) {
            if (is_file(NEXTDOM_ROOT . '/public/img/profils/'.$avatarFile)) {
                $pageContent['profilsAvatars'][] = '/public/img/profils/'.$avatarFile;
            }
        }
        $pageContent['profilsDisplayTypes'] = \nextdom::getConfiguration('eqLogic:displayType');
        $pageContent['profilsJeeObjects'] = JeeObjectManager::all();
        $pageContent['profilsViews'] = \view::all();
        $pageContent['profilsPlans'] = \planHeader::all();
        $pageContent['profilsAllowRemoteUsers'] = \config::byKey('sso:allowRemoteUser');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/params/profils.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/params/profils.html.twig', $pageContent);
    }
}
