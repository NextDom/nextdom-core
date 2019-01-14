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

use NextDom\Managers\ConfigManager;

/**
 * Turnout of the display
 *
 * @package NextDom\Helper
 */
class Router
{
    /**
     * @var string Type of view
     */
    private $viewType;

    /**
     * Builder initializing the type of view
     *
     * @param $viewType string Type of view
     */
    public function __construct(string $viewType)
    {
        $this->viewType = $viewType;
    }

    /**
     * Viewing the requested content
     *
     * @return bool True if an answer has been provided.
     * @throws \Exception
     */
    public function show(): bool
    {
        $result = false;
        if ($this->viewType == 'd') {
            $this->desktopView();
            $result = true;
        } elseif ($this->viewType == 'm') {
            $this->mobileView();
            $result = true;
        }
        return $result;
    }

    /**
     * Display for a computer
     *
     * @throws \Exception
     */
    public function desktopView()
    {
        if (isset($_GET['modal'])) {
            PrepareView::showModal();
        } elseif (isset($_GET['configure'])) {
            $this->showConfiguration();
        } elseif (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            PrepareView::getContentByAjax();
        } else {
            require_once(NEXTDOM_ROOT . '/core/php/authentification.php');
            Status::initConnectState();
            $configs = ConfigManager::byKeys(array(
                'enableCustomCss',
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
                'product_name',
                'product_icon',
                'product_connection_image',
                'theme',
                'default_bootstrap_theme'));
            if ($configs['nextdom::firstUse'] == 1) {
                PrepareView::showFirstUsePage($configs);
            } elseif (!Status::isConnect()) {
                PrepareView::showConnectionPage($configs);
            } else {
                if (Status::isRescueMode()) {
                    PrepareView::showRescueMode($configs);
                } else {
                    PrepareView::showContent($configs);
                }
            }
        }
    }

    /**
     * Displaying the configuration page of a plugin
     *
     * @throws \Exception Affichage
     */
    private function showConfiguration()
    {
        AuthentificationHelper::init();
        \include_file('plugin_info', 'configuration', 'configuration', Utils::init('plugin'), true);
    }

    /**
     * Display mobile view
     *
     * @throws \Exception
     */
    private function mobileView()
    {
        $filename = 'index';
        $type = 'html';
        $plugin = '';
        $modal = Utils::init('modal', false);
        if ($modal !== false) {
            $filename = $modal;
            $type = 'modalhtml';
            $plugin = Utils::init('plugin');
        } elseif (isset($_GET['p']) && isset($_GET['ajax'])) {
            $filename = $_GET['p'];
            $plugin = isset($_GET['m']) ? $_GET['m'] : $plugin;
        }
        \include_file('mobile', $filename, $type, $plugin, true);
    }

    /**
    *
    * Generate 404 page
    */
    public static function showError404AndDie()
    {
        header("HTTP/1.0 404 Not Found");
        require(NEXTDOM_ROOT . '/public/404.html');
        exit();
    }
}
