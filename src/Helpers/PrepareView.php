<?php
/* This file is part of NextDom Software
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

use NextDom\Enums\GetParams;
use NextDom\Enums\ViewType;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\UserManager;
use NextDom\Managers\ViewManager;
use NextDom\Model\Entity\Plugin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * Classe de support à l'affichage des contenus HTML
 */
class PrepareView
{
    private static $NB_THEME_COLORS = 22;

    /**
     * Used for display special pages that do not need all process (Connection, First Use)
     *
     * @param string $pageCode Code of the page
     * @param array $configs Preloaded configuration data
     * @throws \Exception
     */
    public static function showSpecialPage(string $pageCode, array $configs)
    {
        $pageData = [];
        self::initHeaderData($pageData, $configs);
        echo self::getContentFromRoute('pages_routes.yml', $pageCode, $pageData);
    }

    /**
     * Initialise HTML header data
     *
     * @param $pageData
     * @param $configs
     * @throws \Exception
     */
    private static function initHeaderData(&$pageData, $configs)
    {
        $pageData['PRODUCT_NAME'] = $configs['product_name'];
        $pageData['PRODUCT_CUSTOM_NAME'] = $configs['name'];
        $pageData['PRODUCT_ICON'] = $configs['product_icon'];
        $pageData['PRODUCT_CONNECTION_ICON'] = $configs['product_connection_image'];
        $pageData['AJAX_TOKEN'] = AjaxHelper::getToken();
        $pageData['LANGUAGE'] = $configs['language'];

        self::initJsPool($pageData);
        self::initCssPool($pageData, $configs);
        ob_start();
        require_once(NEXTDOM_ROOT . '/src/Api/icon.inc.php');
        $pageData['CUSTOM_CSS'] = ob_get_clean();
    }

    /**
     * Initialise javascript files to include
     *
     * @param array $pageData Array of the page data
     */
    private static function initJsPool(&$pageData)
    {
        if (file_exists(NEXTDOM_ROOT . '/public/js/base.js')) {
            $pageData['JS_POOL'][] = '/public/js/base.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/autosize/dist/autosize.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js';
            $pageData['JS_END_POOL'][] = '/public/js/desktop/search.js';
        } else {
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.utils/jquery.utils.js';
            $pageData['JS_POOL'][] = 'vendor/node_modules/jquery-ui-dist/jquery-ui.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootstrap/dist/js/bootstrap.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/admin-lte/dist/js/adminlte.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/izitoast/dist/js/iziToast.min.js';
            $pageData['JS_POOL'][] = '/assets/js/desktop/utils.js';
            $pageData['JS_POOL'][] = '/core/js/core.js';
            $pageData['JS_POOL'][] = '/core/js/nextdom.class.js';
            $pageData['JS_POOL'][] = '/core/js/private.class.js';
            $pageData['JS_POOL'][] = '/core/js/eqLogic.class.js';
            $pageData['JS_POOL'][] = '/core/js/cmd.class.js';
            $pageData['JS_POOL'][] = '/core/js/object.class.js';
            $pageData['JS_POOL'][] = '/core/js/scenario.class.js';
            $pageData['JS_POOL'][] = '/core/js/plugin.class.js';
            $pageData['JS_POOL'][] = '/core/js/message.class.js';
            $pageData['JS_POOL'][] = '/core/js/view.class.js';
            $pageData['JS_POOL'][] = '/core/js/config.class.js';
            $pageData['JS_POOL'][] = '/core/js/history.class.js';
            $pageData['JS_POOL'][] = '/core/js/cron.class.js';
            $pageData['JS_POOL'][] = '/core/js/security.class.js';
            $pageData['JS_POOL'][] = '/core/js/update.class.js';
            $pageData['JS_POOL'][] = '/core/js/user.class.js';
            $pageData['JS_POOL'][] = '/core/js/backup.class.js';
            $pageData['JS_POOL'][] = '/core/js/interact.class.js';
            $pageData['JS_POOL'][] = '/core/js/update.class.js';
            $pageData['JS_POOL'][] = '/core/js/plan.class.js';
            $pageData['JS_POOL'][] = '/core/js/log.class.js';
            $pageData['JS_POOL'][] = '/core/js/repo.class.js';
            $pageData['JS_POOL'][] = '/core/js/network.class.js';
            $pageData['JS_POOL'][] = '/core/js/dataStore.class.js';
            $pageData['JS_POOL'][] = '/core/js/cache.class.js';
            $pageData['JS_POOL'][] = '/core/js/report.class.js';
            $pageData['JS_POOL'][] = '/core/js/note.class.js';
            $pageData['JS_POOL'][] = '/core/js/listener.class.js';
            $pageData['JS_POOL'][] = '/core/js/jeedom.class.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootbox/dist/bootbox.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/highstock.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/highcharts-more.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/solid-gauge.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/exporting.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/export-data.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.at.caret/jquery.at.caret.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jwerty/jwerty.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/packery/dist/packery.pkgd.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-lazyload/jquery.lazyload.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/lib/codemirror.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/addon/edit/matchbrackets.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/htmlmixed/htmlmixed.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/clike/clike.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/php/php.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/xml/xml.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/javascript/javascript.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/codemirror/mode/css/css.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jstree/dist/jstree.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/blueimp-file-upload/js/jquery.fileupload.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.multi-column-select/multi-column-select.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-cron/dist/jquery-cron.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/autosize/dist/autosize.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/snapsvg/dist/snap.svg-min.js';
            $pageData['JS_END_POOL'][] = '/public/js/desktop/search.js';
        }
    }

