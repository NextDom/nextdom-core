<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

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
 */

namespace NextDom\Helpers;

use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\CacheManager;

class PagesController
{
    const routesList = [
        'dashboard' => 'dashboardPage',
        'scenario' => 'scenarioPage',
        'administration' => 'administrationPage',
        'backup' => 'backupPage',
        'object' => 'objectPage',
        'message' => 'messagePage',
        'cron' => 'cronPage',
        'user' => 'userPage',
        'update' => 'updatePage',
        'system' => 'systemPage',
        'database' => 'databasePage',
        'display' => 'displayPage',
        'log' => 'logPage',
        'report' => 'reportPage',
        'plugin' => 'pluginPage',
        'editor' => 'editorPage',
        'migration' => 'migrationPage',
        'history' => 'historyPage',
        'shutdown' => 'shutdownPage',
        'health' => 'healthPage',
        'profils' => 'profilsPage',
        'view' => 'viewPage',
        'view_edit' => 'viewEditPage',
        'eqAnalyse' => 'eqAnalyzePage',
        'plan' => 'planPage',
        'plan3d' => 'plan3dPage',
        'market' => 'marketPage',
        'reboot' => 'rebootPage',
        'network' => 'networkPage',
        'cache' => 'cachePage',
        'general' => 'generalPage',
        'log_admin' => 'logAdminPage',
        'log_display' => 'logDisplayPage',
        'custom' => 'customPage',
        'api' => 'APIPage',
        'commandes' => 'commandesPage',
        'osdb' => 'osdbPage',
        'reports_admin' => 'reports_adminPage',
        'eqlogic' => 'eqlogicPage',
        'interact' => 'interactPage',
        'interact_admin' => 'interact_adminPage',
        'links' => 'linksPage',
        'security' => 'securityPage',
        'summary' => 'summaryPage',
        'update_admin' => 'update_adminPage',
        'users' => 'usersPage',
        'tools' => 'toolsPage',
        'note' => 'notePage',
        'pluginRoute' => 'pluginRoute'
    ];

    /**
     * Get static method of page by his code
     *
     * @param string $page Page code
     *
     * @return mixed|null Static method or null
     */
    public static function getRoute(string $page)
    {
        $route = null;
        if (array_key_exists($page, self::routesList)) {
            $route = self::routesList[$page];
        } elseif (in_array($page, PluginManager::listPlugin(true, false, true))) {
            $route = 'pluginRoute';
        }
        return $route;
    }

