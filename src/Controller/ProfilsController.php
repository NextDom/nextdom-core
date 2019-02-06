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

use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\SessionHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;

class ProfilsController extends BaseController
{
    /**
     * Render profils page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of profils page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {

        @session_start();
        $_SESSION['user']->refresh();
        @session_write_close();
        $pageData['profilsHomePageDesktop'] = array(
            'core::dashboard' => \__('Dashboard'),
            'core::view' => \__('Vue'),
            'core::plan' => \__('Design'),
            'core::plan3d' => \__('Design 3D'),
        );
        $pageData['profilsHomePageMobile'] = array(
            'core::dashboard' => \__('Dashboard'),
            'core::view' => \__('Vue'),
            'core::plan' => \__('Design'),
            'core::plan3d' => \__('Design 3D'),
        );

        $pluginManagerList = PluginManager::listPlugin();
        foreach ($pluginManagerList as $pluginList) {
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '' && ConfigManager::byKey('displayDesktopPanel', $pluginList->getId(), 0) != 0) {
                $pageData['profilsHomePageDesktop'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '' && ConfigManager::byKey('displayMobilePanel', $pluginList->getId(), 0) != 0) {
                $pageData['profilsHomePageMobile'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
        }
        $pageData['profilsUser'] = $_SESSION['user'];
        $pageData['profilsSessionsList'] = SessionHelper::getSessionsList();

        $lsCssThemes = FileSystemHelper::ls(NEXTDOM_ROOT . '/public/themes/');
        $pageData['profilsMobileThemes'] = [];
        foreach ($lsCssThemes as $dir) {
            if (is_dir(NEXTDOM_ROOT . '/public/themes/' . $dir . '/mobile')) {
                $pageData['profilsMobileThemes'][] = trim($dir, '/');
            }
        }
        $pageData['profilsAvatars'] = [];
        $lsAvatars = FileSystemHelper::ls(NEXTDOM_ROOT . '/public/img/profils/');
        foreach ($lsAvatars as $avatarFile) {
            if (is_file(NEXTDOM_ROOT . '/public/img/profils/' . $avatarFile)) {
                $pageData['profilsAvatars'][] = '/public/img/profils/' . $avatarFile;
            }
        }
        $pageData['profilsDisplayTypes'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        $pageData['profilsJeeObjects'] = JeeObjectManager::all();
        $pageData['profilsViews'] = \view::all();
        $pageData['profilsPlans'] = \planHeader::all();
        $pageData['profilsPlans3d'] = \plan3dHeader::all();
        $pageData['profilsAllowRemoteUsers'] = ConfigManager::byKey('sso:allowRemoteUser');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/profils.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/params/profils.html.twig', $pageData);
    }
}
