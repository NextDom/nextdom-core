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

use NextDom\Exceptions\CoreException;
use NextDom\Managers\AjaxManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Helpers\ModalsController;
use NextDom\Helpers\PagesController;
use NextDom\Helpers\Router;
use NextDom\Managers\ConfigManager;

/**
 * Classe de support à l'affichage des contenus HTML
 */
class PrepareView
{
    private static $NB_THEME_COLORS = 9;

    public static function showFirstUsePage($configs)
    {
        $pageData = [];
        $pageData['JS_POOL'] = [];
        $pageData['JS_END_POOL'] = [];
        $pageData['CSS_POOL'] = [];
        $pageData['TITLE'] = '1ere connexion';
        $pageData['JS_VARS'] = [
            'notify_status'      => $configs['notify::status'],
            'notify_position'    => $configs['notify::position'],
            'notify_timeout'     => $configs['notify::timeout'],
        ];
        $render = Render::getInstance();
        self::initHeaderData($pageData, $configs);
        //TODO: Vérifier ça
        $pageData['CSS_POOL'][]    = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][] = '/public/css/firstUse.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/firstUse.js';

        $render->show('desktop/firstUse.html.twig', $pageData);
    }

    public static function showConnectionPage($configs)
    {
        $pageData                = [];
        $pageData['JS_POOL']     = [];
        $pageData['JS_END_POOL'] = [];
        $pageData['CSS_POOL']    = [];
        $pageData['TITLE']       = 'Connexion';
        $render                  = Render::getInstance();
        self::initHeaderData($pageData, $configs);
        $pageData['JS_END_POOL'][] = '/vendor/node_modules/admin-lte/dist/js/adminlte.min.js';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/connection.js';

        $render->show('desktop/connection.html.twig', $pageData);
    }

    /**
     * @param $configs
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function showRescueMode($configs)
    {
        global $language;

        if (!in_array(Utils::init('p'), array('custom', 'backup', 'cron', 'connection', 'log', 'database', 'editor', 'system'))) {
            $_GET['p'] = 'system';
        }
        $homeLink = 'index.php?v=d&p=dashboard';
        $page = '';
        //TODO: Tests à revoir
        if (Utils::init('p') == '') {
            redirect($homeLink);
        } else {
            $page = init('p');
            $pageData['TITLE'] = ucfirst($page) . ' - ' . $configs['product_name'];
        }
        $language = $configs['language'];

        // TODO: Remplacer par un include dans twig
        $render = Render::getInstance();
        self::initHeaderData($pageData, $configs);

        $pageData['CSS'] = $render->getCssHtmlTag('/public/css/nextdom.css');
        $pageData['varToJs'] = Utils::getVarsToJS(array(
            'userProfils' => $_SESSION['user']->getOptions(),
            'user_id' => $_SESSION['user']->getId(),
            'user_isAdmin' => Status::isConnectAdmin(),
            'user_login' => $_SESSION['user']->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse']
        ));
        $pageData['JS'] = '';

        $pageData['MENU'] = $render->get('commons/menu_rescue.html.twig');

        if (!NextDomHelper::isStarted()) {
            $pageData['alertMsg'] = \__('NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.');
        }
        $pageData['CONTENT'] = self::getContent($render, $pageData, $page, null);

        $render = Render::getInstance();
        $render->show('layouts/base_rescue.html.twig', $pageData);
    }

    /**
     *
     * @param array $configs
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @global string $language
     */
    public static function showContent(array $configs)
    {
        global $language;

        $pageData             = [];
        $pageData['JS_POOL']  = [];
        $pageData['CSS_POOL'] = [];
        $page = '';

        $language = $configs['language'];
        $pageData['HOMELINK'] = self::getHomeLink();
        //TODO: Tests à revoir
        if (Utils::init('p') == '') {
            redirect($pageData['HOMELINK'] );
        } else {
            $page = Utils::init('p');
            $pageData['TITLE'] = ucfirst($page) . ' - ' . $configs['product_name'];
        }

        $render = Render::getInstance();
        $currentPlugin = PrepareView::initPluginsData($render, $pageData, $eventsJsPlugin, $configs);
        self::initPluginsEvents($eventsJsPlugin, $pageData);
        self::initHeaderData($pageData, $configs);

        $pageData['JS_VARS'] = [
            'user_id'            => $_SESSION['user']->getId(),
            'user_isAdmin'       => Status::isConnectAdmin(),
            'user_login'         => $_SESSION['user']->getLogin(),
            'nextdom_Welcome'    => $configs['nextdom::Welcome'],
            'notify_status'      => $configs['notify::status'],
            'notify_position'    => $configs['notify::position'],
            'notify_timeout'     => $configs['notify::timeout'],
            'widget_size'        => $configs['widget::size'],
            'widget_margin'      => $configs['widget::margin'],
            'widget_padding'     => $configs['widget::padding'],
            'widget_radius'      => $configs['widget::radius'],
        ];
        $pageData['JS_VARS_RAW'] = [
            'userProfils' => Utils::getArrayToJQueryJson($_SESSION['user']->getOptions()),
        ];

        self::initMenu($pageData, $currentPlugin);

        $baseView = '/layouts/base_dashboard.html.twig';

        try {
            if (!NextDomHelper::isStarted()) {
                $pageData['ALERT_MSG'] = 'NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            $pageData['content'] = self::getContent($render, $pageData, $page, $currentPlugin);
        } catch (CoreException $e) {
            ob_end_clean();
            $pageData['ALERT_MSG'] = Utils::displayException($e);
        }

        $pageData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show($baseView, $pageData);

    }

    /**
     * TODO: Gros bordel à refaire
     * @return string
     */
    private static function getHomeLink()
    {
        // Détermine la page courante
        $homePage = explode('::', $_SESSION['user']->getOptions('homePage', 'core::dashboard'));
        if (count($homePage) == 2) {
            if ($homePage[0] == 'core') {
                $homeLink = 'index.php?v=d&p=' . $homePage[1];
            } else {
                $homeLink = 'index.php?v=d&m=' . $homePage[0] . '&p=' . $homePage[1];
            }
            if ($homePage[1] == 'plan' && $_SESSION['user']->getOptions('defaultPlanFullScreen') == 1) {
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
     * @param Render $render Render engine
     *
     * @param $pageData
     * @param $eventsJsPlugin
     * @param $configs
     * @return mixed Current loaded plugin
     * @throws \Exception
     */
    public static function initPluginsData(Render $render, &$pageData, &$eventsJsPlugin, $configs)
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $currentPlugin = null;
        $eventsJsPlugin = [];
        $categories = PluginManager::getPluginsByCategory(true);

        if (count($categories) > 0) {
            $pageData['PANEL_MENU']           = [];
            $pageData['MENU_PLUGIN']          = [];
            $pageData['MENU_PLUGIN_CATEGORY'] = [];

            foreach ($categories as $categoryCode => $pluginsList) {
                $pageData['MENU_PLUGIN'][$categoryCode]          = [];
                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode] = [];

                $icon = '';
                $name = $categoryCode;

                if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode])) {
                    $icon = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode]['icon'];
                    $name = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryCode]['name'];
                }

                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode]['name'] = $render->getTranslation($name);
                $pageData['MENU_PLUGIN_CATEGORY'][$categoryCode]['icon'] = $icon;

                foreach ($pluginsList as $plugin) {
                    $pageData['MENU_PLUGIN'][$categoryCode][] = $plugin;
                    if ($plugin->getId() == Utils::init('m')) {
                        $currentPlugin = $plugin;
                        $pageData['title'] = ucfirst($currentPlugin->getName()) . ' - ' . $configs['product_name'];
                    }
                    // TODO: C'est quoi ?
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
     * @param $eventsJsPlugin
     * @param $pageData
     */
    private static function initPluginsEvents($eventsJsPlugin, &$pageData)
    {
        if (count($eventsJsPlugin) > 0) {
            foreach ($eventsJsPlugin as $value) {
                try {
                    $pageData['JS_POOL'][] = '/plugins/'.$value.'/public/js/desktop/events.js';
                } catch (CoreException $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
    }

    /**
     * @param $pageData
     * @param $currentPlugin
     * @throws \Exception
     */
    private static function initMenu(&$pageData, $currentPlugin)
    {
        $pageData['IS_ADMIN']                 = Status::isConnectAdmin();
        $pageData['CAN_SUDO']                 = NextDomHelper::isCapable('sudo');
        $pageData['MENU_NB_MESSAGES']         = \message::nbMessage();
        $pageData['NOTIFY_STATUS']           = ConfigManager::byKey('notify::status');
        if ($pageData['IS_ADMIN']) {
            $pageData['MENU_NB_UPDATES'] = UpdateManager::nbNeedUpdate();
        }
        $pageData['MENU_JEEOBJECT_TREE'] = JeeObjectManager::buildTree(null, false);
        $pageData['MENU_VIEWS_LIST']     = \view::all();
        $pageData['MENU_PLANS_LIST']     = \planHeader::all();
        $pageData['MENU_PLANS3D_LIST']   = \plan3dHeader::all();
        if (is_object($currentPlugin) && $currentPlugin->getIssue()) {
            $pageData['MENU_CURRENT_PLUGIN_ISSUE'] = $currentPlugin->getIssue();
        }
        $pageData['MENU_HTML_GLOBAL_SUMMARY'] = JeeObjectManager::getGlobalHtmlSummary();
        $pageData['PRODUCT_IMAGE']            = ConfigManager::byKey('product_image');
        $pageData['USER_ISCONNECTED']         = $_SESSION['user']->is_Connected();
        $pageData['USER_AVATAR']              = $_SESSION['user']->getOptions('avatar');
        $pageData['USER_LOGIN']               = $_SESSION['user']->getLogin();
        $pageData['NEXTDOM_VERSION']          = NextDomHelper::getNextdomVersion();
        $pageData['JEEDOM_VERSION']          = NextDomHelper::getJeedomVersion();
        $pageData['MENU_PLUGIN_HELP']         = Utils::init('m');
        $pageData['MENU_PLUGIN_PAGE']         = Utils::init('p');
    }

    /**
     * @param $pageData
     * @param $configs
     * @throws \Exception
     */
    private static function initHeaderData(&$pageData, $configs)
    {
        // TODO: Remplacer par un include dans twig
        $pageData['PRODUCT_NAME'] = $configs['product_name'];
        $pageData['PRODUCT_ICON'] = $configs['product_icon'];
        $pageData['PRODUCT_CONNECTION_ICON'] = $configs['product_connection_image'];
        $pageData['AJAX_TOKEN'] = AjaxManager::getToken();
        $pageData['LANGUAGE'] = $configs['language'];
        for ($colorIndex = 1; $colorIndex <= self::$NB_THEME_COLORS; ++$colorIndex) {
            $pageData['COLOR'.$colorIndex] = \nextdom::getConfiguration('theme:color'.$colorIndex);
        }

        self::initJsPool($pageData);
        self::initCssPool($pageData, $configs);
        // TODO: A virer
        ob_start();
        \include_file('core', 'icon.inc', 'php');
        $pageData['CUSTOM_CSS'] = ob_get_clean();
    }

    /**
     * @param $pageData
     */
    private static function initJsPool(&$pageData)
    {
        if (file_exists(NEXTDOM_ROOT . '/public/js/base.js')) {
            $pageData['JS_POOL'][] = '/public/js/base.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/autosize/dist/autosize.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js';
        } else {
            $pageData['JS_POOL'][] = 'vendor/node_modules/jquery-ui-dist/jquery-ui.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootstrap/dist/js/bootstrap.min.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/admin-lte/dist/js/adminlte.min.js';
            $pageData['JS_POOL'][] = '/public/3rdparty/jquery.utils/jquery.utils.js';
            $pageData['JS_POOL'][] = '/vendor/node_modules/izitoast/dist/js/iziToast.min.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/utils.js';
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
            $pageData['JS_POOL'][] = '/vendor/node_modules/bootbox/bootbox.min.js';
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
        }
    }

    /**.
     * @param $pageData
     * @param $configs
     */
    private static function initCssPool(&$pageData, $configs)
    {
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        // Icônes
        $rootDir = NEXTDOM_ROOT . '/public/icon/';
        foreach (FileSystemHelper::ls($rootDir, '*') as $dir) {
            if (is_dir($rootDir . $dir) && file_exists($rootDir . $dir . '/style.css')) {
                $pageData['CSS_POOL'][] = '/public/icon/' . $dir . 'style.css';
            }
        }

        if (!Status::isRescueMode()) {
            if (!Status::isConnect()) {
                if (isset($_SESSION['user']) && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                    $highstockThemeFile = '/vendor/highcharts/themes/' . $_SESSION['user']->getOptions('desktop_highcharts_theme') . '.js';
                    if (file_exists($highstockThemeFile)) {
                        $pageData['JS_POOL'][] = $highstockThemeFile;
                    }
                }
            }
            if ($configs['enableCustomCss'] == 1) {
                if (file_exists(NEXTDOM_ROOT . '/var/custom/desktop/custom.css')) {
                    $pageData['CSS_POOL'][] = '/var/custom/desktop/custom.css';
                }
                if (file_exists(NEXTDOM_ROOT . '/var/custom/desktop/custom.js')) {
                    $pageData['JS_POOL'][] = '/var/custom/desktop/custom.js';
                }
            }
        } else {
            $pageData['CSS_POOL'][] = '/public/css/rescue.css';
        }
    }

    /**
     * @param Render $render
     * @param array $pageContent
     * @param string $page
     * @param $currentPlugin
     *
     * @return mixed
     * @throws \Exception
     */
    private static function getContent(Render $render, array &$pageContent, string $page, $currentPlugin) {
        if ($currentPlugin !== null && is_object($currentPlugin)) {
            ob_start();
            \include_file('desktop', $page, 'php', $currentPlugin->getId(), true);
            return ob_get_clean();
        } else {
            $controllerRoute = PagesController::getRoute($page);
            if ($controllerRoute === null) {
                // Vérifie que l'utilisateur n'essaie pas de sortir
                $purgedPage = preg_replace('/[^a-z0-9_-]/i', '', $page);
                if (file_exists(NEXTDOM_ROOT . '/desktop/' . $purgedPage)) {
                    ob_start();
                    \include_file('desktop', $page, 'php', '', true);
                    return ob_get_clean();
                } else {
                    Router::showError404AndDie();
                    return null;
                }
            } else {
                $controller = new $controllerRoute();
                return $controller->get($render, $pageContent);
            }
        }
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
            $controllerRoute = PagesController::getRoute($page);
            if ($controllerRoute === null) {
                Router::showError404AndDie();
            } else {
                $render = Render::getInstance();
                $pageContent = [];
                $pageContent['JS_POOL'] = [];
                $pageContent['JS_END_POOL'] = [];
                $pageContent['CSS_POOL'] = [];
                $pageContent['JS_VARS'] = [];
                $controller = new $controllerRoute();
                $pageContent['content'] = $controller->get($render, $pageContent);
                $render->show('/layouts/ajax_content.html.twig', $pageContent);
            }
        } catch (CoreException $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
        }
    }

    public static function showModal()
    {
        AuthentificationHelper::init();
        $plugin = Utils::init('plugin', '');
        $modalCode = Utils::init('modal', '');
        // Affichage d'un modal appartenant à un plugin
        if ($plugin != '') {
            try {
                \include_file('desktop', $modalCode, 'modal', $plugin, true);
            } catch (CoreException $e) {
                echo '<div class="alert alert-danger div_alert">';
                echo \translate::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
                echo '</div>';
            }
        }
        // Affichage d'un modal du core
        else {
            $modalRoute = ModalsController::getRoute($modalCode);
            if ($modalRoute === null) {

                try {
                    \include_file('desktop', $modalCode, 'modal', Utils::init('plugin'), true);
                } catch (CoreException $e) {
                    echo '<div class="alert alert-danger div_alert">';
                    echo \translate::exec(Utils::displayException($e), 'desktop/' . Utils::init('p') . '.php');
                    echo '</div>';
                }
            } else {
                $render = Render::getInstance();
                try {
                    $modale = new $modalRoute();
                    echo $modale->get($render);
                } catch (CoreException $ex) {
                    echo '<div class="alert alert-danger div_alert">';
                    echo '<p>Une erreur s\'est produite, impossible d\'afficher le contenu de la modale. Erreur : '. $ex->getMessage() .'</p>';
                    echo '</div>';
                }
                

            }
        }
    }

}
