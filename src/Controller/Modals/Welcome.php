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

namespace NextDom\Controller\Modals;

use NextDom\Helpers\Render;

/**
 * Class Welcome
 * @package NextDom\Controller\Modals
 */
class Welcome extends BaseAbstractModal
{
    /**
     * Render welcome modal
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $pageData = [];
        $pageData['CSS_POOL'][] = '/public/css/pages/welcome.css';
        return Render::getInstance()->get('/modals/welcome.html.twig', $pageData);
    }

}
