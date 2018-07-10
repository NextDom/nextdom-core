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

namespace NextDom\Market\Ajax;

use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Market\Ajax\MarketAjaxParser;
use NextDom\Exceptions\CoreException;

header('Content-Type: application/json');

require_once __DIR__ . '/../../../php/core.inc.php';
require_once 'MarketAjaxParser.php';

try {
    \include_file('core', 'authentification', 'php');

    Status::initConnectState();
    Status::isConnectedAdminOrFail();

    \ajax::init();

    // Récupération des données envoyées
    $action = Utils::init('action');
    $params = Utils::init('params');
    $data = Utils::init('data');
    $result = MarketAjaxParser::parse($action, $params, $data);

    if ($result !== false) {
        \ajax::success($result);
    }

    throw new \Exception(__('Aucune méthode correspondante à : ', __FILE__) . $action);
} catch (\Exception $e) {
    \ajax::error(displayException($e), $e->getCode());
 
} catch (CoreException $exc) {
    \ajax::error(displayException($exc), $exc->getCode());
}