    /**
     * Render dashboard
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of Dashboard V2 page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function dashboardPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_VARS']['nextdom_Welcome'] = \config::byKey('nextdom::Welcome');
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
            throw new \Exception(__('Aucun objet racine trouvé. Pour en créer un, allez dans Outils -> <a href="/index.php?v=d&p=object">Objets</a>'));
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
        $pageContent['profilsUser'] = $_SESSION['user'];

        if ($pageContent['dashboardDisplayScenarioByDefault'] == 1) {
            $pageContent['dashboardScenarios'] = ScenarioManager::all();
        }
        $pageContent['JS_POOL'][] = '/public/js/desktop/dashboard.js';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/dashboard_events.js';
        // A remettre une fois mise sous forme de thème//
        $pageContent['JS_POOL'][] = '/vendor/node_modules/isotope-layout/dist/isotope.pkgd.min.js';
        $pageContent['JS_POOL'][] = '/3rdparty/jquery.multi-column-select/multi-column-select.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/dashboard.html.twig', $pageContent);
    }

    /**
     * Render scenario page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of scenario page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
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

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/scenario.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/jquery.sew/jquery.caretposition.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/jquery.sew/jquery.sew.min.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';


        return $render->get('/desktop/scenario.html.twig', $pageContent);
    }

    /**
     * Render administration page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of administration page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function administrationPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['administrationMemLoad'] = 100;
        $pageContent['administrationSwapLoad'] = 100;
        $freeData = trim(shell_exec('free'));
        $freeData = explode("\n", $freeData);
        if (count($freeData) > 2) {
            $memData = array_merge(
                array_filter(
                    explode(' ', $freeData[1]),
                    function($value) {
                        return $value !== '';
                    }
                )
            );
            $swapData = array_merge(
                array_filter(
                    explode(' ', $freeData[2]),
                    function($value) {
                        return $value !== '';
                    }
                )
            );
            $pageContent['administrationMemLoad'] = round(100 * $memData[2]/$memData[1], 2);
            $pageContent['administrationSwapLoad'] = round(100 * $swapData[2]/$swapData[1], 2);
        }
        $pageContent['administrationCpuLoad'] = round(100 * sys_getloadavg()[0], 2);
        $pageContent['administrationHddLoad'] = round(100 - 100 * disk_free_space(NEXTDOM_ROOT) / disk_total_space(NEXTDOM_ROOT), 2);
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/administration.html.twig', $pageContent);
    }

    /**
     * Render network page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of network page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function networkPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('dns::token', 'market::allowDNS');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['adminConfigs'] = \config::byKeys($keys);
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

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/network.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/network.html.twig', $pageContent);
    }

    /**
     * Render cache page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of cache page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cachePage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminProductName'] = \config::byKey('product_name');
        $pageContent['adminCustomProductName'] = \config::byKey('name');
        $pageContent['adminStats'] = CacheManager::stats();
        $pageContent['adminCacheFolder'] = CacheManager::getFolder();
        $pageContent['adminMemCachedExists'] = class_exists('memcached');
        $pageContent['adminRedisExists'] = class_exists('redis');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/cache.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/cache.html.twig', $pageContent);
    }

    /**
     * Render general page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of general page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function generalPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['adminHardwareName'] = \nextdom::getHardwareName();
        $pageContent['adminHardwareKey'] = \nextdom::getHardwareKey();
        $cache = \cache::byKey('hour');
        $pageContent['adminLastKnowDate'] = $cache->getValue();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/general.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/general.html.twig', $pageContent);
    }

    /**
     * Render log_admin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of log_admin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function logAdminPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

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
        $pageContent['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/log_admin.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/log_admin.html.twig', $pageContent);
    }

    /**
     * Render log_admin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of log_admin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function logDisplayPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();


        $pageContent['JS_VARS']['log_display_name'] = Utils::init('log', 'event');
        $pageContent['JS_VARS']['log_default_search'] = Utils::init('search', '');

        return $render->get('/desktop/tools/log_display.html.twig', $pageContent);
    }

    /**
     * Render custom page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of custom page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function customPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        global $NEXTDOM_INTERNAL_CONFIG;
        // TODO: Regrouper les config::byKey
        $pageContent['customDarkThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-dark'];
        $pageContent['customLightThemes'] = $NEXTDOM_INTERNAL_CONFIG['themes-light'];
        $pageContent['adminCategories'] = \nextdom::getConfiguration('eqLogic:category');
        $pageContent['Theme'] = \nextdom::getConfiguration('theme');
        $pageContent['customProductName'] = \config::byKey('product_name');
        $pageContent['customTheme'] = \config::byKey('theme');
        $pageContent['customEnableCustomCss'] = \config::byKey('enableCustomCss');
        $pageContent['customJS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.js')) {
            $pageContent['customJS'] = trim(file_get_contents(NEXTDOM_ROOT . '/desktop/custom/custom.js'));
        }
        $pageContent['customCSS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.css')) {
            $pageContent['customCSS'] = trim(file_get_contents(NEXTDOM_ROOT . '/desktop/custom/custom.css'));
        }
        $pageContent['customMobileJS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/mobile/custom/custom.js')) {
            $pageContent['customMobileJS'] = trim(file_get_contents(NEXTDOM_ROOT . '/mobile/custom/custom.js'));
        }
        $pageContent['customMobileCSS'] = '';
        if (file_exists(NEXTDOM_ROOT . '/mobile/custom/custom.css')) {
            $pageContent['customMobileCSS'] = trim(file_get_contents(NEXTDOM_ROOT . '/mobile/custom/custom.css'));
        }

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/custom.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/custom.html.twig', $pageContent);
    }

    /**
     * Render API page
     *
     * @param Render $API Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of API page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function APIPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('api', 'apipro', 'apimarket');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['adminConfigs'] = \config::byKeys($keys);
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

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/api.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/api.html.twig', $pageContent);
    }

    /**
     * Render commandes page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of commandes page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function commandesPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/commandes.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/commandes.html.twig', $pageContent);
    }

    /**
     * Render osdb page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of osdb page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function osdbPage(Render $render, array &$pageContent): string
    {
        global $CONFIG;
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminDbConfig'] = $CONFIG['db'];
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/osdb.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/osdb.html.twig', $pageContent);
    }

    /**
     * Render reports_admin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of report_admin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function reports_adminPage(Render $render, array &$pageContent): string
    {
        global $CONFIG;
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminDbConfig'] = $CONFIG['db'];


        $pageContent['JS_END_POOL'][] = '/public/js/desktop/reports_admin.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/reports_admin.html.twig', $pageContent);
    }

    public static function eqlogicPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/eqlogic.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/eqlogic.html.twig', $pageContent);
    }

    /**
     * Render links page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of links page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function linksPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/links.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/links.html.twig', $pageContent);
    }

    /**
     * Render security page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of security page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function securityPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['adminConfigs']['ldap::enable'];
        $cache = CacheManager::byKey('security::banip');

        $pageContent['adminUseLdap'] = function_exists('ldap_connect');
        if ($pageContent['adminUseLdap']) {
            $pageContent['adminLdapEnabled'] = \config::byKey('ldap:enable');
        }
        $pageContent['adminBannedIp'] = [];
        $cache = CacheManager::byKey('security::banip');
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

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/security.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/security.html.twig', $pageContent);
    }

    /**
     * Render interact_admin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of interact_admin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function interact_adminPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/interact_admin.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/interact_admin.html.twig', $pageContent);
    }

    /** Render summary page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of summary page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function summaryPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/summary.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/summary.html.twig', $pageContent);
    }

    /** Render update_admin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of update_admin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function update_adminPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        global $CONFIG;
        global $NEXTDOM_INTERNAL_CONFIG;

        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('market::allowDNS', 'ldap::enable');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['networkkey'] = $key;
        $pageContent['adminConfigs'] = \config::byKeys($keys);
        $pageContent['JS_VARS']['ldapEnable'] = $pageContent['adminConfigs']['ldap::enable'];
        $pageContent['adminIsBan'] = \user::isBan();
        $pageContent['adminHardwareName'] = \nextdom::getHardwareName();
        $pageContent['adminHardwareKey'] = \nextdom::getHardwareKey();
        $pageContent['adminLastKnowDate'] = CacheManager::byKey('hour')->getValue();
        $pageContent['adminIsRescueMode'] = Status::isRescueMode();
        $pageContent['key'] = Status::isRescueMode();

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
        $cache = CacheManager::byKey('security::banip');
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

        $pageContent['adminStats'] = CacheManager::stats();
        $pageContent['adminCacheFolder'] = CacheManager::getFolder();
        $pageContent['adminMemCachedExists'] = class_exists('memcached');
        $pageContent['adminRedisExists'] = class_exists('redis');
        $pageContent['adminAlerts'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageContent['adminOthersLogs'] = array('scenario', 'plugin', 'market', 'api', 'connection', 'interact', 'tts', 'report', 'event');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/update_admin.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/update_admin.html.twig', $pageContent);
    }

    /** Render summary page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of users page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function usersPage(Render $render, array &$pageContent): string
    {
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
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/user.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/users.html.twig', $pageContent);
    }

    /**
     * Render backup page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of backup page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function backupPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_VARS_RAW']['REPO_LIST'] = '[]';
        $pageContent['backupAjaxToken'] = \ajax::getToken();
        $pageContent['backupReposList'] = UpdateManager::listRepo();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/backup.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/backup.html.twig', $pageContent);
    }

    /**
     * Render cron page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of cron page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cronPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['cronEnabled'] = \config::byKey('enableCron');
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/cron.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/cron.html.twig', $pageContent);
    }

    /**
     * Render health page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of health page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function healthPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['healthInformations'] = \nextdom::health();
        $pageContent['healthPluginsInformations'] = [];
        $pageContent['healthPluginDataToShow'] = false;
        $pageContent['healthTotalNOk'] = 0;
        $pageContent['healthTotalPending'] = 0;
        // Santé pour chaque plugin
        foreach (PluginManager::listPlugin(true) as $plugin) {
            $pluginData = [];
            $pluginData['hasSpecificHealth'] = false;
            // Test si le plugin offre une information spécifique pour sa santé
            if (file_exists(dirname(PluginManager::getPathById($plugin->getId())) . '/../desktop/modal/health.php')) {
                $pluginData['hasSpecificHealth'] = true;
            }
            /**
             * Ajouter des informations sur la santé si le plugin
             *  - A des dépendances
             *  - A un daemon
             *  - A une méthode health
             *  - A une informations de santé spécifique
             */
            if ($plugin->getHasDependency() == 1 || $plugin->getHasOwnDeamon() == 1 || method_exists($plugin->getId(), 'health') || $pluginData['hasSpecificHealth']) {
                // Etat pour savoir si des données doivent être affichées
                $pageContent['healthPluginDataToShow'] = true;
                // Objet du plugin
                $pluginData['plugin'] = $plugin;
                // Port si nécessaire
                $pluginData['port'] = false;
                // Nombre d'erreur
                $pluginData['nOk'] = 0;
                // Nombre d'état en attente
                $pluginData['pending'] = 0;
                // Etat pour savoir si le plugin a des dépendances
                $pluginData['hasDependency'] = false;
                // Etat pour savoir si le plugin a un daemon
                $pluginData['hasOwnDaemon'] = false;
                // Etat pour savoir si le tableau doit être affiché
                $pluginData['showOnlyTable'] = false;
                // Le port du plugin est stocké dans la configuration
                $port = \config::byKey('port', $plugin->getId());
                // Si un port est configuré, stockage pour la vue
                if ($port != '') {
                    $pluginData['port'] = $port;
                }
                // Si le plugin a des dépendances, un daemon, une méthode health ou des informations de santé
                // Affiche le tableau
                if ($plugin->getHasDependency() == 1 || $plugin->getHasOwnDeamon() == 1 || method_exists($plugin->getId(), 'health')) {
                    $pluginData['showOnlyTable'] = true;
                }
                // Si le plugin a des dépendances
                if ($plugin->getHasDependency() == 1) {
                    $pluginData['hasDependency'] = true;
                    $dependencyInfo = $plugin->dependancy_info();
                    // récupération des informations sur ses dépendances
                    if (isset($dependencyInfo['state'])) {
                        $pluginData['dependencyState'] = $dependencyInfo['state'];
                        // Erreur
                        if ($pluginData['dependencyState'] == 'nok') {
                            $pluginData['nOk']++;
                            // En cours
                        } elseif ($pluginData['dependencyState'] == 'in_progress') {
                            $pluginData['pending']++;
                            // Autres
                        } elseif ($pluginData['dependencyState'] != 'ok') {
                            $pluginData['nOk']++;
                        }
                    }
                }
                // Si le plugin a un daemon
                if ($plugin->getHasOwnDeamon() == 1) {
                    $pluginData['hasOwnDaemon'] = true;
                    $daemonInfo = $plugin->deamon_info();
                    // Statut du lancement automatique
                    $pluginData['daemonAuto'] = $daemonInfo['auto'];
                    if (isset($daemonInfo['launchable'])) {
                        // Erreur si le daemon doit est lançable et qu'il n'est pas lancé
                        $pluginData['daemonLaunchable'] = $daemonInfo['launchable'];
                        if ($pluginData['daemonLaunchable'] == 'nok' && $pluginData['daemonAuto'] == 1) {
                            $pluginData['nOk']++;
                        }
                    }
                    $pluginData['daemonLaunchableMessage'] = $daemonInfo['launchable_message'];
                    $pluginData['daemonState'] = $daemonInfo['state'];
                    // Erreur si le daemon doit se lancer en automatique et qu'il n'est pas lancé
                    if ($pluginData['daemonState'] == 'nok' && $pluginData['daemonAuto'] == 1) {
                        $pluginData['nOk']++;
                    }
                }
                // Données fournies par le plugin
                if (method_exists($plugin->getId(), 'health')) {
                    $pluginData['health'] = [];
                    // Récupère l'ensemble des données et boucle
                    foreach ($plugin->getId()::health() as $pluginHealthData) {
                        $pluginData['health'][] = [
                            'test' => $pluginHealthData['test'],
                            'state' => $pluginHealthData['state'],
                            'result' => $pluginHealthData['result'],
                            'advice' => $pluginHealthData['advice']
                        ];
                        if ($pluginHealthData['state'] === 'nok' || $pluginHealthData['state'] == false) {
                            $pluginData['nOk']++;
                        }
                    }
                }
                if ($pluginData['nOk'] > 0) {
                    $pageContent['healthTotalNOk']++;
                }
                if ($pluginData['pending'] > 0) {
                    $pageContent['healthTotalPending']++;
                }
                $pageContent['healthPluginsInformations'][] = $pluginData;
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/health.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/health.html.twig', $pageContent);
    }

