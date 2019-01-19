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
use NextDom\Helpers\Status;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\CacheManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UserManager;

class UpdateAdminController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /** Render updateAdmin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of update_admin page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        global $CONFIG;
        global $NEXTDOM_INTERNAL_CONFIG;

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('market::allowDNS', 'ldap::enable');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        //TODO: $key non défini. Keys ?
        $pageContent['networkkey'] = $key;
        $pageContent['adminConfigs'] = ConfigManager::byKeys($keys);
        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['adminConfigs']['ldap::enable'];
        $pageContent['adminIsBan'] = UserManager::isBanned();
        $pageContent['adminHardwareName'] = \nextdom::getHardwareName();
        $pageContent['adminHardwareKey'] = \nextdom::getHardwareKey();
        $pageContent['adminLastKnowDate'] = CacheManager::byKey('hour')->getValue();
        $pageContent['adminIsRescueMode'] = Status::isRescueMode();
        $pageContent['key'] = Status::isRescueMode();

        if (!$pageContent['adminIsRescueMode']) {
            $pageContent['adminPluginsList'] = [];
            $pluginsList = PluginManager::listPlugin(true);
            foreach ($pluginsList as $plugin) {
                $pluginApi = ConfigManager::byKey('api', $plugin->getId());

                if ($pluginApi !== '') {
                    $pluginData = [];
                    $pluginData['api'] = $pluginApi;
                    $pluginData['plugin'] = $plugin;
                    $pageContent['adminPluginsList'][] = $pluginData;
                }
            }
        }
        $pageContent['adminDbConfig'] = $CONFIG['db'];
        $pageContent['adminUseLdap'] = function_exists('ldap_connect');

        $pageContent['adminBannedIp'] = [];
        $cache = CacheManager::byKey('security::banip');
        $values = json_decode($cache->getValue('[]'), true);

        if (is_array($values) && count($values) > 0) {
            foreach ($values as $value) {
                $bannedData = [];
                $bannedData['ip'] = $value['ip'];
                $bannedData['startDate'] = date('Y-m-d H:i:s', $value['datetime']);
                if ($pageContent['adminConfigs']['security::bantime'] < 0) {
                    $bannedData['endDate'] = \__('Jamais');
                } else {
                    $bannedData['endDate'] = date('Y-m-d H:i:s', $value['datetime'] + $pageContent['adminConfigs']['security::bantime']);
                }
                $pageContent['adminBannedIp'][] = $bannedData;
            }
        }

        $pageContent['adminStats'] = CacheManager::stats();
        $pageContent['adminCacheFolder'] = CacheManager::getFolder();
        $pageContent['adminMemCachedExists'] = class_exists('memcached');
        $pageContent['adminRedisExists'] = class_exists('redis');
        $pageContent['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageContent['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/admin/update_admin.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/update_admin.html.twig', $pageContent);
    }
}
