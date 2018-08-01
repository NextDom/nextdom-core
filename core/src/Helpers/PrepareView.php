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
            require_once(NEXTDOM_ROOT . '/desktop/template/menu.php');
        }
    }

    /**
     * Initialise les informations nécessaires au menu
     *
     * @param array $internalConfig Configuration interne de NextDom
     *
     * @return object Plugin courant
     */
    public static function initMenus(array $internalConfig)
    {
        $currentPlugin = null;

        $pluginsList = PluginManager::listPlugin(true, true);
        if (count($pluginsList) > 0) {
            foreach ($pluginsList as $category_name => $category) {
                $icon = '';
                $name = $category_name;
                try {
                    $icon = $internalConfig['plugin']['category'][$category_name]['icon'];
                    $name = $internalConfig['plugin']['category'][$category_name]['name'];
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

    public static function showConnectionPage()
    {
        global $title;

        $title = 'Connexion';

        $render = Render::getInstance();
        ob_start();
        require_once(NEXTDOM_ROOT . '/desktop/template/header.php');
        $globalData['HEADER'] = ob_get_clean();

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

    public static function showRescueMode()
    {
        global $homeLink;

        $configs = \config::byKeys(array('enableCustomCss', 'language', 'nextdom::firstUse'));
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
        ob_start();
        require_once(NEXTDOM_ROOT . '/desktop/template/header.php');
        $globalData['HEADER'] = ob_get_clean();

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

    public static function showContent()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        global $homeLink;
        global $title;
        global $language;
        global $configs;

        $configs = \config::byKeys(array('enableCustomCss', 'language', 'nextdom::firstUse'));

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

        // Informations générales
        self::$eventJsPlugin = [];
        self::$title = 'NextDom';
        $page = '';
        //TODO: Tests à revoir
        if (init('p') == '') {
            redirect($homeLink);
        } else {
            $page = init('p');
            self::$title = ucfirst($page) . ' - ' . $title;
        }
        $language = $configs['language'];

        // Initialisation des plugins
        // Doit être fait avant pour avoir le nom du plugin en titre
        $currentPlugin = PrepareView::initMenus($NEXTDOM_INTERNAL_CONFIG);

        $globalData = [];
        // TODO: Remplacer par un include dans twig
        $title = self::$title;
        $render = Render::getInstance();

        ob_start();
        require_once(NEXTDOM_ROOT . '/desktop/template/header.php');
        $globalData['HEADER'] = ob_get_clean();

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

        if (count(self::$eventJsPlugin) > 0) {
            foreach (self::$eventJsPlugin as $value) {
                try {
                    $pageData['JS'] .= $render->getJsHtmlTag('/plugins/' . $value . '/desktop/js/event.js');
                } catch (\Exception $e) {
                    \log::add($value, 'error', 'Event JS file not found');
                }
            }
        }
        /*
        ob_start();
        PrepareView::showMenu();
        $globalData['MENU'] = ob_get_clean();
        */
        $globalData['MENU'] = self::getMenu($render, $currentPlugin, $homeLink);

        try {
            if (!\nextdom::isStarted()) {
                $pageData['alertMsg'] = 'NextDom est en cours de démarrage, veuillez patienter . La page se rechargera automatiquement une fois le démarrage terminé.';
            }
            ob_start();
            if ($currentPlugin !== null && is_object($currentPlugin)) {
                \include_file('desktop', $page, 'php', $currentPlugin->getId());
            } else {
                \include_file('desktop', $page, 'php');
            }
            $pageData['content'] = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            $pageData['alertMsg'] = displayException($e);
        }

        $globalData['CONTENT'] = $render->get('desktop/index.html.twig', $pageData);

        $render = Render::getInstance();
        $render->show('desktop/base.html.twig', $globalData);

    }

    private static function getMenu(Render $render, $currentPlugin, $homeLink)
    {
        $menuView = '/desktop/menu.html.twig';
        if (isset($_SESSION['user'])) {
            $designTheme = $_SESSION['user']->getOptions('design_nextdom');
            if (file_exists(NEXTDOM_ROOT . '/desktop/menu_' . $designTheme . '.php')) {
                $menuView = '/desktop/menu_' . $designTheme . '.php';
            }
        }

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
}
