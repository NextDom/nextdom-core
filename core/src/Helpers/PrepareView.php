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

use NextDom\Helpers\Status;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\JeeObjectManager;

/**
 * Classe de support à l'affichage des contenus HTML
 */
class PrepareView
{
    /**
     * @var string Données HTML du menu
     */
    private static $pluginMenu;

    /**
     * @var string Données HTML de TODO: ????
     */
    private static $panelMenu;

    private static $eventJsPlugin;

    private static $title;

    private static $pageData = [];

    /**
     * Affiche le contenu de l'en-tête
     */
    public static function showHeader()
    {
        require_once(NEXTDOM_ROOT . '/desktop/template/header.php');
    }

    /**
     * Affiche le menu en fonction du mode
     */
    public static function showMenu()
    {
        if (isset($_SESSION['user'])) {
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
            if (file_exists(NEXTDOM_ROOT . '/desktop/template/menu_' . $designTheme . '.php')) {
                require_once(NEXTDOM_ROOT . '/desktop/template/menu_' . $designTheme . '.php');
            } else {
                require_once(NEXTDOM_ROOT . '/desktop/template/menu.php');
            }
        } else {
            if (isset($_SESSION['user'])) {
                $designTheme = $_SESSION['user']->getOptions('design_nextdom');
                if ($designTheme == "dashboard") {
                    require_once(NEXTDOM_ROOT . '/desktop/template/menu.php');
                } else {
                    require_once(NEXTDOM_ROOT . '/desktop/template/menu_dashboard-v2.php');
                }
            } else {
                require_once(NEXTDOM_ROOT . '/desktop/template/menu_dashboard-v2.php');
            }
        }
    }

    /**
     * Initialise les informations nécessaires au menu
     *
     * @return object Plugin courant
     */
    public static function initPluginsData(Render $render)
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $currentPlugin = null;

        self::$pageData['PANEL_MENU'] = [];
        $pluginsList = PluginManager::listPlugin(true, true);
        if (count($pluginsList) > 0) {
            self::$pageData['MENU_PLUGIN'] = [];
            self::$pageData['MENU_PLUGIN_CATEGORY'] = [];
            foreach ($pluginsList as $categoryName => $category) {
                self::$pageData['MENU_PLUGIN'][$categoryName] = [];
                self::$pageData['MENU_PLUGIN_CATEGORY'][$categoryName] = [];

                $icon = '';
                $name = $categoryName;
                try {
                    $icon = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryName]['icon'];
                    $name = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categoryName]['name'];
                } catch (\Exception $e) {

                }

