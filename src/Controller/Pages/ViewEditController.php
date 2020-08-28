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
use NextDom\Helpers\Render;
use NextDom\Managers\ViewManager;

/**
 * Class ViewEditController
 * @package NextDom\Controller\Pages
 */
class ViewEditController extends BaseController
{
    /**
     * Render view edit page
     *
     * @param array $pageData Page data
     *
     * @return string Content of view edit page
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $pageData['viewEditViewsList'] = ViewManager::all();
        $pageData[ControllerData::CSS_POOL][] = self::PATH_TO_CSS . '/pages/view.css';
        $pageData[ControllerData::JS_END_POOL][] = self::PATH_TO_JS . '/desktop/pages/view_edit.js';

        return Render::getInstance()->get('/desktop/pages/view_edit.html.twig', $pageData);
    }


}
