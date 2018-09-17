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

use NextDom\Helpers\Status;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Helpers\Controller;

/**
 * Classe de support à l'affichage des contenus HTML
 */
class PrepareView
{
    public static function showFirstUsePage($configs)
    {
        $pageData = [];
        $pageData['JS_POOL'] = [];
        $pageData['JS_END_POOL'] = [];
        $pageData['CSS_POOL'] = [];
        $pageData['TITLE'] = '1ere connexion';
        $render = Render::getInstance();
        self::initHeaderData($pageData, $configs);
        //TODO: Vérifier ça
        $logo = \config::byKey('product_connection_image');
        $pageData['CSS_POOL'][]    = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][]    = '/3rdparty/iCheck/all.css';
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
        //TODO: Vérifier ça
        $logo = \config::byKey('product_connection_image');
        $pageData['CSS_POOL'][]    = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][]    = '/3rdparty/iCheck/all.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/connection.js';

        $render->show('desktop/connection.html.twig', $pageData);
    }

    /**
     * @param $configs
     */
    public static function showRescueMode($configs)
    {
        global $language;
        global $configs;

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

        try {
            if (!\nextdom::isStarted()) {
                $pageData['alertMsg'] = 'NextDom est en cours de démarrage, veuillez patienter . La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            ob_start();
            \include_file('desktop', $page, 'php');
            $pageData['content'] = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            $pageData['alertMsg'] = displayException($e);
        }

        $pageData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show('layouts/base_rescue.html.twig', $pageData);
    }

    public static function showContent($configs)
    {
        global $language;
        global $configs;
        
        $pageData             = [];
        $pageData['JS_POOL']  = [];
        $pageData['CSS_POOL'] = [];
        $page = '';
        $language = $configs['language'];
      	$designTheme = $_SESSION['user']->getOptions('design_nextdom');

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
            'nextdom_firstUse'   => $configs['nextdom::firstUse'],
            'widget_width_step'  => $configs['widget::step::width'],
            'widget_height_step' => $configs['widget::step::height'],
            'widget_margin'      => $configs['widget::margin']
        ];
        $pageData['JS_VARS_RAW'] = [
            'userProfils' => Utils::getArrayToJQueryJson($_SESSION['user']->getOptions()),
        ];

        $pageData['MENU_VIEW'] = '/desktop/menu_dashboard.html.twig';
        self::initMenu($pageData, $currentPlugin);

        $baseView = '/layouts/base_dashboard.html.twig';

        try {
            if (!\nextdom::isStarted()) {
                $pageData['ALERT_MSG'] = 'NextDom est en cours de démarrage, veuillez patienter . La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            $pageData['content'] = self::getContent($render, $pageData, $page, $currentPlugin);
        } catch (\Exception $e) {
            ob_end_clean();
            $pageData['ALERT_MSG'] = displayException($e);
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
     * @return mixed Current loaded plugin
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
                    if ($plugin->getDisplay() != '' && \config::bykey('displayDesktopPanel', $plugin->getId(), 0) != 0) {
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
     */
    private static function initPluginsEvents($eventsJsPlugin, &$pageData)
    {
        if (count($eventsJsPlugin) > 0) {
            foreach ($eventsJsPlugin as $value) {
                try {
                    $pageData['JS_POOL'][] = '/plugins/'.$value.'/public/js/desktop/events.js';
                } catch (\Exception $e) {
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
        $pageData['CAN_SUDO']                 = \nextdom::isCapable('sudo');
        $pageData['MENU_NB_MESSAGES']         = \message::nbMessage();
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
        $pageData['PRODUCT_IMAGE']            = \config::byKey('product_image');
        $pageData['USER_ISCONNECTED']         = $_SESSION['user']->is_Connected();
        $pageData['USER_AVATAR']              = $_SESSION['user']->getOptions('avatar');
        $pageData['USER_LOGIN']               = $_SESSION['user']->getLogin();
        $pageData['NEXTDOM_VERSION']          = \nextdom::version();
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
        $pageData['AJAX_TOKEN'] = \ajax::getToken();
        $pageData['LANGUAGE'] = $configs['language'];
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
            $pageData['JS_POOL'][] = 'public/js/base.js';
            $pageData['JS_POOL'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.min.js';
            $pageData['JS_POOL'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.widgets.min.js';
        } else {
            $pageData['JS_POOL'][] = '3rdparty/iziToast/js/iziToast.js';
            $pageData['JS_POOL'][] = '/public/js/desktop/utils.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.utils/jquery.utils.js';
            $pageData['JS_POOL'][] = 'core/js/core.js';
            $pageData['JS_POOL'][] = '3rdparty/bootstrap/bootstrap.min.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.ui/jquery-ui.min.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.ui/jquery.ui.datepicker.fr.js';
            $pageData['JS_POOL'][] = 'core/js/nextdom.class.js';
            $pageData['JS_POOL'][] = 'core/js/private.class.js';
            $pageData['JS_POOL'][] = 'core/js/eqLogic.class.js';
            $pageData['JS_POOL'][] = 'core/js/cmd.class.js';
            $pageData['JS_POOL'][] = 'core/js/object.class.js';
            $pageData['JS_POOL'][] = 'core/js/scenario.class.js';
            $pageData['JS_POOL'][] = 'core/js/plugin.class.js';
            $pageData['JS_POOL'][] = 'core/js/message.class.js';
            $pageData['JS_POOL'][] = 'core/js/view.class.js';
            $pageData['JS_POOL'][] = 'core/js/config.class.js';
            $pageData['JS_POOL'][] = 'core/js/history.class.js';
            $pageData['JS_POOL'][] = 'core/js/cron.class.js';
            $pageData['JS_POOL'][] = 'core/js/security.class.js';
            $pageData['JS_POOL'][] = 'core/js/update.class.js';
            $pageData['JS_POOL'][] = 'core/js/user.class.js';
            $pageData['JS_POOL'][] = 'core/js/backup.class.js';
            $pageData['JS_POOL'][] = 'core/js/interact.class.js';
            $pageData['JS_POOL'][] = 'core/js/update.class.js';
            $pageData['JS_POOL'][] = 'core/js/plan.class.js';
            $pageData['JS_POOL'][] = 'core/js/log.class.js';
            $pageData['JS_POOL'][] = 'core/js/repo.class.js';
            $pageData['JS_POOL'][] = 'core/js/network.class.js';
            $pageData['JS_POOL'][] = 'core/js/dataStore.class.js';
            $pageData['JS_POOL'][] = 'core/js/cache.class.js';
            $pageData['JS_POOL'][] = 'core/js/report.class.js';
            $pageData['JS_POOL'][] = 'core/js/jeedom.class.js';
            $pageData['JS_POOL'][] = '3rdparty/bootbox/bootbox.min.js';
            $pageData['JS_POOL'][] = '3rdparty/highstock/highstock.js';
            $pageData['JS_POOL'][] = '3rdparty/highstock/highcharts-more.js';
            $pageData['JS_POOL'][] = '3rdparty/highstock/modules/solid-gauge.js';
            $pageData['JS_POOL'][] = '3rdparty/highstock/modules/exporting.js';
            $pageData['JS_POOL'][] = '3rdparty/highstock/modules/export-data.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.at.caret/jquery.at.caret.min.js';
            $pageData['JS_POOL'][] = '3rdparty/jwerty/jwerty.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.packery/jquery.packery.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.lazyload/jquery.lazyload.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/lib/codemirror.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/addon/edit/matchbrackets.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/htmlmixed/htmlmixed.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/clike/clike.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/php/php.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/xml/xml.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/javascript/javascript.js';
            $pageData['JS_POOL'][] = '3rdparty/codemirror/mode/css/css.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.tree/jstree.min.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.ui.widget.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.iframe-transport.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.fileupload/jquery.fileupload.js';
            $pageData['JS_POOL'][] = '3rdparty/datetimepicker/jquery.datetimepicker.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.cron/jquery.cron.min.js';
            $pageData['JS_POOL'][] = '3rdparty/jquery.contextMenu/jquery.contextMenu.min.js';
            $pageData['JS_POOL'][] = '3rdparty/autosize/autosize.min.js';
            $pageData['JS_POOL'][] = '3rdparty/iCheck/icheck.min.js';
            $pageData['JS_POOL'][] = '3rdparty/AdminLTE/js/dashboard-v2.js';
        }
    }

    /**.
     * @param $pageData
     * @param $configs
     */
    private static function initCssPool(&$pageData, $configs)
    {
        $pageData['CSS_POOL'][] = '/public/css/nextdom.css';
        $pageData['CSS_POOL'][] = '/3rdparty/iziToast/css/iziToast.css';
        // Icônes
        $pageData['CSS_POOL'][] = '/3rdparty/font-awesome/css/font-awesome.min.css';
        $pageData['CSS_POOL'][] = '/3rdparty/font-awesome5/css/fontawesome-all.css';
        $rootDir = NEXTDOM_ROOT . '/public/icon/';
        foreach (ls($rootDir, '*') as $dir) {
            if (is_dir($rootDir . $dir) && file_exists($rootDir . $dir . '/style.css')) {
                $pageData['CSS_POOL'][] = '/public/icon/' . $dir . 'style.css';
            }
        }

        if (!Status::isRescueMode()) {
            if (!Status::isConnect()) {
                if (isset($_SESSION['user']) && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                    $highstockThemeFile = '/3rdparty/highstock/themes/' . $_SESSION['user']->getOptions('desktop_highcharts_theme') . '.js';
                    if (file_exists($highstockThemeFile)) {
                        $pageData['JS_POOL'][] = $highstockThemeFile;
                    }
                }
            }
        }

        if (!Status::isRescueMode() && $configs['enableCustomCss'] == 1) {
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.css')) {
                $pageData['CSS_POOL'][] = '/desktop/custom/custom.css';
            }
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.js')) {
                $pageData['JS_POOL'][] = '/desktop/custom/custom.js';
            }
        }
    }

    /**
     * @param Render $render
     * @param array $pageContent
     * @param string $page
     * @param $currentPlugin
     * @return string
     * @throws \Exception
     */
    private static function getContent(Render $render, array &$pageContent, string $page, $currentPlugin) {
        if ($currentPlugin !== null && is_object($currentPlugin)) {
            ob_start();
            \include_file('desktop', $page, 'php', $currentPlugin->getId(), true);
            return ob_get_clean();
        } else {
            $controllerRoute = Controller::getRoute($page);
            if ($controllerRoute == null) {
                ob_start();
                \include_file('desktop', $page, 'php', '', true);
                return ob_get_clean();
            } else {
                return \NextDom\Helpers\Controller::$controllerRoute($render, $pageContent);
            }
        }
    }
    
}
