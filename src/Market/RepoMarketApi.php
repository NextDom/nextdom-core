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

use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Api;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;
use NextDom\Model\Entity\Cron;

header('Access-Control-Allow-Origin: *');
require_once __DIR__ . "/../../src/core.php";
if (UserManager::isBanned() && false) {
    header("Status: 404 Not Found");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    require(NEXTDOM_ROOT . '/public/404.html');
    die();
}
try {
    if (!Api::apiAccess(Utils::init('apikey'), 'apimarket')) {
        UserManager::failedLogin();
        throw new CoreException(__('Vous n\'êtes pas autorisé à effectuer cette action 1, IP : ') . NetworkHelper::getClientIp());
    }
    if (Utils::init(AjaxParams::ACTION) == 'resync') {
        if (NextDomHelper::isStarted() && ConfigManager::byKey(ConfigKey::ENABLE_CRON, Common::CORE, 1, true) == 0) {
            die(__('Tous les crons sont actuellement désactivés'));
        }
        $cron = new Cron();
        $cron->setClass('RepoMarket');
        $cron->setFunction(Utils::init(AjaxParams::TEST));
        $cron->setOnce(1);
        $cron->save();
        $cron->start();
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    LogHelper::addError(LogTarget::JEE_EVENT, $e->getMessage());
}
