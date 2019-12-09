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

namespace NextDom\Controller\Tools;

use NextDom\Controller\BaseController;
use NextDom\Helpers\Render;

/**
 * Class OsDbController
 * @package NextDom\Controller\Tools
 */
class OsDbController extends BaseController
{
    /**
     * Render osdb page
     *
     * @param array $pageData Page data
     *
     * @return string Content of osdb page
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        global $CONFIG;
        $pageData['adminDbConfig'] = $CONFIG['db'];
        $pageData['JS_END_POOL'][] = '/public/js/desktop/tools/osdb.js';

        return Render::getInstance()->get('/desktop/tools/osdb.html.twig', $pageData);
    }

}
