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

namespace NextDom\Controller\Params;

use NextDom\Controller\BaseController;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\SessionHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UserManager;
use NextDom\Managers\ViewManager;

/**
 * Class ProfilsController
 * @package NextDom\Controller\Params
 */
class ProfilsController extends BaseController
{
    /**
     * Render profils page
     *
     * @param array $pageData Page data
     *
     * @return string Content of profils page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        @session_start();
        UserManager::getStoredUser()->refresh();
        @session_write_close();
        $pageData['profilsHomePageDesktop'] = array(
            'core::dashboard' => __('Dashboard'),
            'core::view' => __('Vue'),
            'core::plan' => __('Design'),
            'core::plan3d' => __('Design 3D'),
        );
        $pluginManagerList = PluginManager::listPlugin();
        foreach ($pluginManagerList as $pluginList) {
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '' && ConfigManager::byKey('displayDesktopPanel', $pluginList->getId(), 0) != 0) {
                $pageData['profilsHomePageDesktop'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
        }
        $pageData['profilsUser'] = UserManager::getStoredUser();
        $pageData['profilsSessionsList'] = SessionHelper::getSessionsList();

        $lsCssThemes = FileSystemHelper::ls(NEXTDOM_ROOT . '/public/themes/');
        $pageData['profilsAvatars'] = [];

        $profilRootURL = "/public/img/profils/";
        $profilRootDir = sprintf("%s/public/img/profils/", NEXTDOM_ROOT);
        $lsAvatars = FileSystemHelper::ls($profilRootDir);
        foreach ($lsAvatars as $avatarFile) {
            $path = sprintf("%s/%s", $profilRootDir, $avatarFile);
            $url = sprintf("%s/%s", $profilRootURL, $avatarFile);
            if (true == is_file($path)) {
                $pageData['profilsAvatars'][] = $url;
            }
        }
        $pageData['profilsDisplayTypes'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        $pageData['profilsJeeObjects'] = ObjectManager::all();
        $pageData['profilsViews'] = ViewManager::all();
        $pageData['profilsPlans'] = PlanHeaderManager::all();
        $pageData['profilsPlans3d'] = Plan3dHeaderManager::all();
        $pageData['profilsAllowRemoteUsers'] = ConfigManager::byKey('sso:allowRemoteUser');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/profils.js';

        return Render::getInstance()->get('/desktop/params/profils.html.twig', $pageData);
    }
}
