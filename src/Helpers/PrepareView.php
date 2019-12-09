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

use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\GetParams;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\ViewType;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\MessageManager;
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
    private $currentConfig = [];

    /**
     * Read configuration
     *
     * @throws \Exception
     */
    public function initConfig()
    {
        $this->currentConfig = ConfigManager::byKeys([
            'language',
            'nextdom::firstUse',
            'nextdom::Welcome',
            'notify::status',
            'notify::position',
            'notify::timeout',
            'widget::size',
            'widget::margin',
            'widget::padding',
            'widget::radius',
            'default_bootstrap_theme']);
    }

    /**
     * Test if first use page must be showed
     *
     * @return bool
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function firstUseAlreadyShowed()
    {
        $result = false;
        if (isset($this->currentConfig['nextdom::firstUse']) && $this->currentConfig['nextdom::firstUse'] == 0) {
            $result = true;
        } else {
            // Prevent F5 bug on second step
            $user = UserManager::byLogin('admin');
            if (is_object($user) && $user->getPassword() !== Utils::sha512('admin')) {
                ConfigManager::save('nextdom::firstUse', 0);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Used for display special pages that do not need all process (Connection, First Use)
     *
     * @param string $pageCode Code of the page
     *
     * @throws \Exception
     */
    public function showSpecialPage(string $pageCode)
    {
        $pageData = [];
        $this->initHeaderData($pageData);
        $pageData['JS_VARS_RAW']['serverDatetime'] = Utils::getMicrotime();
        echo $this->getContentFromRoute('pages_routes.yml', $pageCode, $pageData);
    }

    /**
     * Initialise HTML header data
     *
     * @param $pageData
     *
     * @throws \Exception
     */
    private function initHeaderData(&$pageData)
    {
        $pageData['AJAX_TOKEN'] = AjaxHelper::getToken();
        $pageData['LANGUAGE'] = $this->currentConfig['language'];

        $this->initJsPool($pageData);
        $this->initCssPool($pageData);
        ob_start();
        require_once(NEXTDOM_ROOT . '/src/Api/icon.inc.php');
        $pageData['CUSTOM_CSS'] = ob_get_clean();
    }

    /**
     * Initialise javascript files to include
     *
     * @param array $pageData Array of the page data
     */
    private function initJsPool(&$pageData)
    {
        if (file_exists(NEXTDOM_ROOT . '/public/js/base.js')) {
            // Loading of base.js that contain all JS in the else below via gen_assets
            $pageData['JS_POOL'][] = '/public/js/base.js';
            // Loading dynamic libraries, must be here
            $pageData['JS_POOL'][] = '/vendor/node_modules/autosize/dist/autosize.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js';
        } else {
            // If base.js problem, loading JS files dynamicly
            // First respect this files and their order to prevent conflicts
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-ui-dist/jquery-ui.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootstrap/dist/js/bootstrap.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/admin-lte/dist/js/adminlte.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/pace-js/pace.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/izitoast/dist/js/iziToast.min.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.utils/jquery.utils.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.at.caret/jquery.at.caret.min.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.multi-column-select/multi-column-select.js';
            $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.ui-touch-punch/jquery.ui.touch-punch.min.js';

            // Add NextDom core JS
            $pageData['JS_POOL'][] = '/assets/js/core/core.js';
            $pageData['JS_POOL'][] = '/assets/js/core/nextdom.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/private.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/eqLogic.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/cmd.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/object.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/scenario.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/plugin.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/message.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/view.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/config.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/history.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/cron.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/security.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/update.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/user.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/backup.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/interact.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/update.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/plan.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/log.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/repo.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/network.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/dataStore.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/cache.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/report.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/note.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/listener.class.js';
            $pageData['JS_POOL'][] = '/assets/js/core/jeedom.class.js';

            // Then NextDom JS files
            $pageData['JS_POOL'][] = '/public/js/desktop/conflicts.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/loads.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/inits.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/gui.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/utils.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/search.js';

            // And libraries JS
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootbox/dist/bootbox.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/highstock.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/highcharts-more.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/solid-gauge.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/exporting.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/highcharts/modules/export-data.js';
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
            $pageData['JS_POOL'][] = '/vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/blueimp-file-upload/js/jquery.fileupload.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-cron/dist/jquery-cron.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/moment/moment.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/vivagraphjs/dist/vivagraph.min.js';

            // Then Factory framwework files
            $pageData['JS_POOL'][] = '/public/js/factory/NextDomUIDGenerator.js';
            $pageData['JS_POOL'][] = '/public/js/factory/NextDomElement.js';
            $pageData['JS_POOL'][] = '/public/js/factory/NextDomEnum.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/A.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Br.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Button.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Div.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/DivWithTooltip.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/HorizontalLayout.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/IFA.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/InputText.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Label.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Space.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Table.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Tbody.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Td.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/TextNode.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Th.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Thead.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/Tr.js';
            $pageData['JS_POOL'][] = '/public/js/factory/elements/VerticalLayout.js';

            // Finally dynamic libraries, must be here
            $pageData['JS_POOL'][] = '/vendor/node_modules/autosize/dist/autosize.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js';
        }
    }

    /**.
     * Initialise CSS file to include
     *
     * @param $pageData
     * @throws \Exception
     */
    private function initCssPool(&$pageData)
    {
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][] = '/public/css/themes/' . ConfigManager::byKey('nextdom::user-theme', 'core', 'dark-nextdom') . '.css';
        $rootDir = NEXTDOM_ROOT . '/public/icon/';
        foreach (FileSystemHelper::ls($rootDir, '*') as $dir) {
            if (is_dir($rootDir . $dir) && file_exists($rootDir . $dir . '/style.css')) {
                $pageData['CSS_POOL'][] = '/public/icon/' . $dir . 'style.css';
            }
        }
        if (AuthentificationHelper::isConnected()) {
            if (UserManager::getStoredUser() !== null && UserManager::getStoredUser()->getOptions('desktop_highcharts_theme') != '') {
                $highstockThemeFile = '/vendor/node_modules/highcharts/themes/' . UserManager::getStoredUser()->getOptions('desktop_highcharts_theme') . '.js';
                $pageData['JS_POOL'][] = $highstockThemeFile;
            }
        }
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
    private function getContentFromRoute(string $routesFile, string $routeCode, array &$pageData = null)
    {
        $controllerRoute = $this->getControllerRouteData($routesFile, $routeCode);
        if ($controllerRoute === null) {
            Router::showError404AndDie();
        } else {
            if ($this->userCanUseRoute($controllerRoute)) {
                return $this->getContentFromControllerRouteData($controllerRoute, $pageData);
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
    private function getControllerRouteData(string $routesFile, string $routeCode)
    {
        $routeFileLocator = new FileLocator(NEXTDOM_ROOT . '/src');
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
    private function userCanUseRoute($controllerRouteData)
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
    private function getContentFromControllerRouteData($controllerRouteData, array &$pageData = null)
    {
        return call_user_func_array($controllerRouteData->getDefaults()['_controller'], [&$pageData]);
    }

    /**
     * Show modal window.
     */
    public function showModal()
    {
        $plugin = Utils::init(AjaxParams::PLUGIN, '');
        $modalCode = Utils::init(AjaxParams::MODAL, '');
        // Show modal from plugin (old way)
        if ($plugin !== '') {
            try {
                FileSystemHelper::includeFile('desktop', $modalCode, 'modal', $plugin, true);
            } catch (\Exception $e) {
                echo '<section class="alert-header">';
                echo '<div class="alert alert-danger">';
                echo TranslateHelper::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
                echo '</div>';
                echo '</section>';
            }
        } // Show modal from core
        else {
            echo $this->getContentFromRoute('modals_routes.yml', $modalCode);
        }
    }

    /**
     * Response to an Ajax request
     *
     * @throws \Exception
     */
    public function showContentByAjax()
    {
        try {
            $page = Utils::init(GetParams::PAGE);
            $controllerRoute = $this->getControllerRouteData('pages_routes.yml', $page);
            if ($controllerRoute === null) {
                if (in_array($page, PluginManager::listPlugin(true, false, true))) {
                    ob_start();
                    FileSystemHelper::includeFile('desktop', $page, 'php', $page, true);
                    echo ob_get_clean();
                } else {
                    Router::showError404AndDie();
                }
            } else {
                if ($this->userCanUseRoute($controllerRoute)) {
                    $pageData = [];
                    $pageData['JS_POOL'] = [];
                    $pageData['JS_END_POOL'] = [];
                    $pageData['CSS_POOL'] = [];
                    $pageData['JS_VARS'] = [];
                    $pageData['content'] = $this->getContentFromControllerRouteData($controllerRoute, $pageData);
                    Render::getInstance()->show('/layouts/ajax_content.html.twig', $pageData);
                }
            }
        } catch (\Exception $e) {
            echo '<section class="alert-header">';
            echo '<div class="alert alert-danger">';
            echo TranslateHelper::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
            echo '</section>';
        }
    }

    /**
     * Full process render page
     *
     * @throws \Exception
     * @global string $language
     */
    public function showContent()
    {
        global $language;

        $pageData = [];
        $pageData['JS_POOL'] = [];
        $pageData['CSS_POOL'] = [];

        $language = $this->currentConfig['language'];
        $pageData['HOMELINK'] = $this->getHomeLink();
        $page = Utils::init(GetParams::PAGE);

        if ($page == '') {
            Utils::redirect($pageData['HOMELINK']);
        } else {
            $pageData['TITLE'] = ucfirst($page) . ' - NextDom';
        }

        $currentPlugin = $this->initPluginsData($pageData, $eventsJsPlugin);
        $this->initPluginsEvents($eventsJsPlugin, $pageData);
        $this->initHeaderData($pageData);

        $currentJeeObject = JeeObjectManager::getRootObjects();
        $currentJeeObjectId = '';
        if (!empty($currentJeeObject)) {
            $currentJeeObjectId = $currentJeeObject->getId();
        }

        $pageData['JS_VARS'] = [
            'user_id' => UserManager::getStoredUser()->getId(),
            'user_isAdmin' => AuthentificationHelper::isConnectedAsAdmin(),
            'user_login' => UserManager::getStoredUser()->getLogin(),
            'nextdom_Welcome' => $this->currentConfig['nextdom::Welcome'],
            'notify_status' => $this->currentConfig['notify::status'],
            'notify_position' => $this->currentConfig['notify::position'],
            'notify_timeout' => $this->currentConfig['notify::timeout'],
            'widget_size' => $this->currentConfig['widget::size'],
            'widget_margin' => $this->currentConfig['widget::margin'],
            'widget_padding' => $this->currentConfig['widget::padding'],
            'widget_radius' => $this->currentConfig['widget::radius'],
            'root_object_id' => $currentJeeObjectId
        ];
        $pageData['JS_VARS_RAW'] = [
            'userProfils' => Utils::getArrayToJQueryJson(UserManager::getStoredUser()->getOptions()),
            'serverDatetime' => Utils::getMicrotime()
        ];

        $this->initMenu($pageData, $currentPlugin);

        $baseView = '/layouts/base_dashboard.html.twig';

        try {
            if (!NextDomHelper::isStarted()) {
                $pageData['ALERT_MSG'] = __('NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.');
            }
            $pageData['content'] = $this->getContent($pageData, $page, $currentPlugin);
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
     *
     * @throws \Exception
     */
    private function getHomeLink(): string
    {
        // Détermine la page courante
        $defaultDashboardObjectId = '';
        $defaultDashboardObjectName = UserManager::getStoredUser()->getOptions('defaultDashboardObject');
        $defaultDashboardObject = JeeObjectManager::byId($defaultDashboardObjectName);
        if (!empty($defaultDashboardObject)) {
            $defaultDashboardObjectId = $defaultDashboardObject->getId();
        }
        $homePage = explode('::', UserManager::getStoredUser()->getOptions('homePage', 'core::dashboard'));
        if (count($homePage) == 2) {
            if ($homePage[0] == 'core') {
                $homeLink = 'index.php?' . http_build_query([
                        GetParams::VIEW_TYPE => ViewType::DESKTOP_VIEW,
                        GetParams::PAGE => $homePage[1],
                    ]);
                if ($defaultDashboardObjectId != '') {
                    $homeLink .= '&object_id=' . $defaultDashboardObjectId;
                }
            } else {
                // @TODO : m ???
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
            if ($defaultDashboardObjectId != '') {
                $homeLink .= '&object_id=' . $defaultDashboardObjectId;
            }
        }
        return $homeLink;
    }

    /**
     * Initialize plugins informations necessary for the menu
     *
     * @param $pageData
     * @param $eventsJsPlugin
     *
     * @return mixed Current loaded plugin
     *
     * @throws \Exception
     */
    public function initPluginsData(&$pageData, &$eventsJsPlugin)
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

                if (isset($NEXTDOM_INTERNAL_CONFIG[NextDomObj::PLUGIN][Common::CATEGORY][$categoryCode])) {
                    $icon = $NEXTDOM_INTERNAL_CONFIG[NextDomObj::PLUGIN][Common::CATEGORY][$categoryCode][Common::ICON];
                    $name = $NEXTDOM_INTERNAL_CONFIG[NextDomObj::PLUGIN][Common::CATEGORY][$categoryCode][Common::NAME];
                }

                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode][Common::NAME] = Render::getInstance()->getTranslation($name);
                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode][Common::ICON] = $icon;

                /** @var Plugin $plugin */
                foreach ($pluginsList as $plugin) {
                    $pageData['MENU_PLUGIN'][$categoryCode][] = $plugin;
                    if ($plugin->getId() == Utils::init('m')) {
                        $currentPlugin = $plugin;
                        $pageData['title'] = ucfirst($currentPlugin->getName()) . ' - NextDom';
                    }
                    if ($plugin->getDisplay() != '' && ConfigManager::bykey('displayDesktopPanel', $plugin->getId(), 0) != 0) {
                        $pageData['PANEL_MENU'][] = $plugin;
                    }
                    if ($plugin->getEventjs() == 1) {
                        $eventsJsPlugin[] = $plugin->getId();
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
     * @throws \Exception
     */
    private function initPluginsEvents($eventsJsPlugin, &$pageData)
    {
        if (count($eventsJsPlugin) > 0) {
            $pageData['PLUGINS_JS_POOL'] = [];
            foreach ($eventsJsPlugin as $pluginId) {
                $eventJsFile = '/plugins/' . $pluginId . '/desktop/js/event.js';
                if (file_exists(NEXTDOM_ROOT . $eventJsFile)) {
                    $pageData['PLUGINS_JS_POOL'][] = $eventJsFile;
                }
            }
        }
    }

    /**
     * Initialise data for the menu
     *
     * @param array $pageData
     * @param Plugin $currentPlugin
     * @throws \Exception
     */
    private function initMenu(&$pageData, $currentPlugin)
    {
        $pageData['IS_ADMIN'] = AuthentificationHelper::isConnectedAsAdmin();
        $pageData['CAN_SUDO'] = NextDomHelper::isCapable('sudo');
        $pageData['SHOW_MOBILE_IN_MENU'] = is_dir(NEXTDOM_ROOT . '/mobile');
        $pageData['MENU_NB_MESSAGES'] = MessageManager::nbMessage();
        $pageData['NOTIFY_STATUS'] = ConfigManager::byKey('notify::status');
        if ($pageData['IS_ADMIN']) {
            $pageData['MENU_NB_UPDATES'] = UpdateManager::nbNeedUpdate();
        }
        $pageData['MENU_JEEOBJECT_TREE'] = JeeObjectManager::buildTree(null, false);
        $pageData['MENU_VIEWS_LIST'] = ViewManager::all();
        $pageData['MENU_PLANS_LIST'] = PlanHeaderManager::all();
        $pageData['MENU_PLANS3D_LIST'] = Plan3dHeaderManager::all();
        if (is_object($currentPlugin) && $currentPlugin->getIssue()) {
            $pageData['MENU_CURRENT_PLUGIN_ISSUE'] = $currentPlugin->getIssue();
        }
        $pageData['MENU_HTML_GLOBAL_SUMMARY'] = JeeObjectManager::getGlobalHtmlSummary();
        $pageData['profilsUser'] = UserManager::getStoredUser();
        $pageData['PROFIL_AVATAR'] = UserManager::getStoredUser()->getOptions('avatar');
        $pageData['PROFIL_LOGIN'] = UserManager::getStoredUser()->getLogin();
        $pageData['NEXTDOM_ICON'] = ConfigManager::byKey('nextdom::user-icon');
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
    private function getContent(array &$pageData, string $page, $currentPlugin)
    {
        if ($currentPlugin !== null && is_object($currentPlugin)) {
            ob_start();
            FileSystemHelper::includeFile('desktop', $page, 'php', $currentPlugin->getId(), true);
            return ob_get_clean();
        } else {
            return $this->getContentFromRoute('pages_routes.yml', $page, $pageData);
        }
    }

    /**
     * Response to an Ajax request
     *
     * @throws \Exception
     */
    public function getContentByAjax()
    {
        try {
            $page = Utils::init(GetParams::PAGE);
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
            echo '<section class="alert-header">';
            echo '<div class="alert alert-danger">';
            echo \translate::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
            echo '</section>';
        }
    }

}