    /**.
     * Initialise CSS file to include
     *
     * @param $pageData
     * @param $configs
     */
    private static function initCssPool(&$pageData, $configs)
    {
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        if (!file_exists(NEXTDOM_DATA . '/public/css/theme.css')) {
            self::generateCssThemFile();
        }
        $pageData['CSS_POOL'][] = '/var/public/css/theme.css';
        // Icônes
        $rootDir = NEXTDOM_ROOT . '/public/icon/';
        foreach (FileSystemHelper::ls($rootDir, '*') as $dir) {
            if (is_dir($rootDir . $dir) && file_exists($rootDir . $dir . '/style.css')) {
                $pageData['CSS_POOL'][] = '/public/icon/' . $dir . 'style.css';
            }
        }

        if (!AuthentificationHelper::isRescueMode()) {

            if (AuthentificationHelper::isConnected()) {

                if (UserManager::getStoredUser() !== null && UserManager::getStoredUser()->getOptions('desktop_highcharts_theme') != '') {
                    $highstockThemeFile = '/vendor/node_modules/highcharts/themes/' . UserManager::getStoredUser()->getOptions('desktop_highcharts_theme') . '.js';
                    $pageData['JS_POOL'][] = $highstockThemeFile;

                }
            }
            if ($configs['enableCustomCss'] == 1) {
                if (file_exists(NEXTDOM_DATA . '/custom/desktop/custom.css')) {
                    $pageData['CSS_POOL'][] = '/var/custom/desktop/custom.css';
                }
                if (file_exists(NEXTDOM_DATA . '/custom/desktop/custom.js')) {
                    $pageData['JS_POOL'][] = '/var/custom/desktop/custom.js';
                }
            }
        } else {
            $pageData['CSS_POOL'][] = '/public/css/rescue.css';
        }
    }

    private static function generateCssThemFile()
    {
        $pageData = [];
        for ($colorIndex = 1; $colorIndex <= self::$NB_THEME_COLORS; ++$colorIndex) {
            $pageData['COLOR' . $colorIndex] = NextDomHelper::getConfiguration('theme:color' . $colorIndex);
        }
        $themeContent = Render::getInstance()->get('commons/theme.html.twig', $pageData);
        // Minification from scratch, TODO: Use real solution
        $themeContent = preg_replace('!/\*.*?\*/!s', '', $themeContent);
        $themeContent = str_replace("\n", "", $themeContent);
        $themeContent = str_replace(";}", "}", $themeContent);
        $themeContent = str_replace(": ", ":", $themeContent);
        $themeContent = str_replace(" {", "{", $themeContent);
        $themeContent = str_replace(", ", ",", $themeContent);
        file_put_contents(NEXTDOM_DATA . '/public/css/theme.css', $themeContent);
    }

