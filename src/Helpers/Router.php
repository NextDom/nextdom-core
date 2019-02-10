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

use NextDom\Enums\GetParams;
use NextDom\Enums\ViewType;
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
        if ($this->viewType == ViewType::DESKTOP_VIEW) {
            $this->desktopView();
            $result = true;
        } elseif ($this->viewType == ViewType::MOBILE_VIEW) {
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
        Status::initConnectState();
        Status::initRescueModeState();

        if (isset($_GET[GetParams::MODAL])) {
            PrepareView::showModal();
        } elseif (isset($_GET[GetParams::PLUGIN_CONF])) {
            // Displaying the configuration section of a plugin in the configuration page
            FileSystemHelper::includeFile('plugin_info', 'configuration', 'configuration', Utils::init(GetParams::PLUGIN_ID), true);
        } elseif (isset($_GET[GetParams::AJAX_QUERY]) && $_GET[GetParams::AJAX_QUERY] == 1) {
            PrepareView::showContentByAjax();
        } else {
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
                PrepareView::showSpecialPage('firstUse', $configs);
            } elseif (!Status::isConnected()) {
                PrepareView::showSpecialPage('connection', $configs);
            } else {
                if (Status::isRescueMode()) {
                    Status::isConnectedAdminOrFail();
                    PrepareView::showRescueMode($configs);
                } else {
                    PrepareView::showContent($configs);
                }
            }
        }
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
        } elseif (isset($_GET['p']) && isset($_GET[GetParams::AJAX_QUERY])) {
            $filename = $_GET['p'];
            $plugin = isset($_GET['m']) ? $_GET['m'] : $plugin;
        }
        FileSystemHelper::includeFile('mobile', $filename, $type, $plugin, true);
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
