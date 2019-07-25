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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * Show 404 error page (Not found)
     */
    public static function showError404AndDie()
    {
        header("HTTP/1.0 404 Not Found");
        require(NEXTDOM_ROOT . '/public/404.html');
        die();
    }

    /**
     * Show 401 error page (Unauthorized)
     */
    public static function showError401AndDie()
    {
        header("HTTP/1.1 401 Unauthorized");
        die();
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
        } elseif ($this->viewType == ViewType::STATIC_VIEW) {
            $this->staticView();
            $result = true;
        }
        return $result;
    }

    /**
     * Test if modal window is requested
     *
     * @return bool True if modal window is requested
     */
    private function isModalRequest()
    {
        return isset($_GET[GetParams::MODAL]);
    }

    /**
     * Test if plugin configuration page is requested
     *
     * @return bool True if plugin configuration page is requested
     */
    private function isPluginConfRequest()
    {
        return isset($_GET[GetParams::PLUGIN_CONF]);
    }

    /**
     * Test if page is requested by Ajax query
     *
     * @return bool True if page is requested by Ajax query
     */
    private function isAjaxQuery() {
        return isset($_GET[GetParams::AJAX_QUERY]) && $_GET[GetParams::AJAX_QUERY] == 1;
    }
    /**
     * Display for a computer
     *
     * @throws \Exception
     */
    public function desktopView()
    {
        AuthentificationHelper::init();
        $prepareView = new PrepareView();
        if ($this->isModalRequest()) {
            $prepareView->showModal();
        } elseif ($this->isPluginConfRequest()) {
            // Displaying the configuration section of a plugin in the configuration page
            FileSystemHelper::includeFile('plugin_info', 'configuration', 'configuration', Utils::init(GetParams::PLUGIN_ID), true);
        } elseif ($this->isAjaxQuery()) {
            $prepareView->showContentByAjax();
        } else {
            $prepareView->initConfig();
            if (!$prepareView->firstUseIsShowed()) {
                $prepareView->showSpecialPage('firstUse');
            } elseif (!AuthentificationHelper::isConnected()) {
                $prepareView->showSpecialPage('connection');
            } else {
                if (AuthentificationHelper::isRescueMode()) {
                    AuthentificationHelper::isConnectedAsAdminOrFail();
                    $prepareView->showRescueMode();
                } else {
                    $prepareView->showContent();
                }
            }
        }
    }

    /**
     * Show static content
     */
    private function staticView()
    {
        $response = new Response();
        $request = Request::createFromGlobals();
        $file = $request->get("file");
        $mapped = FileSystemHelper::getAssetPath($file);
        $data = @file_get_contents($mapped);
        $mtime = @filemtime($mapped);

        $response->prepare($request);
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        if (false !== $data) {
            $response
                ->setStatusCode(Response::HTTP_OK)
                ->setPublic()
                ->setMaxAge(0)
                ->setContent($data)
                ->setMaxAge(600)
                ->setLastModified(new \DateTime("@" . $mtime));
            $response->isNotModified($request);
        }
        $response->send();
    }
}
