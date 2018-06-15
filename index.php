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
//use NextDom;
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/core/php/utils.inc.php');

use NextDom\Helper\Status;
use NextDom\Helper\Client;
use NextDom\Helper\Router;
use NextDom\Helper\Utils;

try {
    // Test si l'installation doit être lancée
    if (!file_exists(dirname(__FILE__) . '/core/config/common.config.php')) {
        header("location: install/setup.php");
    }
    // Paramètre v = Type de vue (mobile = m, desktop = d)

    // Redirection initiale
    $viewType = Utils::init('v', false);
    if ($viewType === false) {
        $getParams = 'd';
        if (Client::isMobile()) {
            $getParams = 'm';
        }
        foreach ($_GET AS $var => $value) {
            $getParams .= '&' . $var . '=' . $value;
        }
        // Réécrit l'url et recharge la page pour le bon device
        $url = 'index.php?v=' . trim($getParams, '&');
        Utils::redirect($url);
        die();
    }
    else {
        require_once __DIR__ . "/core/php/core.inc.php";
        Status::initRescueModeState();
        // Affichage
        $router = new Router($viewType);
        $result = $router->show();
        if (!$result) {
            throw new Exception('Erreur : veuillez contacter l\'administrateur');
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

 
