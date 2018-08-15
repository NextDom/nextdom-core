<?php
/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Helpers;


use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UpdateManager;

class Controller
{
    const routesList = [
        'dashboard-v2' => 'dashboardV2Page',
        'scenario' => 'scenarioPage',
        'administration' => 'administrationPage',
        'backup' => 'backupPage',
        'object' => 'objectPage',
        'message' => 'messagePage',
        'cron' => 'cronPage',
        'user' => 'userPage'
    ];

    public static function getRoute(string $page)
    {
        $route = null;
        if (array_key_exists($page, self::routesList)) {
            $route = self::routesList[$page];
        }
        return $route;
    }

    /**
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of Dashboard V2 page
     */
    public static function dashboardV2Page(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_VARS']['SEL_OBJECT_ID'] = Utils::init('object_id');
        $pageContent['JS_VARS']['SEL_CATEGORY'] = Utils::init('category', 'all');
        $pageContent['JS_VARS']['SEL_TAG'] = Utils::init('tag', 'all');
        $pageContent['JS_VARS']['SEL_SUMMARY'] = Utils::init('summary');

        if ($pageContent['JS_VARS']['SEL_OBJECT_ID'] == '') {
            $object = JeeObjectManager::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
        } else {
            $object = JeeObjectManager::byId(Utils::init('object_id'));
        }
        if (!is_object($object)) {
            $object = JeeObjectManager::rootObject();
        }
        if (!is_object($object)) {
            throw new \Exception(__('Aucun objet racine trouvé. Pour en créer un, allez dans Outils -> Objets.<br/> Si vous ne savez pas quoi faire ou que c\'est la première fois que vous utilisez Jeedom, n\'hésitez pas à consulter cette <a href="https://jeedom.github.io/documentation/premiers-pas/fr_FR/index" target="_blank">page</a> et celle-là si vous avez un pack : <a href="https://jeedom.com/start" target="_blank">page</a>'));
        }
        $pageContent['JS_VARS']['rootObjectId'] = $object->getId();

        $pageContent['dashboardDisplayObjectByDefault'] = $_SESSION['user']->getOptions('displayObjetByDefault');
        $pageContent['dashboardDisplayScenarioByDefault'] = $_SESSION['user']->getOptions('displayScenarioByDefault');
        $pageContent['dashboardCategory'] = $pageContent['JS_VARS']['SEL_CATEGORY'];
        $pageContent['dashboardTag'] = $pageContent['JS_VARS']['SEL_TAG'];
        $pageContent['dashboardCategories'] = \nextdom::getConfiguration('eqLogic:category', true);
        $pageContent['dashboardTags'] = EqLogicManager::getAllTags();
        $pageContent['dashboardObjectId'] = $pageContent['JS_VARS']['SEL_OBJECT_ID'];
        $pageContent['dashboardObject'] = $object;
        $pageContent['dashboardChildrenObjects'] = JeeObjectManager::buildTree($object);
        if ($pageContent['dashboardDisplayScenarioByDefault'] == 1) {
            $pageContent['dashboardScenarios'] = ScenarioManager::all();
        }
        $pageContent['JS_POOL'][] = '/desktop/js/dashboard.js';
        // A remettre une fois mise sous forme de thème
//        $pageContent['JS_POOL'][] = '/desktop/js/dashboard-v2.js';
        $pageContent['JS_POOL'][] = '/3rdparty/jquery.isotope/isotope.pkgd.min.js';
        $pageContent['JS_POOL'][] = '/3rdparty/jquery.multi-column-select/multi-column-select.js';

        return $render->get('/desktop/dashboard-v2.html.twig', $pageContent);
    }

    public static function scenarioPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['scenarios'] = array();
        // TODO: A supprimé pour éviter la requête inutile
        $pageContent['scenarioCount'] = count(ScenarioManager::all());
        $pageContent['scenarios'][-1] = ScenarioManager::all(null);
        $pageContent['scenarioListGroup'] = ScenarioManager::listGroup();
        if (is_array($pageContent['scenarioListGroup'])) {
            foreach ($pageContent['scenarioListGroup'] as $group) {
                $pageContent['scenarios'][$group['group']] = ScenarioManager::all($group['group']);
            }
        }
        $pageContent['scenarioInactiveStyle'] = \nextdom::getConfiguration('eqLogic:style:noactive');
        $pageContent['scenarioEnabled'] = \config::byKey('enableScenario');
        $pageContent['scenarioAllObjects'] = JeeObjectManager::all();

        $pageContent['JS_END_POOL'][] = '/desktop/js/scenario.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/jquery.sew/jquery.caretposition.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/jquery.sew/jquery.sew.min.js';

