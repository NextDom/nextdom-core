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

namespace NextDom\Controller\Admin;

use NextDom\Controller\BaseController;
use NextDom\Helpers\Render;
use NextDom\Managers\CacheManager;

/**
 * Class CacheController
 * @package NextDom\Controller\Admin
 */
class CacheController extends BaseController
{
    /**
     * Render cache page
     *
     * @param array $pageData Page data
     *
     * @return string Content of cache page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        $pageData['adminStats'] = CacheManager::stats();
        $pageData['adminCacheFolder'] = CacheManager::getFolder();
        $pageData['adminMemCachedExists'] = class_exists('memcached');
        $pageData['adminRedisExists'] = class_exists('redis');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/cache.js';

        return Render::getInstance()->get('/desktop/admin/cache.html.twig', $pageData);
    }

}
