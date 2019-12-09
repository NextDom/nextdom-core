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
use NextDom\Enums\ConfigKey;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\JeeObjectManager;

/**
 * Class ObjectController
 * @package NextDom\Controller\Tools
 */
class ObjectController extends BaseController
{
    /**
     * Render objects page
     *
     * @param array $pageData Page data
     *
     * @return string Content of objects page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $pageData['JS_VARS']['select_id'] = Utils::init('id', '-1');
        $pageData['objectList'] = JeeObjectManager::buildTree(null, false);
        $pageData['objectSummary'] = ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY);

        $pageData['JS_END_POOL'][] = '/public/js/desktop/tools/object.js';

        return Render::getInstance()->get('/desktop/tools/object.html.twig', $pageData);
    }

}
