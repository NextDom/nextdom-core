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

namespace NextDom\Repo;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Api;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;
use NextDom\Model\Entity\Cron;

header('Access-Control-Allow-Origin: *');
require_once __DIR__ . "/../../src/core.php";
if (UserManager::isBanned() && false) {
    header("Status: 404 Not Found");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    die();
}
try {
    if (!Api::apiAccess(init('apikey'), 'apimarket')) {
        UserManager::failedLogin();
        sleep(5);
        throw new CoreException(__('Vous n\'êtes pas autorisé à effectuer cette action 1, IP : ', __FILE__) . getClientIp());
    }
    if (init('action') == 'resync') {
        if (NextDomHelper::isStarted() && ConfigManager::byKey('enableCron', 'core', 1, true) == 0) {
            die(__('Tous les crons sont actuellement désactivés', __FILE__));
        }
        $cron = new Cron();
        $cron->setClass('RepoMarket');
        $cron->setFunction(init('test'));
        $cron->setOnce(1);
        $cron->save();
        $cron->start();
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    LogHelper::add('jeeEvent', 'error', $e->getMessage());
}
