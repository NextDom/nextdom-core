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
        if (Status::isRescueMode()) {
            require_once(NEXTDOM_ROOT . '/desktop/template/menuRescue.php');
        } else {
            if (isset($_SESSION['user'])) {
                $designTheme = $_SESSION['user']->getOptions('design_nextdom');
                if (file_exists(NEXTDOM_ROOT . '/desktop/template/menu_'.$designTheme.'.php')) {
                    require_once(NEXTDOM_ROOT . '/desktop/template/menu_'.$designTheme.'.php');
                }else{
                    require_once(NEXTDOM_ROOT . '/desktop/template/menu.php');
                }} else{
                    require_once(NEXTDOM_ROOT . '/desktop/template/menu.php');
                }
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
        global $title;
        global $eventjs_plugin;
        $plugin = null;

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
                        $plugin = $pluginItem;
                        $title = ucfirst($plugin->getName()) . ' - NextDom';
                    }
                    self::$pluginMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $pluginItem->getId() . '&p=' . $pluginItem->getIndex() . '"><img class="img-responsive" src="' . $pluginItem->getPathImgIcon() . '" /> ' . $pluginItem->getName() . '</a></li>';
                    // TODO: C'est quoi ?
                    if ($pluginItem->getDisplay() != '' && \config::bykey('displayDesktopPanel', $pluginItem->getId(), 0) != 0) {
                        self::$panelMenu .= '<li class="plugin-item"><a href="index.php?v=d&m=' . $pluginItem->getId() . '&p=' . $pluginItem->getDisplay() . '"><img class="img-responsive" src="' . $pluginItem->getPathImgIcon() . '" /> ' . $pluginItem->getName() . '</a></li>';
                    }
                    if ($pluginItem->getEventjs() == 1) {
                        $eventjs_plugin[] = $pluginItem->getId();
                    }
                }
                self::$pluginMenu .= '</ul></li>';
            }
        }
        return $plugin;
    }

    /**
    * Afficher un message d'erreur
    *
    * @param string $msg Message de l'erreur
    *
    * @return string Code HTML du message d'erreur
    */
    public static function showAlertMessage(string $msg) {
        echo '<div class="alert alert-danger">'.$msg.'</div>';
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
}
