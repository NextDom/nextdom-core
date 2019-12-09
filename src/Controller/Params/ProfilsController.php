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
use NextDom\Managers\ConfigManager;
use NextDom\Managers\JeeObjectManager;
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
        $pageData['profilsUser'] = UserManager::getStoredUser();
        @session_write_close();
        $pageData['profilsHomePageDesktop'] = [
            'core::dashboard' => __('Dashboard'),
            'core::view' => __('Vue'),
            'core::plan' => __('Design'),
            'core::plan3d' => __('Design 3D'),
        ];
        $pluginManagerList = PluginManager::listPlugin();
        foreach ($pluginManagerList as $pluginList) {
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '' && ConfigManager::byKey('displayDesktopPanel', $pluginList->getId(), 0) != 0) {
                $pageData['profilsHomePageDesktop'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
        }

        $lsCssThemes = FileSystemHelper::ls(NEXTDOM_ROOT . '/public/themes/');
        $pageData['profilsAvatar'] = ConfigManager::byKey('avatar');
        if (isset($_SESSION) && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->getOptions('avatar', null) !== null) {
            $pageData['profilsAvatar'] = UserManager::getStoredUser()->getOptions('avatar');
        } else {
            @session_start();
            UserManager::getStoredUser()->setOptions('avatar', $pageData['profilsAvatar']);
            UserManager::getStoredUser()->save();
            @session_write_close();
        }

        $pageData['profilsAvatars'] = [];
        $profilRootURL = "/public/img/profils";
        $profilRootDir = sprintf("%s/public/img/profils/", NEXTDOM_ROOT);
        $lsAvatars = FileSystemHelper::ls($profilRootDir);
        foreach ($lsAvatars as $avatarFile) {
            $path = sprintf("%s/%s", $profilRootDir, $avatarFile);
            $url = sprintf("%s/%s", $profilRootURL, $avatarFile);
            if (true == is_file($path)) {
                $pageData['profilsAvatars'][] = $url;
            }
        }

        $pageData['profilsWidgetTheme'] = ConfigManager::byKey('widget::theme');
        if (isset($_SESSION) && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->getOptions('widget::theme', null) !== null) {
            $pageData['profilsWidgetTheme'] = UserManager::getStoredUser()->getOptions('widget::theme');
        } else {
            @session_start();
            UserManager::getStoredUser()->setOptions('widget::theme', $pageData['profilsWidgetTheme']);
            UserManager::getStoredUser()->save();
            @session_write_close();
        }

        $pageData['profilsWidgetThemes'] = [];
        $lsDir = FileSystemHelper::ls(NEXTDOM_ROOT . '/core/template/dashboard/themes/', '*', true);
        foreach ($lsDir as $themesDir) {
            $lsThemes = FileSystemHelper::ls(NEXTDOM_ROOT . '/core/template/dashboard/themes/' . $themesDir, '*.png');
            foreach ($lsThemes as $themeFile) {
                $themeData = [];
                $themeData['dir'] = '/core/template/dashboard/themes/' . $themesDir . $themeFile;
                $themeData['name'] = $themeFile;
                $pageData['profilsWidgetThemes'][] = $themeData;
            }
        }

        $pageData['profilsDisplayTypes'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        foreach ($pageData['profilsDisplayTypes'] as $key => $value) {
            if (isset($_SESSION) && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->getOptions('widget::background-opacity::' . $key, null) == null) {
                @session_start();
                UserManager::getStoredUser()->setOptions('widget::background-opacity::' . $key, 1);
                UserManager::getStoredUser()->save();
                @session_write_close();
            }
        }
        $pageData['profilsJeeObjects'] = JeeObjectManager::all();
        $pageData['profilsViews'] = ViewManager::all();
        $pageData['profilsPlans'] = PlanHeaderManager::all();
        $pageData['profilsPlans3d'] = Plan3dHeaderManager::all();
        $pageData['profilsAllowRemoteUsers'] = ConfigManager::byKey('sso:allowRemoteUser');

        $themesBases = FileSystemHelper::ls('public/css/themes/', '*nextdom.css');
        $pageData['profilsThemesBases'] = [];
        foreach ($themesBases as $themeBase) {
            $pageData['profilsThemesBases'][] = substr($themeBase, 0, -12);
        }
        $themesIdentities = FileSystemHelper::ls('public/css/themes/', 'dark*.css');
        $pageData['profilsThemesIdentities'] = [];
        foreach ($themesIdentities as $themeIdentity) {
            $pageData['profilsThemesIdentities'][] = substr($themeIdentity, 5, -4);
        }
        $themesIcons = FileSystemHelper::ls('public/img/NextDom/', 'NextDom_Square_*.png');
        $pageData['profilsThemesIcons'] = [];
        foreach ($themesIcons as $themesIcon) {
            $pageData['profilsThemesIcons'][] = substr($themesIcon, 15, -4);
        }
        $pageData['profilsThemeChoice'] = ConfigManager::byKey('nextdom::user-theme');
        $pageData['profilsIconChoice'] = ConfigManager::byKey('nextdom::user-icon');

        $pageData['adminCategories'] = NextDomHelper::getConfiguration('eqLogic:category');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/params/profils.js';

        return Render::getInstance()->get('/desktop/params/profils.html.twig', $pageData);
    }
}
