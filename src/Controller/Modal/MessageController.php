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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\MessageManager;

class MessageController extends BaseAbstractModal
{
    /**
     * Render message page
     *
     * @return string Content of render page
     *
     */
    public static function get(): string
    {
        $pageData['messageSelectedPlugin'] = Utils::init('plugin_id');
        if ($pageData['messageSelectedPlugin'] != '') {
            $pageData['messagesList'] = MessageManager::byPlugin($pageData['messageSelectedPlugin']);
        } else {
            $pageData['messagesList'] = MessageManager::all();
        }
        $pageData['messagePluginsList'] = MessageManager::listPlugin();

        return Render::getInstance()->get('/modals/message.html.twig', $pageData);
    }


}