        return $render->get('/desktop/scenario.html.twig', $pageContent);
    }

    public static function administrationPage(Render $render, array &$pageContent): string
    {
        global $CONFIG;
        global $NEXTDOM_INTERNAL_CONFIG;

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('api', 'apipro', 'dns::token', 'market::allowDNS', 'market::allowBeta', 'market::allowAllRepo', 'ldap::enable', 'apimarket', 'product_name', 'security::bantime');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['adminConfigs'] = \config::byKeys($keys);
        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['adminConfigs']['ldap::enable'];
        $pageContent['adminIsBan'] = \user::isBan();
        $pageContent['adminHardwareName'] = \nextdom::getHardwareName();
        $pageContent['adminHardwareKey'] = \nextdom::getHardwareKey();
        $pageContent['adminLastKnowDate'] = \cache::byKey('hour')->getValue();
        $pageContent['adminIsRescueMode'] = Status::isRescueMode();
        if (!$pageContent['adminIsRescueMode']) {
            $pageContent['adminPluginsList'] = [];
            $pluginsList = PluginManager::listPlugin(true);
            foreach ($pluginsList as $plugin) {
                $pluginApi = \config::byKey('api', $plugin->getId());
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
        $cache = \cache::byKey('security::banip');
        $values = json_decode($cache->getValue('[]'), true);
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $value) {
                $bannedData = [];
                $bannedData['ip'] = $value['ip'];
                $bannedData['startDate'] = date('Y-m-d H:i:s', $value['datetime']);
                if ($pageContent['adminConfigs']['security::bantime'] < 0) {
                    $bannedData['endDate'] = __('Jamais');
                } else {
                    $bannedData['endDate'] = date('Y-m-d H:i:s', $value['datetime'] + $pageContent['adminConfigs']['security::bantime']);
                }
                $pageContent['adminBannedIp'][] = $bannedData;
            }
        }

        $pageContent['adminNetworkInterfaces'] = [];
        foreach (\network::getInterfaces() as $interface) {
            $intData = [];
            $intData['name'] = $interface;
            $intData['mac'] = \network::getInterfaceMac($interface);
            $intData['ip'] = \network::getInterfaceIp($interface);
            $pageContent['adminNetworkInterfaces'][] = $intData;
        }
        $pageContent['adminDnsRun'] = \network::dns_run();
        $pageContent['adminNetworkExternalAccess'] = \network::getNetworkAccess('external');
        $pageContent['adminCategories'] = \nextdom::getConfiguration('eqLogic:category');
        $pageContent['adminStats'] = \cache::stats();
        $pageContent['adminCacheFolder'] = \cache::getFolder();
        $pageContent['adminMemCachedExists'] = class_exists('memcached');
        $pageContent['adminRedisExists'] = class_exists('redis');
        $pageContent['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageContent['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageContent['CSS_POOL'][] = '/desktop/css/administration.css';
        $pageContent['JS_END_POOL'][] = '/desktop/js/administration.js';

        return $render->get('/desktop/administration.html.twig', $pageContent);
    }

    public static function backupPage(Render $render, array &$pageContent): string {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_VARS_RAW']['REPO_LIST'] = '[]';

        $pageContent['backupAjaxToken'] = \ajax::getToken();
        $pageContent['backupReposList'] = UpdateManager::listRepo();
        $pageContent['JS_END_POOL'][] = '/desktop/js/backup.js';

        return $render->get('/desktop/backup.html.twig', $pageContent);
    }

    public static function objectPage(Render $render, array &$pageContent): string {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_VARS']['select_id'] = Utils::init('id', '-1');
        $pageContent['JS_END_POOL'][] = '/desktop/js/object.js';

        $pageContent['objectList'] = JeeObjectManager::buildTree(null, false);
        $pageContent['objectSummary'] = \config::byKey('object:summary');

        return $render->get('/desktop/object.html.twig', $pageContent);
    }

    public static function messagePage(Render $render, array &$pageContent): string {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['JS_END_POOL'][] = '/desktop/js/message.js';

        $pageContent['messageSelectedPlugin'] = Utils::init('plugin');
        if ($pageContent['messageSelectedPlugin'] != '') {
            $pageContent['messagesList'] = \message::byPlugin($pageContent['messageSelectedPlugin']);
        }
        else {
            $pageContent['messagesList'] = \message::all();
        }
        $pageContent['messagePluginsList'] = \message::listPlugin();
        return $render->get('/desktop/message.html.twig', $pageContent);
    }

    public static function cronPage(Render $render, array &$pageContent): string {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['cronEnabled'] = \config::byKey('enableCron');
        $pageContent['JS_END_POOL'][] = '/desktop/js/cron.js';
        return $render->get('/desktop/cron.html.twig', $pageContent);
    }

    public static function userPage(Render $render, array &$pageContent): string {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['userLdapEnabled'] = \config::byKey('ldap::enable');
        if ($pageContent['userLdapEnabled'] != '1') {
            $user = \user::byLogin('nextdom_support');
            $pageContent['userSupportExists'] = is_object($user);
        }
        $pageContent['userSessionsList'] = \listSession();
        $pageContent['usersList'] = \user::all();
        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['userLdapEnabled'];
        $pageContent['JS_END_POOL'][] = '/desktop/js/user.js';

        return $render->get('/desktop/user.html.twig', $pageContent);
    }
}
