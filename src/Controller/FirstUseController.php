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
use NextDom\Helpers\Router;
use NextDom\Managers\ConfigManager;

class FirstUseController extends BaseController
{
    /**
     *
     * @param \NextDom\Helpers\Render $render
     * @param array $pageData
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        $configs = ConfigManager::byKeys(array(
            'notify::status',
            'notify::position',
            'notify::timeout',
            'nextdom::firstUse'));
        if ($configs['nextdom::firstUse'] == 0) {
            Router::showError404AndDie();
        }
        $pageData['JS_END_POOL'] = [];
        $pageData['TITLE'] = '1ere connexion';
        $pageData['JS_VARS'] = [
            'notify_status' => $configs['notify::status'],
            'notify_position' => $configs['notify::position'],
            'notify_timeout' => $configs['notify::timeout'],
        ];
        $render = Render::getInstance();
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][] = '/public/css/firstUse.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/firstUse.js';

        return $render->get('desktop/firstUse.html.twig', $pageData);
    }

}
