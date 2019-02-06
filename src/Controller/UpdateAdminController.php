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

use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\UserManager;

class UpdateAdminController extends BaseController
{
    /** Render updateAdmin page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of update_admin page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        global $CONFIG;
        global $NEXTDOM_INTERNAL_CONFIG;

        $pageData['adminReposList'] = UpdateManager::listRepo();
        $keys = array('market::allowDNS', 'ldap::enable');
        foreach ($pageData['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageData['adminConfigs'] = ConfigManager::byKeys($keys);
        $pageData['JS_VARS']['ldapEnable'] = $pageData['adminConfigs']['ldap::enable'];
        $pageData['adminIsBan'] = UserManager::isBanned();
        $pageData['adminHardwareName'] = NextDomHelper::getHardwareName();
        $pageData['adminHardwareKey'] = NextDomHelper::getHardwareKey();
        $pageData['adminLastKnowDate'] = CacheManager::byKey('hour')->getValue();
        $pageData['adminIsRescueMode'] = Status::isRescueMode();
        $pageData['key'] = Status::isRescueMode();

        if (!$pageData['adminIsRescueMode']) {
            $pageData['adminPluginsList'] = [];
            $pluginsList = PluginManager::listPlugin(true);
            foreach ($pluginsList as $plugin) {
                $pluginApi = ConfigManager::byKey('api', $plugin->getId());

                if ($pluginApi !== '') {
                    $pluginData = [];
                    $pluginData['api'] = $pluginApi;
                    $pluginData['plugin'] = $plugin;
                    $pageData['adminPluginsList'][] = $pluginData;
                }
            }
        }
        $pageData['adminDbConfig'] = $CONFIG['db'];
        $pageData['adminUseLdap'] = function_exists('ldap_connect');

        $pageData['adminBannedIp'] = [];
        $cache = CacheManager::byKey('security::banip');
        $values = json_decode($cache->getValue('[]'), true);

        if (is_array($values) && count($values) > 0) {
            foreach ($values as $value) {
                $bannedData = [];
                $bannedData['ip'] = $value['ip'];
                $bannedData['startDate'] = date('Y-m-d H:i:s', $value['datetime']);
                if ($pageData['adminConfigs']['security::bantime'] < 0) {
                    $bannedData['endDate'] = \__('Jamais');
                } else {
                    $bannedData['endDate'] = date('Y-m-d H:i:s', $value['datetime'] + $pageData['adminConfigs']['security::bantime']);
                }
                $pageData['adminBannedIp'][] = $bannedData;
            }
        }

        $pageData['adminStats'] = CacheManager::stats();
        $pageData['adminCacheFolder'] = CacheManager::getFolder();
        $pageData['adminMemCachedExists'] = class_exists('memcached');
        $pageData['adminRedisExists'] = class_exists('redis');
        $pageData['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageData['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/update_admin.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/update_admin.html.twig', $pageData);
    }
}