                self::$pageData['MENU_PLUGIN_CATEGORY'][$categoryName]['name'] = $render->getTranslation($name);
                self::$pageData['MENU_PLUGIN_CATEGORY'][$categoryName]['icon'] = $icon;

//                self::$pluginMenu .= '<li class="dropdown-submenu"><a data-toggle="dropdown"><i class="fa ' . $icon . '"></i> ' . $render->getTranslation($name) . '</a>';
//                self::$pluginMenu .= '<ul class="dropdown-menu">';
                foreach ($category as $plugin) {
                    self::$pageData['MENU_PLUGIN'][$categoryName][] = $plugin;
                    if ($plugin->getId() == Utils::init('m')) {
                        $currentPlugin = $plugin;
                        self::$title = ucfirst($currentPlugin->getName()) . ' - NextDom';
                    }
//                    self::$pluginMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $plugin->getId() . '&p=' . $plugin->getIndex() . '"><img class="img-responsive" src="' . $plugin->getPathImgIcon() . '" /> ' . $plugin->getName() . '</a></li>';
                    // TODO: C'est quoi ?
                    if ($plugin->getDisplay() != '' && \config::bykey('displayDesktopPanel', $plugin->getId(), 0) != 0) {
                        //self::$panelMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $plugin->getId() . '&p=' . $plugin->getDisplay() . '"><img class="img-responsive" src="' . $plugin->getPathImgIcon() . '" /> ' . $plugin->getName() . '</a></li>';
                        self::$pageData['PANEL_MENU'][] = $plugin;
                    }
                    if ($plugin->getEventjs() == 1) {
                        self::$eventJsPlugin[] = $plugin->getId();
                    }
                }
//                self::$pluginMenu .= '</ul></li>';
            }
        }
        return $currentPlugin;
    }

    /**
     * Afficher un message d'erreur
     *
     * @param string $msg Message de l'erreur
     */
    public static function showAlertMessage(string $msg)
    {
        echo '<div class="alert alert-danger">' . $msg . '</div>';
    }

    public static function showConnectionPage($configs)
    {
        self::$pageData = [];
        self::$pageData['JS_POOL'] = [];
        self::$pageData['CSS_POOL'] = [];
        self::$pageData['TITLE'] = 'Connexion';
        $render = Render::getInstance();
        self::initHeaderData($render, SELF::$title, $configs);

        $logo = \config::byKey('product_connection_image');
        self::$pageData['CSS_POOL'][] = '/desktop/css/connection.css';
        self::$pageData['JS_POOL'][] = '/desktop/js/connection.js';
        self::$pageData['JS_POOL'][] = '/3rdparty/animate/animate.js';

        $render->show('desktop/connection.html.twig', self::$pageData);
    }

    /**
     * Obtenir le code HTML du menu des plugins
     *
     * @return string Code HTML du menu des plugins
     */
    public static function getPluginMenu()
    {
        return self::$pluginMenu;
    }

    /**
     * Obtenir le code HTML du panneau TODO ?????
     *
     * @return string Code HTML du panneau
     */
    public static function getPanelMenu()
    {
        return self::$panelMenu;
    }

    /**
     * @param $configs
     */
    public static function showRescueMode($configs)
    {
        global $homeLink;
        global $language;
        global $configs;

        if (!in_array(Utils::init('p'), array('custom', 'backup', 'cron', 'connection', 'log', 'database', 'editor', 'system'))) {
            $_GET['p'] = 'system';
        }
        $homeLink = 'index.php?v=d&p=dashboard';
        $eventjs_plugin = [];
        $title = 'NextDom';
        $page = '';
        //TODO: Tests à revoir
        if (init('p') == '') {
            redirect($homeLink);
        } else {
            $page = init('p');
            self::$title = ucfirst($page) . ' - ' . $title;
        }
        $language = $configs['language'];

        // TODO: Remplacer par un include dans twig
        $render = Render::getInstance();
        self::initHeaderData($render, self::$title, $configs);

        self::$pageData['CSS'] = $render->getCssHtmlTag('/css/nextdom.css');
        self::$pageData['varToJs'] = Utils::getVarsToJS(array(
            'userProfils' => $_SESSION['user']->getOptions(),
            'user_id' => $_SESSION['user']->getId(),
            'user_isAdmin' => Status::isConnectAdmin(),
            'user_login' => $_SESSION['user']->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse']
        ));
        self::$pageData['JS'] = '';

        if (count($eventjs_plugin) > 0) {
            foreach ($eventjs_plugin as $value) {
                try {
                    self::$pageData['JS'] .= $render->getJsHtmlTag('/plugins/' . $value . '/desktop/js/event.js');
                } catch (\Exception $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
        self::$pageData['MENU'] = $render->get('desktop/menu_rescue.html.twig');

        try {
            if (!\nextdom::isStarted()) {
                self::$pageData['alertMsg'] = 'NextDom est en cours de démarrage, veuillez patienter . La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            ob_start();
            \include_file('desktop', $page, 'php');
            self::$pageData['content'] = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            self::$pageData['alertMsg'] = displayException($e);
        }

        self::$pageData['CONTENT'] = $render->get('desktop/index.html.twig', self::$pageData);

        $render = Render::getInstance();
        $render->show('desktop/base.html.twig', self::$pageData);
    }

    public static function showContent($configs)
    {
        global $homeLink;
        global $language;
        global $configs;
        self::$pageData = [];
        self::$pageData['JS_POOL'] = [];
        self::$pageData['CSS_POOL'] = [];
        self::$eventJsPlugin = [];
        self::$title = 'NextDom';
        $page = '';
        $language = $configs['language'];
        $homeLink = self::getHomeLink();

        //TODO: Tests à revoir
        if (Utils::init('p') == '') {
            redirect($homeLink);
        } else {
            $page = Utils::init('p');
            self::$title = ucfirst($page) . ' - ' . self::$title;
        }

        $render = Render::getInstance();
        $currentPlugin = PrepareView::initPluginsData($render);
        self::initPluginsEvents($render);
        self::initHeaderData($render, self::$title, $configs);

        self::$pageData['CSS_POOL'][] = '/css/nextdom.css';
        self::$pageData['JS_VARS'] = array(
            'user_id' => $_SESSION['user']->getId(),
            'user_isAdmin' => Status::isConnectAdmin(),
            'user_login' => $_SESSION['user']->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse'],
            'widget_width_step' => $configs['widget::step::width'],
            'widget_height_step' => $configs['widget::step::height'],
            'widget_margin' => $configs['widget::margin']
        );
        self::$pageData['JS_VARS_RAW'] = array(
            'userProfils' => Utils::getArrayToJQueryJson($_SESSION['user']->getOptions()),
        );


        self::$pageData['MENU_VIEW'] = '/desktop/menu.html.twig';
        if (isset($_SESSION['user'])) {
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
            if (file_exists(NEXTDOM_ROOT . '/views/desktop/menu_' . $designTheme . '.html.twig')) {
                self::$pageData['MENU_VIEW'] = '/desktop/menu_' . $designTheme . '.html.twig';
            }
        }

        $baseView = '/desktop/base.html.twig';
        self::initMenu($render, $currentPlugin, $homeLink);
        if (strstr(self::$pageData['MENU_VIEW'], 'v2')) {
            $baseView = '/desktop/base-v2.html.twig';
        }

        try {
            if (!\nextdom::isStarted()) {
                $pageData['alertMsg'] = 'NextDom est en cours de démarrage, veuillez patienter . La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            ob_start();
            if ($currentPlugin !== null && is_object($currentPlugin)) {
                \include_file('desktop', $page, 'php', $currentPlugin->getId());
            } else {
                \include_file('desktop', $page, 'php', '', true);
            }
            $pageData['content'] = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            $pageData['alertMsg'] = displayException($e);
        }

        self::$pageData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show($baseView, self::$pageData);

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
                if ($homePage[1] == "dashboard") {
                    $designTheme = $_SESSION['user']->getOptions('design_nextdom');
                }
                if ($designTheme <> "") {
                    $homeLink = 'index.php?v=d&p=' . $designTheme;
                } else {
                    $homeLink = 'index.php?v=d&p=' . $homePage[1];
                }
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

    private static function initPluginsEvents(Render $render)
    {
        $result = '';
        if (count(self::$eventJsPlugin) > 0) {
            foreach (self::$eventJsPlugin as $value) {
                try {
                    self::$pageData['JS_POOL'][] = '/plugins/'.$value.'/desktop/js/events.js';
                } catch (\Exception $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
        return $result;
    }

    private static function initMenu($menuView, $currentPlugin, $homeLink)
    {
        self::$pageData['nbMessage'] = \message::nbMessage();
        self::$pageData['nbUpdate'] = UpdateManager::nbNeedUpdate();
        self::$pageData['jeeObjectsTree'] = JeeObjectManager::buildTree(null, false);
        self::$pageData['viewsList'] = \view::all();
        self::$pageData['plansList'] = \planHeader::all();
        self::$pageData['plans3dList'] = \plan3dHeader::all();
        if (is_object($currentPlugin) && $currentPlugin->getIssue()) {
            self::$pageData['currentPluginIssue'] = $currentPlugin->getIssue();
        }
        self::$pageData['canSudo'] = \nextdom::isCapable('sudo');
        self::$pageData['isAdmin'] = Status::isConnectAdmin();
        self::$pageData['htmlGlobalSummary'] = JeeObjectManager::getGlobalHtmlSummary();
        self::$pageData['homeLink'] = $homeLink;
        self::$pageData['logo'] = \config::byKey('product_image');
        self::$pageData['userLogin'] = $_SESSION['user']->getLogin();
        self::$pageData['nextdomVersion'] = \nextdom::version();
        self::$pageData['mParam'] = Utils::init('m');
        self::$pageData['pParam'] = Utils::init('p');
    }

    private static function initHeaderData(Render $render, $title, $configs)
    {
//        $headerData = [];
        // TODO: Remplacer par un include dans twig
        self::$pageData['PRODUCT_NAME'] = $configs['product_name'];
        self::$pageData['PRODUCT_ICON'] = $configs['product_icon'];
        self::$pageData['AJAX_TOKEN'] = \ajax::getToken();
        self::$pageData['LANGUAGE'] = $configs['language'];
        self::$pageData['TITLE'] = $title;
        self::initJsPool();
        self::initCssPool($configs);
        // TODO: A virer
        ob_start();
        \include_file('core', 'icon.inc', 'php');
        self::$pageData['CUSTOM_CSS'] = ob_get_clean();
    }

    private static function initJsPool()
    {
        if (file_exists(NEXTDOM_ROOT . '/js/base.js')) {
            self::$pageData['JS_POOL'][] = '/js/base.js';
            self::$pageData['JS_POOL'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.min.js';
            self::$pageData['JS_POOL'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.widgets.min.js';
        } else {
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.utils/jquery.utils.js';
            self::$pageData['JS_POOL'][] = 'core/core.js';
            self::$pageData['JS_POOL'][] = '3rdparty/bootstrap/bootstrap.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.ui/jquery-ui.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.ui/jquery.ui.datepicker.fr.js';
            self::$pageData['JS_POOL'][] = 'core/js.inc.js';
            self::$pageData['JS_POOL'][] = '3rdparty/bootbox/bootbox.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/highstock/highstock.js';
            self::$pageData['JS_POOL'][] = '3rdparty/highstock/highcharts-more.js';
            self::$pageData['JS_POOL'][] = '3rdparty/highstock/modules/solid-gauge.js';
            self::$pageData['JS_POOL'][] = '3rdparty/highstock/modules/exporting.js';
            self::$pageData['JS_POOL'][] = '3rdparty/highstock/modules/export-data.js';
            self::$pageData['JS_POOL'][] = 'desktop/utils.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.toastr/jquery.toastr.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.at.caret/jquery.at.caret.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jwerty/jwerty.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.packery/jquery.packery.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.lazyload/jquery.lazyload.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/lib/codemirror.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/addon/edit/matchbrackets.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/htmlmixed/htmlmixed.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/clike/clike.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/php/php.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/xml/xml.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/javascript/javascript.js';
            self::$pageData['JS_POOL'][] = '3rdparty/codemirror/mode/css/css.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.tree/jstree.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.ui.widget.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.iframe-transport.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.fileupload.js';
            self::$pageData['JS_POOL'][] = '3rdparty/datetimepicker/jquery.datetimepicker.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.cron/jquery.cron.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/jquery.contextMenu/jquery.contextMenu.min.js';
            self::$pageData['JS_POOL'][] = '3rdparty/autosize/autosize.min.js';
        }
    }

    private static function initCssPool($configs)
    {
        $nextdomThemeDir = NEXTDOM_ROOT . '/css/themes/';
        $bootstrapTheme = '';
        $defaultBootstrapTheme = $configs['default_bootstrap_theme'];
        if (isset($_SESSION['user'])) {
            $bootstrapTheme = $_SESSION['user']->getOptions('bootstrap_theme');
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
        }

        self::$pageData['CSS_POOL'][] = '/css/nextdom.css';
        // TODO: AU SECOURS
        $addBootstrapThemeFile = true;
        if (!Status::isRescueMode()) {
            if (!Status::isConnect()) {
                if (file_exists($nextdomThemeDir . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css')) {
                    self::$pageData['CSS_POOL'][] = '/css/themes/' . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css';
                } else {
                    self::$pageData['CSS_POOL'][] = '/3rdparty/bootstrap/css/bootstrap.min.css';
                }
                if (is_dir($nextdomThemeDir . $bootstrapTheme . '/desktop')) {
                    if (file_exists($nextdomThemeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js')) {
                        self::$pageData['JS_POOL'][] = '/css/themes/' . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js';
                    }
                }
                if (isset($_SESSION['user']) && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                    $highstockThemeFile = '/3rdparty/highstock/themes/' . $_SESSION['user']->getOptions('desktop_highcharts_theme') . '.js';
                    if (file_exists($highstockThemeFile)) {
                        self::$pageData['JS_POOL'][] = $highstockThemeFile;
                    }
                }
            } else {
                if (file_exists($nextdomThemeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.css')) {
                    self::$pageData['CSS_POOL'][] = '/css/themes/' . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.css';
                    $addBootstrapThemeFile = false;
                } else {
                    if (file_exists($nextdomThemeDir . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css')) {
                        self::$pageData['CSS_POOL'][] = '/css/themes/' . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css';
                        $addBootstrapThemeFile = false;
                    }
                }
            }
        }
        if ($addBootstrapThemeFile) {
            self::$pageData['CSS_POOL'][] = '/3rdparty/bootstrap/css/bootstrap.min.css';
        }
        // TODO: Simplifiable ?
        if (isset($_SESSION['user'])) {
            if (file_exists(NEXTDOM_ROOT . '/css/' . $designTheme . '.css')) {
                self::$pageData['CSS_POOL'][] = '/css/' . $designTheme . '.css';
            } else {
                self::$pageData['CSS_POOL'][] = '/css/dashboard-v2.css';
            }
            if (file_exists(NEXTDOM_ROOT . '/desktop/js/' . $designTheme . '.js')) {
                self::$pageData['JS_POOL'][] = '/desktop/js/' . $designTheme . '.js';
            } else {
                self::$pageData['JS_POOL'][] = '/desktop/js/dashboard-v2.js';
            }
        }
        if (!Status::isRescueMode() && $configs['enableCustomCss'] == 1) {
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.css')) {
                self::$pageData['CSS_POOL'][] = '/desktop/custom/custom.css';
            }
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.js')) {
                self::$pageData['JS_POOL'][] = '/desktop/custom/custom.js';
            }
        }
    }
}
