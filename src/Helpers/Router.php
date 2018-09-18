<?php
/* This file is part of NextDom software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;

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
            $this->showModal();
        } elseif (isset($_GET['configure'])) {
            $this->showConfiguration();
        } elseif (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $this->getContentByAjax();
        } else {
            require_once(NEXTDOM_ROOT . '/core/php/authentification.php');
            Status::initConnectState();
            $configs = \config::byKeys(array(
                'enableCustomCss',
                'language',
                'nextdom::firstUse',
                'widget::step::width',
                'widget::step::height',
                'widget::margin',
                'product_name',
                'product_icon',
                'product_connection_image',
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
     * Viewing a modal on a computer
     *
     * @throws \Exception
     */
    private function showModal()
    {
        try {
            \include_file('core', 'authentification', 'php');
            \include_file('desktop', Utils::init('modal'), 'modal', Utils::init('plugin'), true);
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(\displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
        }
    }

    /**
     * Displaying the configuration page of a plugin
     *
     * @throws \Exception Affichage
     */
    private function showConfiguration()
    {
        \include_file('core', 'authentification', 'php');
        \include_file('plugin_info', 'configuration', 'configuration', Utils::init('plugin'), true);
    }

    /**
     * Response to an Ajax request
     *
     * @throws \Exception
     */
    private function getContentByAjax()
    {
        try {
            \include_file('core', 'authentification', 'php');
            $page = Utils::init('p');
            $controllerRoute = Controller::getRoute($page);
            if ($controllerRoute === null) {
                $this->showError404AndDie();
            } else {
                $render = Render::getInstance();
                $pageContent = [];
                $pageContent['JS_POOL'] = [];
                $pageContent['JS_END_POOL'] = [];
                $pageContent['CSS_POOL'] = [];
                $pageContent['JS_VARS'] = [];
                $pageContent['content'] = \NextDom\Helpers\Controller::$controllerRoute($render, $pageContent);
                $render->show('/layouts/ajax_content.html.twig', $pageContent);
            }
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(displayException($e), 'desktop/' . Utils::init('p') . '.php');
            echo '</div>';
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
    private function showError404AndDie()
    {
        header("HTTP/1.0 404 Not Found");
        require(NEXTDOM_ROOT . '/public/404.html');
        exit();
    }
}
