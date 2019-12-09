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

namespace NextDom\Controller\Pages;

use NextDom\Controller\BaseController;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Router;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;

/**
 * Class FirstUseController
 * @package NextDom\Controller
 */
class FirstUseController extends BaseController
{
    /**
     *
     * @param array $pageData
     * @return string
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $configs = ConfigManager::byKeys([
            'notify::status',
            'notify::position',
            'notify::timeout',
            'nextdom::firstUse']);
        if ($configs['nextdom::firstUse'] == 0) {
            Router::showError404AndDie();
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

        $pageData['JS_END_POOL'] = [];
        $pageData['TITLE'] = '1ère Connexion';
        $pageData['JS_VARS'] = [
            'notify_status' => $configs['notify::status'],
            'notify_position' => $configs['notify::position'],
            'notify_timeout' => $configs['notify::timeout'],
            'serverTZoffsetMin' => Utils::getTZoffsetMin(),
            'serverDatetime' => Utils::getMicrotime()
        ];
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][] = '/public/css/pages/firstUse.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/pages/firstUse.js';

        return Render::getInstance()->get('desktop/pages/firstUse.html.twig', $pageData);
    }

}
