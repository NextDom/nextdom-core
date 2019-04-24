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

require_once __DIR__ . "/src/core.php";

use NextDom\Enums\GetParams;
use NextDom\Enums\ViewType;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Client;
use NextDom\Helpers\Router;
use NextDom\Helpers\Utils;

// Test if NextDom is installed. Redirection to setup if necessary
if (!file_exists( NEXTDOM_DATA .'/config/common.config.php')) {
    header("location: install/setup.php");
}

$viewType = Utils::init(GetParams::VIEW_TYPE, '');
if ($viewType === '') {
    $getParams = ViewType::DESKTOP_VIEW;
    if (Client::isMobile()) {
        $getParams = ViewType::MOBILE_VIEW;
    }
    // Add all others GET parameters
    foreach ($_GET AS $var => $value) {
        $getParams .= '&' . $var . '=' . $value;
    }
    // Rewrite URL and reload with the good URL
    $url = 'index.php?' . GetParams::VIEW_TYPE . '=' . trim($getParams, '&');
    Utils::redirect($url);
}

// Show the content
// Start routing
$router = new Router($viewType);
if (!$router->show()) {
    throw new CoreException(__('Erreur : veuillez contacter l\'administrateur'));
}


 
