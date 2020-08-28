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
use NextDom\Enums\ControllerData;
use NextDom\Enums\ConfigKey;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Router;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ThemeManager;

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
            ConfigKey::NOTIFY_STATUS,
            ConfigKey::NOTIFY_POSITION,
            ConfigKey::NOTIFY_TIMEOUT,
            ConfigKey::NEXTDOM_FIRST_USE]
        );
        if ($configs[ConfigKey::NEXTDOM_FIRST_USE] == 0) {
            Router::showError404AndDie();
        }

        $pageData['profilsWidgetThemes'] = ThemeManager::getWidgetThemes();
        $pageData[ControllerData::JS_END_POOL] = [];
        $pageData[ControllerData::TITLE] = '1ère Connexion';
        $pageData[ControllerData::JS_VARS] = [
            'notify_status' => $configs[ConfigKey::NOTIFY_STATUS],
            'notify_position' => $configs[ConfigKey::NOTIFY_POSITION],
            'notify_timeout' => $configs[ConfigKey::NOTIFY_TIMEOUT],
            'serverTZoffsetMin' => Utils::getTZoffsetMin(),
            'serverDatetime' => Utils::getMicrotime()
        ];

        $pageData[ControllerData::CSS_POOL][] = self::PATH_TO_CSS . '/pages/firstUse.css';
        $pageData[ControllerData::JS_END_POOL][] = self::PATH_TO_JS . '/desktop/tools/log.js';
        $pageData[ControllerData::JS_END_POOL][] = self::PATH_TO_JS . '/desktop/pages/firstUse.js';
        $pageData[ControllerData::AJAX_TOKEN] = AjaxHelper::getToken();

        return Render::getInstance()->get('desktop/pages/firstUse.html.twig', $pageData);
    }

}