    /**
     * Render history page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of history page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function historyPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['historyDate'] = array(
            'start' => date('Y-m-d', strtotime(\config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d'))),
            'end' => date('Y-m-d'),
        );
        $pageContent['historyCmdsList'] = CmdManager::allHistoryCmd();
        $pageContent['historyPluginsList'] = PluginManager::listPlugin();
        $pageContent['historyEqLogicCategories'] = \nextdom::getConfiguration('eqLogic:category');
        $pageContent['historyObjectsList'] = JeeObjectManager::all();
        $pageContent['JS_POOL'][] = '/3rdparty/visjs/vis.min.js';
        $pageContent['CSS_POOL'][] = '/3rdparty/visjs/vis.min.css';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/history.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/history.html.twig', $pageContent);
    }

    /**
     * Render log page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of log page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function logPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/log.js';
        $currentLogfile = Utils::init('logfile');
        $logFilesList = [];
        $dir = opendir(NEXTDOM_ROOT . '/log/');
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && $file != '.htaccess' && !is_dir(NEXTDOM_ROOT . '/log/' . $file)) {
                $logFilesList[] = $file;
            }
        }
        natcasesort($logFilesList);
        $pageContent['logFilesList'] = [];
        foreach ($logFilesList as $logFile) {
            $hasError = 0;
            $logFileData = [];
            $logFileData['name'] = $logFile;
            $logFileData['icon'] = 'check';
            $logFileData['color'] = 'green';
            if (shell_exec('grep -c -E "\[ERROR\]|\[error\]" ' . NEXTDOM_ROOT . '/log/' . $logFile) != 0) {
                $logFileData['icon'] = 'exclamation-triangle';
                $logFileData['color'] = 'red';
            } elseif (shell_exec('grep -c -E "\[WARNING\]" ' . NEXTDOM_ROOT . '/log/' . $logFile) != 0) {
                $logFileData['icon'] = 'exclamation-circle';
                $logFileData['color'] = 'orange';
            }
            if ($currentLogfile == $logFile) {
                $logFileData['active'] = true;
            } else {
                $logFileData['active'] = false;
            }
            $logFileData['size'] = round(filesize(NEXTDOM_ROOT . '/log/' . $logFile) / 1024);
            $pageContent['logFilesList'][] = $logFileData;
        }
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/logs-view.html.twig', $pageContent);
    }

    /**
     * Render migration page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of migration page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function migrationPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['migrationAjaxToken'] = \ajax::getToken();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/migration.js';
        return $render->get('/desktop/tools/migration.html.twig', $pageContent);
    }

    /**
     * Render node page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of migration page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function notePage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/note.js';
        return $render->get('/desktop/tools/note.html.twig', $pageContent);
    }

    /**
     * Render report page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of report page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function reportPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/report.js';
        $report_path = NEXTDOM_ROOT . '/data/report/';
        $pageContent['reportViews'] = [];
        $allViews = \view::all();
        foreach ($allViews as $view) {
            $viewData = [];
            $viewData['id'] = $view->getId();
            $viewData['name'] = $view->getName();
            $viewData['number'] = count(ls($report_path . '/view/' . $view->getId(), '*'));
            $pageContent['reportViews'][] = $viewData;
        }
        $pageContent['reportPlans'] = [];
        $allPlanHeader = \planHeader::all();
        foreach ($allPlanHeader as $plan) {
            $planData = [];
            $planData['id'] = $plan->getId();
            $planData['name'] = $plan->getName();
            $planData['number'] = count(ls($report_path . '/plan/' . $plan->getId(), '*'));
            $pageContent['reportPlans'][] = $planData;
        }
        $pageContent['reportPlugins'] = [];
        $pluginManagerList = PluginManager::listPlugin(true);
        foreach ($pluginManagerList as $plugin) {
            if ($plugin->getDisplay() != '') {
                $pluginData = [];
                $pluginData['id'] = $plugin->getId();
                $pluginData['name'] = $plugin->getName();
                $pluginData['number'] = count(ls($report_path . '/plugin/' . $plugin->getId(), '*'));
                $pageContent['reportPlugins'][] = $pluginData;
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/reports-view.html.twig', $pageContent);
    }

    /**
     * Render update page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of objects page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function updatePage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $updates = array();
        foreach (UpdateManager::listCoreUpdate() as $udpate) {
            $updates[str_replace(array('.php', '.sql'), '', $udpate)] = str_replace(array('.php', '.sql'), '', $udpate);
        }
        usort($updates, 'version_compare');
        $pageContent['updatesList'] = array_reverse($updates);
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/update.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/tools/update-view.html.twig', $pageContent);
    }

    /**
     * Render message page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of render page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function messagePage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/message.js';

        $pageContent['messageSelectedPlugin'] = Utils::init('plugin');
        if ($pageContent['messageSelectedPlugin'] != '') {
            $pageContent['messagesList'] = \message::byPlugin($pageContent['messageSelectedPlugin']);
        } else {
            $pageContent['messagesList'] = \message::all();
        }
        $pageContent['messagePluginsList'] = \message::listPlugin();
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/message.html.twig', $pageContent);
    }

    /**
     * Render system page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of system page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function systemPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageData['systemCanSudo'] = \nextdom::isCapable('sudo');
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/system.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/system.html.twig', $pageContent);
    }

    /**
     * Render database page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of database page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function databasePage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/database.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/database.html.twig', $pageContent);
    }

    /**
     * Render display page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of display page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function displayPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/display.js';

        $nbEqlogics = 0;
        $nbCmds = 0;
        $objects = JeeObjectManager::all();
        $eqLogics = [];
        $cmds = [];
        $eqLogics[-1] = EqLogicManager::byObjectId(null, false);

        foreach ($eqLogics[-1] as $eqLogic) {
            $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
            $nbCmds += count($cmds[$eqLogic->getId()]);
        }
        $nbEqlogics += count($eqLogics[-1]);

        foreach ($objects as $object) {
            $eqLogics[$object->getId()] = $object->getEqLogic(false, false);
            foreach ($eqLogics[$object->getId()] as $eqLogic) {
                $cmds[$eqLogic->getId()] = $eqLogic->getCmd();
                $nbCmds += count($cmds[$eqLogic->getId()]);
            }
            $nbEqlogics += count($eqLogics[$object->getId()]);
        }

        $pageContent['displayObjects'] = $objects;
        $pageContent['displayNbEqLogics'] = $nbEqlogics;
        $pageContent['displayNbCmds'] = $nbCmds;
        $pageContent['displayEqLogics'] = $eqLogics;
        $pageContent['displayCmds'] = $cmds;

        return $render->get('/desktop/display.html.twig', $pageContent);
    }

    /**
     * Render plugin page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of plugin page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function pluginPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plugin.js';
        $pageContent['JS_VARS']['sel_plugin_id'] = Utils::init('id', '-1');
        $pageContent['pluginsList'] = PluginManager::listPlugin();
        $pageContent['pluginReposList'] = [];

        $updateManagerListRepo = UpdateManager::listRepo();
        foreach ($updateManagerListRepo as $repoCode => $repoData) {
            if ($repoData['enable'] && isset($repoData['scope']['hasStore']) && $repoData['scope']['hasStore']) {
                $pageContent['pluginReposList'][$repoCode] = $repoData;
            }
        }
        $pageContent['pluginInactiveOpacity'] = \nextdom::getConfiguration('eqLogic:style:noactive');
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plugin.html.twig', $pageContent);
    }

    /**
     * Render editor page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of editor page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function editorPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_VARS']['rootPath'] = NEXTDOM_ROOT;

        $pageContent['editorFolders'] = [];
        $pageContent['editorRootPath'] = NEXTDOM_ROOT;

        $lsNextDomRoot = \ls(NEXTDOM_ROOT, '*', false, array('folders'));
        foreach ($lsNextDomRoot as $folder) {
            $pageContent['editorFolders'][] = $folder;
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/editor.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';


        return $render->get('/desktop/editor.html.twig', $pageContent);
    }

    /**
     * Render shutdown page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of shutdown page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function shutdownPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/shutdown.html.twig', $pageContent);
    }

    /**
     * Render profils page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of profils page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function profilsPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        @session_start();
        $_SESSION['user']->refresh();
        @session_write_close();
        $pageContent['profilsHomePage'] = array(
            'core::dashboard' => __('Dashboard'),
            'core::view' => __('Vue'),
            'core::plan' => __('Design'),
        );

        $pluginManagerList = PluginManager::listPlugin();
        foreach ($pluginManagerList as $pluginList) {
            if ($pluginList->isActive() == 1 && $pluginList->getDisplay() != '') {
                $pageContent['profilsHomePage'][$pluginList->getId() . '::' . $pluginList->getDisplay()] = $pluginList->getName();
            }
        }
        $pageContent['profilsUser'] = $_SESSION['user'];
        $pageContent['profilsSessionsList'] = listSession();

        $lsCssThemes = ls(NEXTDOM_ROOT . '/public/themes/');
        $pageContent['profilsMobileThemes'] = [];
        foreach ($lsCssThemes as $dir) {
            if (is_dir(NEXTDOM_ROOT . '/public/themes/' . $dir . '/mobile')) {
                $pageContent['profilsMobileThemes'][] = trim($dir, '/');
            }
        }
        $pageContent['profilsAvatars'] = [];
        $lsAvatars = ls(NEXTDOM_ROOT . '/public/img/profils/');
        foreach ($lsAvatars as $avatarFile) {
            if (is_file(NEXTDOM_ROOT . '/public/img/profils/'.$avatarFile)) {
                $pageContent['profilsAvatars'][] = '/public/img/profils/'.$avatarFile;
            }
        }
        $pageContent['profilsDisplayTypes'] = \nextdom::getConfiguration('eqLogic:displayType');
        $pageContent['profilsJeeObjects'] = JeeObjectManager::all();
        $pageContent['profilsViews'] = \view::all();
        $pageContent['profilsPlans'] = \planHeader::all();
        $pageContent['profilsAllowRemoteUsers'] = \config::byKey('sso:allowRemoteUser');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/profils.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/profils.html.twig', $pageContent);
    }

    /**
     * Render view page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of view page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function viewPage(Render $render, array &$pageContent): string
    {

        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['viewsList'] = \view::all();
        $pageContent['viewHideList'] = true;
        $pageContent['viewIsAdmin'] = Status::isConnectAdmin();
        $pageContent['viewDefault'] = $_SESSION['user']->getOptions('displayViewByDefault');
        $pageContent['viewNoControl'] = Utils::init('noControl');

        $currentView = null;
        if (Utils::init('view_id') == '') {

            if ($_SESSION['user']->getOptions('defaultDesktopView') != '') {
                $currentView = \view::byId($_SESSION['user']->getOptions('defaultDesktopView'));
            }

            if (!is_object($currentView)) {
                $currentView = $pageContent['viewsList'][0];
            }
        } else {
            $currentView = \view::byId(init('view_id'));

            if (!is_object($currentView)) {
                throw new \Exception('{{Vue inconnue. Vérifier l\'ID.}}');
            }
        }

        if (!is_object($currentView)) {
            throw new \Exception(__('Aucune vue n\'existe, cliquez <a href="index.php?v=d&p=view_edit">ici</a> pour en créer une.'));
        }
        $pageContent['viewCurrent'] = $currentView;

        if ($_SESSION['user']->getOptions('displayViewByDefault') == 1 && Utils::init('report') != 1) {
            $pageContent['viewHideList'] = false;
        }
        $pageContent['JS_VARS']['view_id'] = $currentView->getId();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/view.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/view.html.twig', $pageContent);
    }

    /**
     * Render view edit page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of view edit page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function viewEditPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['viewEditViewsList'] = \view::all();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/view_edit.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/view_edit.html.twig', $pageContent);
    }

    /**
     * Render eqLogic analyze page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of eqLogic analyze page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function eqAnalyzePage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        global $NEXTDOM_INTERNAL_CONFIG;

        $pageContent['eqAnalyzeEqLogicList'] = [];

        $eqLogicMangerAll = EqLogicManager::all();
        foreach ($eqLogicMangerAll as $eqLogic) {
            $battery_type = str_replace(array('(', ')'), ['', ''], $eqLogic->getConfiguration('battery_type', ''));
            if ($eqLogic->getStatus('battery', -2) != -2) {
                $pageContent['eqAnalyzeEqLogicList'][] = $eqLogic;
            }
        }
        usort($pageContent['eqAnalyzeEqLogicList'], function ($a, $b) {
            $result = 0;
            if ($a->getStatus('battery') < $b->getStatus('battery')) {
                $result = -1;
            } elseif ($a->getStatus('battery') > $b->getStatus('battery')) {
                $result = 1;
            }
            return $result;
        });

        $cmdDataArray = [];
        foreach ($eqLogicMangerAll as $eqLogic) {
            $cmdData = [];
            $cmdData['eqLogic'] = $eqLogic;
            $cmdData['infoCmds'] = [];
            $cmdData['actionCmds'] = [];

            $eqlogicGetCmdInfo = $eqLogic->getCmd('info');
            foreach ($eqlogicGetCmdInfo as $cmd) {
                if (count($cmd->getConfiguration('actionCheckCmd', array())) > 0) {
                    $data = [];
                    $data['cmd'] = $cmd;
                    $data['actions'] = [];
                    foreach ($cmd->getConfiguration('actionCheckCmd') as $actionCmd) {
                        $data['actions'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                    $cmdData['infoCmds'][] = $data;
                }
            }

            $eqLogicGetCmdAction = $eqLogic->getCmd('action');
            foreach ($eqLogicGetCmdAction as $cmd) {
                $actionCmdData = [];
                $actionCmdData['cmd'] = $cmd;

                if (count($cmd->getConfiguration('nextdomPreExecCmd', [])) > 0) {
                    $actionCmdData['preExecCmds'] = [];

                    $cmdGetConfigurationNextdomPreExecCmd = $cmd->getConfiguration('nextdomPreExecCmd');
                    foreach ($cmdGetConfigurationNextdomPreExecCmd as $actionCmd) {
                        $actionCmdData['preExecCmds'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                }
                if (count($cmd->getConfiguration('nextdomPostExecCmd', [])) > 0) {
                    $actionCmdData['postExecCmds'] = [];
                    foreach ($cmdGetConfigurationNextdomPreExecCmd as $actionCmd) {
                        $actionCmdData['postExecCmds'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                }
                $cmdData['actionCmds'][] = $actionCmdData;
            }
            $cmdDataArray[] = $cmdData;
        }
        $pageContent['eqAnalyzeCmdData'] = $cmdDataArray;
//TODO: Imbriquer les boucles quand le fonctionnement sera sûr
        $pageContent['eqAnalyzeAlerts'] = [];

        $eqLogicManagerAll = EqLogicManager::all();
        foreach ($eqLogicManagerAll as $eqLogic) {
            $hasSomeAlerts = 0;

            $listCmds = [];
            $eqLogicGetCmdInfo = $eqLogic->getCmd('info');
            foreach ($eqLogicGetCmdInfo as $cmd) {
                foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {

                    if ($value['check']) {
                        if ($cmd->getAlert($level . 'if', '') != '') {
                            $hasSomeAlerts += 1;
                            if (!in_array($cmd, $listCmds)) {
                                $listCmds[] = $cmd;
                            }
                        }
                    }
                }
            }

            if ($eqLogic->getConfiguration('battery_warning_threshold', '') != '') {
                $hasSomeAlerts += 1;
            }

            if ($eqLogic->getConfiguration('battery_danger_threshold', '') != '') {
                $hasSomeAlerts += 1;
            }

            if ($eqLogic->getTimeout('')) {
                $hasSomeAlerts += 1;
            }

            if ($hasSomeAlerts != 0) {
                $alertData = [];
                $alertData['eqLogic'] = $eqLogic;

                foreach ($listCmds as $cmdalert) {
                    foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
                        if ($value['check']) {
                            if ($cmdalert->getAlert($level . 'if', '') != '') {
                                $during = '';
                                if ($cmdalert->getAlert($level . 'during', '') == '') {
                                    $during = ' effet immédiat';
                                } else {
                                    $during = ' pendant plus de ' . $cmdalert->getAlert($level . 'during', '') . ' minute(s)';
                                }
                                $alertData['msg'] = ucfirst($level) . ' si ' . \nextdom::toHumanReadable(str_replace('#value#', '<b>' . $cmdalert->getName() . '</b>', $cmdalert->getAlert($level . 'if', ''))) . $during . '</br>';
                            }
                        }
                    }
                }
                $pageContent['eqAnalyzeAlerts'][] = $alertData;
            }
        }

        $pageContent['eqAnalyzeNextDomDeadCmd'] = \nextdom::deadCmd();
        $pageContent['eqAnalyzeCmdDeadCmd'] = CmdManager::deadCmd();
        $pageContent['eqAnalyzeJeeObjectDeadCmd'] = JeeObjectManager::deadCmd();
        $pageContent['eqAnalyzeScenarioDeadCmd'] = ScenarioManager::consystencyCheck(true);
        $pageContent['eqAnalyzeInteractDefDeadCmd'] = \interactDef::deadCmd();
        $pageContent['eqAnalyzePluginDeadCmd'] = [];

        $pluginManagerListPluginTrue = PluginManager::listPlugin(true);
        foreach ($pluginManagerListPluginTrue as $plugin) {
            $pluginId = $plugin->getId();
            if (method_exists($pluginId, 'deadCmd')) {
                $pageContent['eqAnalyzePluginDeadCmd'][] = $pluginId::deadCmd();
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/eqAnalyse.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/eqAnalyze.html.twig', $pageContent);
    }

    /**
     * Render objects page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of objects page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function objectPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $pageContent['JS_VARS']['select_id'] = Utils::init('id', '-1');
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/object.js';
        $pageContent['objectProductName'] = \config::byKey('product_name');
        $pageContent['objectCustomProductName'] = \config::byKey('name');
        $pageContent['objectList'] = JeeObjectManager::buildTree(null, false);
        $pageContent['objectSummary'] = \config::byKey('object:summary');
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/object.html.twig', $pageContent);
    }

    /**
     * Render interact page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of interact page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function interactPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        $interacts = array();
        $pageContent['interactTotal'] = \interactDef::all();
        $interacts[-1] = \interactDef::all(null);
        $interactListGroup = \interactDef::listGroup();
        if (is_array($interactListGroup)) {
            foreach ($interactListGroup as $group) {
                $interacts[$group['group']] = \interactDef::all($group['group']);
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/interact.js';
        $pageContent['interactsList'] = $interacts;
        $pageContent['interactsListGroup'] = $interactListGroup;
        $pageContent['interactDisabledOpacity'] = \nextdom::getConfiguration('eqLogic:style:noactive');
        $pageContent['interactCmdType'] = \nextdom::getConfiguration('cmd:type');
        $pageContent['interactAllUnite'] = CmdManager::allUnite();
        $pageContent['interactJeeObjects'] = JeeObjectManager::all();
        $pageContent['interactEqLogicTypes'] = EqLogicManager::allType();
        $pageContent['interactEqLogics'] = EqLogicManager::all();
        $pageContent['interactEqLogicCategories'] = \nextdom::getConfiguration('eqLogic:category');
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/interact.html.twig', $pageContent);
    }

    /**
     * Render plan page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of plan page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function planPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $planHeader = null;
        $planHeaders = \planHeader::all();
        $planHeadersSendToJS = array();
        foreach ($planHeaders as $planHeader_select) {
            $planHeadersSendToJS[] = array('id' => $planHeader_select->getId(), 'name' => $planHeader_select->getName());
        }
        $pageContent['JS_VARS_RAW']['planHeader'] = Utils::getArrayToJQueryJson($planHeadersSendToJS);
        if (Utils::init('plan_id') == '') {
            foreach ($planHeaders as $planHeader_select) {
                if ($planHeader_select->getId() == $_SESSION['user']->getOptions('defaultDashboardPlan')) {
                    $planHeader = $planHeader_select;
                    break;
                }
            }
        } else {
            foreach ($planHeaders as $planHeader_select) {
                if ($planHeader_select->getId() == Utils::init('plan_id')) {
                    $planHeader = $planHeader_select;
                    break;
                }
            }
        }
        if (!is_object($planHeader) && count($planHeaders) > 0) {
            $planHeader = $planHeaders[0];
        }
        if (!is_object($planHeader)) {
            $pageContent['planHeaderError'] = true;
            $pageContent['JS_VARS']['planHeader_id'] = -1;
        } else {
            $pageContent['planHeaderError'] = false;
            $pageContent['JS_VARS']['planHeader_id'] = $planHeader->getId();
        }

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plan.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plan.html.twig', $pageContent);
    }

    /**
     * Render 3d plan page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of 3d plan page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function plan3dPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $plan3dHeader = null;
        $list_plan3dHeader = \plan3dHeader::all();
        if (Utils::init('plan3d_id') == '') {
            if ($_SESSION['user']->getOptions('defaultDesktopPlan3d') != '') {
                $plan3dHeader = \plan3dHeader::byId($_SESSION['user']->getOptions('defaultDesktopPlan3d'));
            }
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        } else {
            $plan3dHeader = \plan3dHeader::byId(Utils::init('plan3d_id'));
            if (!is_object($plan3dHeader)) {
                $plan3dHeader = $list_plan3dHeader[0];
            }
        }
        if (is_object($plan3dHeader)) {
            $pageContent['JS_VARS']['plan3dHeader_id'] = $plan3dHeader->getId();
            $pageContent['plan3dCurrentHeaderId'] = $plan3dHeader->getId();
        } else {
            $pageContent['JS_VARS']['plan3dHeader_id'] = -1;
        }
        $pageContent['plan3dHeader'] = \plan3dHeader::all();
        $pageContent['plan3dFullScreen'] = Utils::init('fullscreen') == 1;

        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/three.min.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/loaders/LoaderSupport.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/loaders/OBJLoader.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/loaders/MTLLoader.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/controls/TrackballControls.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/controls/OrbitControls.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/renderers/Projector.js';
        $pageContent['JS_END_POOL'][] = '/3rdparty/three.js/objects/Sky.js';
        $pageContent['JS_END_POOL'][] = '/core/js/plan3d.class.js';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/plan3d.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/plan3d.html.twig', $pageContent);
    }


    /**
     * Render market page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of market page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function marketPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        global $NEXTDOM_INTERNAL_CONFIG;

        $sourcesList = [];

        foreach ($NEXTDOM_INTERNAL_CONFIG['nextdom_market']['sources'] as $source) {
            // TODO: Limiter les requêtes
            if (\config::byKey('nextdom_market::' . $source['code']) == 1) {
                $sourcesList[] = $source;
            }
        }

        $pageContent['JS_VARS']['github'] = \config::byKey('github::enable');
        $pageContent['JS_VARS_RAW']['sourcesList'] = Utils::getArrayToJQueryJson($sourcesList);
        $pageContent['JS_VARS']['moreInformationsStr'] = __("Plus d'informations");
        $pageContent['JS_VARS']['updateStr'] = __("Mettre à jour");
        $pageContent['JS_VARS']['updateAllStr'] = __("Voulez-vous mettre à jour tous les plugins ?");
        $pageContent['JS_VARS']['updateThisStr'] = __("Voulez-vous mettre à jour ce plugin ?");
        $pageContent['JS_VARS']['installedPluginStr'] = __("Plugin installé");
        $pageContent['JS_VARS']['updateAvailableStr'] = __("Mise à jour disponible");
        $pageContent['marketSourcesList'] = $sourcesList;
        $pageContent['marketSourcesFilter'] = \config::byKey('nextdom_market::show_sources_filters');

        // Affichage d'un message à un utilisateur
        if (isset($_GET['message'])) {
            $messages = [
                __('La mise à jour du plugin a été effecutée.'),
                __('Le plugin a été supprimé')
            ];

            $messageIndex = intval($_GET['message']);
            if ($messageIndex < count($messages)) {
                \message::add('core', $messages[$messageIndex]);
            }
        }

        $pageContent['CSS_POOL'][] = '/public/css/market.css';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/Market/market.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/market.html.twig', $pageContent);
    }

    /**
     * Render reboot page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of reboot page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function rebootPage(Render $render, array &$pageContent): string
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/reboot.html.twig', $pageContent);
    }

    /**
     * Render for all plugins pages
     *
     * @param Render $render Render engine (unused)
     * @param array $pageContent Page data (unused)
     * @return string Plugin page
     * @throws \Exception
     */
    public static function pluginRoute(Render $render, array &$pageContent): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        \include_file('desktop', $page, 'php', $plugin->getId(), true);
        return ob_get_clean();
    }

}
