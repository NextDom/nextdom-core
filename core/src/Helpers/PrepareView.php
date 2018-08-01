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
    public static function initMenus()
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $currentPlugin = null;

        $pluginsList = PluginManager::listPlugin(true, true);
        if (count($pluginsList) > 0) {
            foreach ($pluginsList as $category_name => $category) {
                $icon = '';
                $name = $category_name;
                try {
                    $icon = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$category_name]['icon'];
                    $name = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$category_name]['name'];
                } catch (\Exception $e) {
                    $icon = '';
                    $name = $category_name;
                }

                self::$pluginMenu .= '<li class="dropdown-submenu"><a data-toggle="dropdown"><i class="fa ' . $icon . '"></i> {{' . $name . '}}</a>';
                self::$pluginMenu .= '<ul class="dropdown-menu">';
                foreach ($category as $pluginItem) {
                    if ($pluginItem->getId() == init('m')) {
                        $currentPlugin = $pluginItem;
                        self::$title = ucfirst($currentPlugin->getName()) . ' - NextDom';
                    }
                    self::$pluginMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $pluginItem->getId() . '&p=' . $pluginItem->getIndex() . '"><img class="img-responsive" src="' . $pluginItem->getPathImgIcon() . '" /> ' . $pluginItem->getName() . '</a></li>';
                    // TODO: C'est quoi ?
                    if ($pluginItem->getDisplay() != '' && \config::bykey('displayDesktopPanel', $pluginItem->getId(), 0) != 0) {
                        self::$panelMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $pluginItem->getId() . '&p=' . $pluginItem->getDisplay() . '"><img class="img-responsive" src="' . $pluginItem->getPathImgIcon() . '" /> ' . $pluginItem->getName() . '</a></li>';
                    }
                    if ($pluginItem->getEventjs() == 1) {
                        self::$eventJsPlugin[] = $pluginItem->getId();
                    }
                }
                self::$pluginMenu .= '</ul></li>';
            }
        }
        return $currentPlugin;
    }

    /**
     * Afficher un message d'erreur
     *
     * @param string $msg Message de l'erreur
     *
     * @return string Code HTML du message d'erreur
     */
    public static function showAlertMessage(string $msg)
    {
        echo '<div class="alert alert-danger">' . $msg . '</div>';
    }

    public static function showConnectionPage($configs)
    {
        $title = 'Connexion';

        $render = Render::getInstance();
        $globalData['HEADER'] = self::getHeader($render, $title, $configs);

        $logo = \config::byKey('product_connection_image');
        $css = $render->getCssHtmlTag('/desktop/css/connection.css');
        $js = $render->getJsHtmlTag('/desktop/js/connection.js');
        $js .= $render->getJsHtmlTag('/3rdparty/animate/animate.js');
        $globalData['CONTENT'] = $render->get('desktop/connection.html.twig', array('logo' => $logo, 'CSS' => $css, 'JS' => $js));

        $render = Render::getInstance();
        $render->show('desktop/base.html.twig', $globalData);
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

        $globalData = [];
        // TODO: Remplacer par un include dans twig
        $render = Render::getInstance();
        $globalData['HEADER'] = self::getHeader($render, self::$title, $configs);

        $pageData = [];

        $pageData['CSS'] = $render->getCssHtmlTag('/css/nextdom.css');
        $pageData['varToJs'] = Utils::getVarsToJS(array(
            'userProfils' => $_SESSION['user']->getOptions(),
            'user_id' => $_SESSION['user']->getId(),
            'user_isAdmin' => Status::isConnectAdmin(),
            'user_login' => $_SESSION['user']->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse']
        ));
        $pageData['JS'] = '';

        if (count($eventjs_plugin) > 0) {
            foreach ($eventjs_plugin as $value) {
                try {
                    $pageData['JS'] .= $render->getJsHtmlTag('/plugins/' . $value . '/desktop/js/event.js');
                } catch (\Exception $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
        $globalData['MENU'] = $render->get('desktop/menu_rescue.html.twig');

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

        $globalData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show('desktop/base.html.twig', $globalData);
    }

    public static function showContent($configs)
    {
        global $homeLink;
        global $language;
        global $configs;

        self::$eventJsPlugin = [];
        self::$title = 'NextDom';
        $globalData = [];
        $page = '';
        $baseView = '/desktop/base.html.twig';
        $language = $configs['language'];
        $homeLink = self::getHomeLink();

        //TODO: Tests à revoir
        if (Utils::init('p') == '') {
            redirect($homeLink);
        } else {
            $page = Utils::init('p');
            self::$title = ucfirst($page) . ' - ' . self::$title;
        }

        $currentPlugin = PrepareView::initMenus();
        $render = Render::getInstance();


        $globalData['HEADER'] = self::getHeader($render, self::$title, $configs);

        $pageData = [];
        $pageData['CSS'] = $render->getCssHtmlTag('/css/nextdom.css');
        $pageData['varToJs'] = Utils::getVarsToJS(array(
            'userProfils' => $_SESSION['user']->getOptions(),
            'user_id' => $_SESSION['user']->getId(),
            'user_isAdmin' => Status::isConnectAdmin(),
            'user_login' => $_SESSION['user']->getLogin(),
            'nextdom_firstUse' => $configs['nextdom::firstUse']
        ));
        $pageData['JS'] = self::getPluginJsEvents($render);

        $menuView = '/desktop/menu.html.twig';
        if (isset($_SESSION['user'])) {
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
            if (file_exists(NEXTDOM_ROOT . '/views/desktop/menu_' . $designTheme . '.html.twig')) {
                $menuView = '/desktop/menu_' . $designTheme . '.html.twig';
            }
        }
        $globalData['MENU'] = self::getMenu($render, $menuView, $currentPlugin, $homeLink);
        if (strstr($menuView, 'v2')) {
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

        $globalData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show($baseView, $globalData);

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

    private static function getPluginJsEvents(Render $render)
    {
        $result = '';
        if (count(self::$eventJsPlugin) > 0) {
            foreach (self::$eventJsPlugin as $value) {
                try {
                    $result .= $render->getJsHtmlTag('/plugins/' . $value . '/desktop/js/event.js');
                } catch (\Exception $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
        return $result;
    }

    private static function getMenu(Render $render, $menuView, $currentPlugin, $homeLink)
    {
        $menuData = [];
        $menuData['pluginMenu'] = self::$pluginMenu;
        $menuData['panelMenu'] = self::$panelMenu;
        $menuData['nbMessage'] = \message::nbMessage();
        $menuData['nbUpdate'] = UpdateManager::nbNeedUpdate();
        $menuData['jeeObjectsTree'] = JeeObjectManager::buildTree(null, false);
        $menuData['viewsList'] = \view::all();
        $menuData['plansList'] = \planHeader::all();
        $menuData['plans3dList'] = \plan3dHeader::all();
        if (is_object($currentPlugin) && $currentPlugin->getIssue()) {
            $menuData['currentPluginIssue'] = $currentPlugin->getIssue();
        }
        $menuData['canSudo'] = \nextdom::isCapable('sudo');
        $menuData['isAdmin'] = Status::isConnectAdmin();
        $menuData['htmlGlobalSummary'] = JeeObjectManager::getGlobalHtmlSummary();
        $menuData['homeLink'] = $homeLink;
        $menuData['logo'] = \config::byKey('product_image');
        $menuData['userLogin'] = $_SESSION['user']->getLogin();
        $menuData['nextdomVersion'] = \nextdom::version();
        $menuData['mParam'] = Utils::init('m');
        $menuData['pParam'] = Utils::init('p');
        return $render->get($menuView, $menuData);
    }

    private static function getHeader(Render $render, $title, $configs)
    {
        $headerData = [];
        // TODO: Remplacer par un include dans twig
        $themeDir = NEXTDOM_ROOT . '/css/themes/';
        $bootstrapTheme = '';
        $defaultBootstrapTheme = \config::byKey('default_bootstrap_theme');
        $headerData['productName'] = \config::byKey('product_name');
        $headerData['ajaxToken'] = \ajax::getToken();
        $headerData['cssPool'] = [];
        $headerData['jsPool'] = [];
        if (!Status::isConnect()) {
            if (!Status::isRescueMode() && file_exists($themeDir . \config::byKey('default_bootstrap_theme') . '/desktop/' . \config::byKey('default_bootstrap_theme') . '.css')) {
                $headerData['cssPool'][] = $themeDir . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css';
            } else {
                $headerData['cssPool'][] = '/3rdparty/bootstrap/css/bootstrap.min.css';
            }
        } else {
            $cssBootstrapToAdd = true;
            if (!Status::isRescueMode()) {
                if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.css')) {
                    $headerData['cssPool'][] = $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.css';
                    $cssBootstrapToAdd = false;
                } else {
                    if (file_exists($themeDir . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css')) {
                        $headerData['cssPool'][] = $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css';
                        $cssBootstrapToAdd = false;
                    }
                }
            }
            if ($cssBootstrapToAdd) {
                $headerData['cssPool'][] = '/3rdparty/bootstrap/css/bootstrap.min.css';
            }
        }
        // TODO: A virer
        ob_start();
        \include_file('core', 'icon.inc', 'php');
        \include_file('', 'nextdom', 'css');
        $headerData['customCss'] = ob_get_clean();

        if (file_exists(NEXTDOM_ROOT . '/js/base.js')) {
            $headerData['jsPool'][] = '/js/base.js';
            $headerData['jsPool'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.min.js';
            $headerData['jsPool'][] = '/3rdparty/jquery.tablesorter/jquery.tablesorter.widgets.min.js';
        } else {
            $headerData['jsPool'][] = '3rdparty/jquery.utils/jquery.utils.js';
            $headerData['jsPool'][] = 'core/core.js';
            $headerData['jsPool'][] = '3rdparty/bootstrap/bootstrap.min.js';
            $headerData['jsPool'][] = '3rdparty/jquery.ui/jquery-ui.min.js';
            $headerData['jsPool'][] = '3rdparty/jquery.ui/jquery.ui.datepicker.fr.js';
            $headerData['jsPool'][] = 'core/js.inc.js';
            $headerData['jsPool'][] = '3rdparty/bootbox/bootbox.min.js';
            $headerData['jsPool'][] = '3rdparty/highstock/highstock.js';
            $headerData['jsPool'][] = '3rdparty/highstock/highcharts-more.js';
            $headerData['jsPool'][] = '3rdparty/highstock/modules/solid-gauge.js';
            $headerData['jsPool'][] = '3rdparty/highstock/modules/exporting.js';
            $headerData['jsPool'][] = '3rdparty/highstock/modules/export-data.js';
            $headerData['jsPool'][] = 'desktop/utils.js';
            $headerData['jsPool'][] = '3rdparty/jquery.toastr/jquery.toastr.min.js';
            $headerData['jsPool'][] = '3rdparty/jquery.at.caret/jquery.at.caret.min.js';
            $headerData['jsPool'][] = '3rdparty/jwerty/jwerty.js';
            $headerData['jsPool'][] = '3rdparty/jquery.packery/jquery.packery.js';
            $headerData['jsPool'][] = '3rdparty/jquery.lazyload/jquery.lazyload.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/lib/codemirror.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/addon/edit/matchbrackets.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/htmlmixed/htmlmixed.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/clike/clike.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/php/php.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/xml/xml.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/javascript/javascript.js';
            $headerData['jsPool'][] = '3rdparty/codemirror/mode/css/css.js';
            $headerData['jsPool'][] = '3rdparty/jquery.tree/jstree.min.js';
            $headerData['jsPool'][] = '3rdparty/jquery.fileupload/jquery.ui.widget.js';
            $headerData['jsPool'][] = '3rdparty/jquery.fileupload/jquery.iframe-transport.js';
            $headerData['jsPool'][] = '3rdparty/jquery.fileupload/jquery.fileupload.js';
            $headerData['jsPool'][] = '3rdparty/datetimepicker/jquery.datetimepicker.js';
            $headerData['jsPool'][] = '3rdparty/jquery.cron/jquery.cron.min.js';
            $headerData['jsPool'][] = '3rdparty/jquery.contextMenu/jquery.contextMenu.min.js';
            $headerData['jsPool'][] = '3rdparty/autosize/autosize.min.js';
        }

        // TODO: A remonter
        if (isset($_SESSION['user'])) {
            $bootstrapTheme = $_SESSION['user']->getOptions('bootstrap_theme');
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
            if (file_exists(NEXTDOM_ROOT . '/css/' . $designTheme . '.css')) {
                $headerData['cssPool'][] = '/css/' . $designTheme . '.css';
            } else {
                $headerData['cssPool'][] = '/css/dashboard-v2.css';
            }
            if (file_exists(NEXTDOM_ROOT . '/desktop/js/' . $designTheme . '.js')) {
                $headerData['jsPool'][] = '/desktop/js/' . $designTheme . '.js';
            }
            else {
                $headerData['jsPool'][] = '/desktop/js/dashboard-v2.js';
            }
        }
        if (!Status::isRescueMode() && $configs['enableCustomCss'] == 1) {
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.css')) {
                $headerData['cssPool'][] = '/desktop/custom/custom.css';
            }
            if (file_exists(NEXTDOM_ROOT . '/desktop/custom/custom.js')) {
                $headerData['jsPool'][] = '/desktop/custom/custom.js';
            }
        }

        // TODO: Horreur à remonter
        try {
            if (Status::isConnect()) {
                if (!Status::isRescueMode() && is_dir($themeDir . $bootstrapTheme . '/desktop')) {
                    if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js')) {
                        $headerData['jsPool'][] = $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js';
                    }
                }
                if (!Status::isRescueMode() && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                    try {
                        if (is_dir($themeDir . $bootstrapTheme . '/desktop')) {
                            if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js')) {
                                $headerData['jsPool'][] = $themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js';
                            }
                        }
                    } catch (\Exception $e) {

                    }
                    if (!Status::isRescueMode() && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                        try {
                            $headerData['jsPool'][] = '/3rdparty/highstock/themes/' . $_SESSION['user']->getOptions('desktop_highcharts_theme') . '.js';
                        } catch (Exception $e) {

                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
        $headerData['language'] = Utils::getVarToJS('nextdom_langage', $configs['language']);
        $headerData['productIcon'] = \config::byKey('product_icon');
        $headerData['title'] = $title;
        return $render->get('/desktop/header.html.twig', $headerData);
    }
}