    /**
     * Load routes file and show content depends of the route
     *
     * @param string $routesFile Name of the route file in src directory
     * @param string $routeCode Code of the route
     * @param array|null $pageData Array with the content to pass to the render
     *
     * @return string|null Content of the route
     * @throws \NextDom\Exceptions\CoreException
     */
    private static function getContentFromRoute(string $routesFile, string $routeCode, array &$pageData = null)
    {
        $controllerRoute = self::getControllerRouteData($routesFile, $routeCode);
        if ($controllerRoute === null) {
            Router::showError404AndDie();
        } else {
            if (self::userCanUseRoute($controllerRoute)) {
                return self::getContentFromControllerRouteData($controllerRoute, $pageData);
            }
        }
        return null;
    }

    /**
     * Get the controller data of the specified route
     *
     * @param string $routesFile Name of the route file in src directory
     * @param string $routeCode Code of the route
     *
     * @return \Symfony\Component\Routing\Route
     */
    private static function getControllerRouteData(string $routesFile, string $routeCode)
    {
        $routeFileLocator = new FileLocator(NEXTDOM_ROOT . '/src');
        /*
        $router = new \Symfony\Component\Routing\Router(
            new YamlFileLoader($routeFileLocator),
            $routesFile,
            ['cache_dir' => NEXTDOM_DATA . '/cache/routes']
        );
        return $router->getRouteCollection()->get($routeCode);
        var_dump($router->getRouteCollection()->get('dashboard'));
        */
        $yamlLoader = new YamlFileLoader($routeFileLocator);
        $routes = $yamlLoader->load($routesFile);
        return $routes->get($routeCode);
    }

    /**
     * Test if the connected user can use the route
     *
     * @param \Symfony\Component\Routing\Route $controllerRouteData Object of the route
     *
     * @return bool True if the user can use the root
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    private static function userCanUseRoute($controllerRouteData)
    {
        $canUseRoute = true;
        $rights = $controllerRouteData->getCondition();
        if ($rights !== '') {
            if ($rights === 'admin') {
                $canUseRoute = AuthentificationHelper::isConnectedAsAdminOrFail();
            } else {
                $canUseRoute = AuthentificationHelper::isConnectedOrFail();
            }
        }
        return $canUseRoute;
    }

    /**
     * Load routes file and show content depends of the route
     *
     * @param \Symfony\Component\Routing\Route $controllerRouteData
     * @param array|null $pageData Array with the content to pass to the render
     *
     * @return string|null Content of the route
     */
    private static function getContentFromControllerRouteData($controllerRouteData, array &$pageData = null)
    {
        return call_user_func_array($controllerRouteData->getDefaults()['_controller'], [&$pageData]);
    }

    /**
     * Show modal window.
     */
    public static function showModal()
    {
        $plugin = Utils::init('plugin', '');
        $modalCode = Utils::init('modal', '');
        // Show modal from plugin (old way)
        if ($plugin !== '') {
            try {
                FileSystemHelper::includeFile('desktop', $modalCode, 'modal', $plugin, true);
            } catch (\Exception $e) {
                echo '<div class="alert alert-danger div_alert">';
                echo TranslateHelper::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
                echo '</div>';
            }
        } // Show modal from core
        else {
            echo self::getContentFromRoute('modals_routes.yml', $modalCode);
        }
    }

    /**
     * Response to an Ajax request
     *
     * @throws \Exception
     */
    public static function showContentByAjax()
    {
        try {
            $page = Utils::init(GetParams::PAGE);
            $controllerRoute = self::getControllerRouteData('pages_routes.yml', $page);
            if ($controllerRoute === null) {
                if (in_array($page, PluginManager::listPlugin(true, false, true))) {
                    ob_start();
                    FileSystemHelper::includeFile('desktop', $page, 'php', $page, true);
                    echo ob_get_clean();
                } else {
                    Router::showError404AndDie();
                }
            } else {
                if (self::userCanUseRoute($controllerRoute)) {
                    $pageData = [];
                    $pageData['JS_POOL'] = [];
                    $pageData['JS_END_POOL'] = [];
                    $pageData['CSS_POOL'] = [];
                    $pageData['JS_VARS'] = [];
                    $pageData['content'] = self::getContentFromControllerRouteData($controllerRoute, $pageData);
                    Render::getInstance()->show('/layouts/ajax_content.html.twig', $pageData);
                }
            }
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo TranslateHelper::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
        }
    }

