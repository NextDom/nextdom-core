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
use NextDom\Enums\DateFormat;
use NextDom\Helpers\Render;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;

/**
 * Class SecurityController
 * @package NextDom\Controller\Admin
 */
class SecurityController extends BaseController
{
    /**
     * Render security page
     *
     * @param array $pageData Page data
     *
     * @return string Content of security page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $keys = ['security::bantime', 'ldap::enable'];
        $configs = ConfigManager::byKeys($keys);

        $pageData['JS_VARS']['ldapEnable'] = $configs['ldap::enable'];

        $pageData['adminUseLdap'] = function_exists('ldap_connect');
        if ($pageData['adminUseLdap']) {
            $pageData['adminLdapEnabled'] = ConfigManager::byKey('ldap:enable');
        }
        $pageData['adminBannedIp'] = [];
        $cache = CacheManager::byKey('security::banip');
        $values = json_decode($cache->getValue('[]'), true);

        if (is_array($values) && count($values) > 0) {
            foreach ($values as $value) {
                $bannedData = [];
                $bannedData['ip'] = $value['ip'];
                $bannedData['startDate'] = date(DateFormat::FULL, $value['datetime']);
                if ($configs['security::bantime'] < 0) {
                    $bannedData['endDate'] = __('Jamais');
                } else {
                    $bannedData['endDate'] = date(DateFormat::FULL, $value['datetime'] + $pageData['adminConfigs']['security::bantime']);
                }
                $pageData['adminBannedIp'][] = $bannedData;
            }
        }

        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/security.js';

        return Render::getInstance()->get('/desktop/admin/security.html.twig', $pageData);
    }


}