    /**
     * Full process render page
     *
     * @param array $configs
     *
     * @throws \Exception
     * @global string $language
     */
    public static function showContent(array $configs)
    {
        global $language;

        $pageData = [];
        $pageData['JS_POOL'] = [];
        $pageData['CSS_POOL'] = [];

        $language = $configs['language'];
        $pageData['HOMELINK'] = self::getHomeLink();
        $page = Utils::init(GetParams::PAGE);

        if ($page == '') {
            Utils::redirect($pageData['HOMELINK']);
        } else {
            $pageData['TITLE'] = ucfirst($page) . ' - ' . $configs['product_name'];
        }

        $currentPlugin = PrepareView::initPluginsData($pageData, $eventsJsPlugin, $configs);
        self::initPluginsEvents($eventsJsPlugin, $pageData);
        self::initHeaderData($pageData, $configs);

        $pageData['JS_VARS'] = [
            'user_id' => UserManager::getStoredUser()->getId(),
            'user_isAdmin' => AuthentificationHelper::isConnectedAsAdmin(),
            'user_login' => UserManager::getStoredUser()->getLogin(),
            'nextdom_Welcome' => $configs['nextdom::Welcome'],
            'nextdom_waitSpinner' => $configs['nextdom::waitSpinner'],
            'notify_status' => $configs['notify::status'],
            'notify_position' => $configs['notify::position'],
            'notify_timeout' => $configs['notify::timeout'],
            'widget_size' => $configs['widget::size'],
            'widget_margin' => $configs['widget::margin'],
            'widget_padding' => $configs['widget::padding'],
            'widget_radius' => $configs['widget::radius'],
        ];
        $pageData['JS_VARS_RAW'] = [
            'userProfils' => Utils::getArrayToJQueryJson(UserManager::getStoredUser()->getOptions()),
        ];

        self::initMenu($pageData, $currentPlugin);

        $baseView = '/layouts/base_dashboard.html.twig';

        try {
            if (!NextDomHelper::isStarted()) {
                $pageData['ALERT_MSG'] = __('NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.');
            }
            $pageData['content'] = self::getContent($pageData, $page, $currentPlugin);
        } catch (\Exception $e) {
            ob_end_clean();
            $pageData['ALERT_MSG'] = Utils::displayException($e);
        }

        $render = Render::getInstance();
        $pageData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);
        $render->show($baseView, $pageData);
    }

    /**
     * Get the current home link
     *
     * @return string Home link
     */
    private static function getHomeLink(): string
    {
        // Détermine la page courante
        $homePage = explode('::', UserManager::getStoredUser()->getOptions('homePage', 'core::dashboard'));
        if (count($homePage) == 2) {
            if ($homePage[0] == 'core') {
                $homeLink = 'index.php?' . http_build_query([
                        GetParams::VIEW_TYPE => ViewType::DESKTOP_VIEW,
                        GetParams::PAGE => $homePage[1],
                    ]);
            } else {
                // TODO : m ???
                $homeLink = 'index.php?' . http_build_query([
                        GetParams::VIEW_TYPE => ViewType::DESKTOP_VIEW,
                        'm' => $homePage[0],
                        GetParams::PAGE => $homePage[1],
                    ]);
            }
            if ($homePage[1] == 'plan' && UserManager::getStoredUser()->getOptions('defaultPlanFullScreen') == 1) {
                $homeLink .= '&fullscreen=1';
            }
        } else {
            $homeLink = 'index.php?v=d&p=dashboard';
        }
        return $homeLink;
    }

    /**
     * Initialize plugins informations necessary for the menu
     *
     * @param $pageData
     * @param $eventsJsPlugin
     * @param $configs
     *
     * @return mixed Current loaded plugin
     *
     * @throws \Exception
     */
    public static function initPluginsData(&$pageData, &$eventsJsPlugin, $configs)
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $currentPlugin = null;
        $eventsJsPlugin = [];
        $categories = PluginManager::getPluginsByCategory(true);

        if (count($categories) > 0) {
            $pageData['PANEL_MENU'] = [];
            $pageData['MENU_PLUGIN'] = [];
            $pageData['MENU_PLUGIN_CATEGORY'] = [];

            foreach ($categories as $categoryCode => $pluginsList) {
                $pageData['MENU_PLUGIN'][$categoryCode] = [];
                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode] = [];

                $icon = '';
                $name = $categoryCode;

                if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode])) {
                    $icon = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode]['icon'];
                    $name = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode]['name'];
                }

                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode]['name'] = Render::getInstance()->getTranslation($name);
                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode]['icon'] = $icon;

                foreach ($pluginsList as $plugin) {
                    $pageData['MENU_PLUGIN'][$categoryCode][] = $plugin;
                    if ($plugin->getId() == Utils::init('m')) {
                        $currentPlugin = $plugin;
                        $pageData['title'] = ucfirst($currentPlugin->getName()) . ' - ' . $configs['product_name'];
                    }
                    if ($plugin->getDisplay() != '' && ConfigManager::bykey('displayDesktopPanel', $plugin->getId(), 0) != 0) {
                        $pageData['PANEL_MENU'][] = $plugin;
                    }
                    if ($plugin->getEventjs() == 1) {
                        $eventJsPlugin[] = $plugin->getId();
                    }
                }
            }
        }
        return $currentPlugin;
    }

    /**
     * Add list of plugins events javascripts files
     *
     * @param $eventsJsPlugin
     * @param $pageData
     */
    private static function initPluginsEvents($eventsJsPlugin, &$pageData)
    {
        if (count($eventsJsPlugin) > 0) {
            foreach ($eventsJsPlugin as $value) {
                try {
                    $pageData['JS_POOL'][] = '/plugins/' . $value . '/public/js/desktop/events.js';
                } catch (\Exception $e) {
                    LogHelper::add($value, 'error', 'Event JS file not found');
                }
            }
        }
    }

    /**
     * Initialise data for the menu
     *
     * @param $pageData
     * @param $currentPlugin
     * @throws \Exception
     */
    private static function initMenu(&$pageData, $currentPlugin)
    {
        $pageData['IS_ADMIN'] = AuthentificationHelper::isConnectedAsAdmin();
        $pageData['CAN_SUDO'] = NextDomHelper::isCapable('sudo');
        $pageData['MENU_NB_MESSAGES'] = MessageManager::nbMessage();
        $pageData['NOTIFY_STATUS'] = ConfigManager::byKey('notify::status');
        if ($pageData['IS_ADMIN']) {
            $pageData['MENU_NB_UPDATES'] = UpdateManager::nbNeedUpdate();
        }
        $pageData['MENU_JEEOBJECT_TREE'] = ObjectManager::buildTree(null, false);
        $pageData['MENU_VIEWS_LIST'] = ViewManager::all();
        $pageData['MENU_PLANS_LIST'] = PlanHeaderManager::all();
        $pageData['MENU_PLANS3D_LIST'] = Plan3dHeaderManager::all();
        if (is_object($currentPlugin) && $currentPlugin->getIssue()) {
            $pageData['MENU_CURRENT_PLUGIN_ISSUE'] = $currentPlugin->getIssue();
        }
        $pageData['MENU_HTML_GLOBAL_SUMMARY'] = ObjectManager::getGlobalHtmlSummary();
        $pageData['PRODUCT_IMAGE'] = ConfigManager::byKey('product_image');
        $pageData['profilsUser'] = UserManager::getStoredUser();
        $pageData['NEXTDOM_VERSION'] = NextDomHelper::getNextdomVersion();
        $pageData['JEEDOM_VERSION'] = NextDomHelper::getJeedomVersion();
        $coreUpdates = UpdateManager::byType('core');
        if (is_array($coreUpdates) && count($coreUpdates) > 0 && is_object($coreUpdates[0])) {
            $version = $coreUpdates[0]->getConfiguration('version');
            if ($version !== '') {
                $pageData['CORE_BRANCH'] = $coreUpdates[0]->getConfiguration('version');
            }
        }
        $pageData['MENU_PLUGIN_HELP'] = Utils::init('m');
        $pageData['MENU_PLUGIN_PAGE'] = Utils::init('p');
    }

    /**
     * Get the content of the route
     *
     * @param array $pageData
     * @param string $page
     * @param Plugin $currentPlugin
     *
     * @return mixed
     * @throws \Exception
     */
    private static function getContent(array &$pageData, string $page, $currentPlugin)
    {
        if ($currentPlugin !== null && is_object($currentPlugin)) {
            ob_start();
            FileSystemHelper::includeFile('desktop', $page, 'php', $currentPlugin->getId(), true);
            return ob_get_clean();
        } else {
            return self::getContentFromRoute('pages_routes.yml', $page, $pageData);
        }
    }

    /**
     * Show the rescue page
     *
     * @param $configs
     * @throws \Exception
     */
    public static function showRescueMode($configs)
    {
        global $language;

        if (!in_array(Utils::init(GetParams::PAGE), ['custom', 'backup', 'cron', 'connection', 'log', 'database', 'editor', 'system'])) {
            $_GET[GetParams::PAGE] = 'system';
        }
        $homeLink = 'index.php?v=d&p=dashboard';

        //TODO: Tests à revoir
        $page = Utils::init(GetParams::PAGE);
        if ($page == '') {
            Utils::redirect($homeLink);
        } else {
            $pageData['TITLE'] = ucfirst($page) . ' - ' . $configs['product_name'];
        }
        $language = $configs['language'];

        // TODO: Remplacer par un include dans twig
        self::initHeaderData($pageData, $configs);
        $render = Render::getInstance();
        $pageData['CSS'] = $render->getCssHtmlTag('/public/css/nextdom.css');
        $pageData['varToJs'] = Utils::getVarsToJS([
            'userProfils' => UserManager::getStoredUser()->getOptions(),
            'user_id' => UserManager::getStoredUser()->getId(),
            'user_isAdmin' => AuthentificationHelper::isConnectedAsAdmin(),
            'user_login' => UserManager::getStoredUser()->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse'], // TODO sans doute inutile
            'serverTZoffsetMin' => Utils::getTZoffsetMin(),
        ]);
        $pageData['JS'] = '';

        $pageData['MENU'] = $render->get('commons/menu_rescue.html.twig');

        if (!NextDomHelper::isStarted()) {
            $pageData['alertMsg'] = __('NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.');
        }
        $pageData['CONTENT'] = self::getContent($pageData, $page, null);

        $render->show('layouts/base_rescue.html.twig', $pageData);
    }

    /**
     * Response to an Ajax request
     *
     * @throws \Exception
     */
    public static function getContentByAjax()
    {
        try {
            AuthentificationHelper::init();
            $page = Utils::init('p');
            $routeFileLocator = new FileLocator(NEXTDOM_ROOT . '/src');
            $yamlLoader = new YamlFileLoader($routeFileLocator);
            $routes = $yamlLoader->load('routes.yml');
            $controllerRoute = $routes->get($page);
            if ($controllerRoute === null) {
                if (in_array($page, PluginManager::listPlugin(true, false, true))) {
                    ob_start();
                    FileSystemHelper::includeFile('desktop', $page, 'php', $page, true);
                    echo ob_get_clean();
                } else {
                    Router::showError404AndDie();
                }
            } else {
                $render = Render::getInstance();
                $pageContent = [];
                $pageContent['JS_POOL'] = [];
                $pageContent['JS_END_POOL'] = [];
                $pageContent['CSS_POOL'] = [];
                $pageContent['JS_VARS'] = [];
                $pageContent['content'] = call_user_func_array($controllerRoute->getDefaults()['_controller'], [$render, &$pageContent]);
                $render->show('/layouts/ajax_content.html.twig', $pageContent);
            }
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
        }
    }

}
