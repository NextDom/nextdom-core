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

/**
 * ???
 *
 * Usage :
 *  - jeeListener.php listener_id=LISTENER_ID event_id=EVENT_ID plugin_id=PLUGIN_ID value=VALUE datetime=DATETIME
 *
 * Parameters :
 *  - LISTENER_ID : ???
 *  - EVENT_ID : ???
 *  - PLUGIN_ID : ???
 *  - VALUE : ???
 *  - DATETIME : ???
 */

namespace NextDom;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ListenerManager;
use NextDom\Helpers\Utils;

require_once __DIR__ . "/../../src/core.php";

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

$maxExecTimeScript = 60;

if (ConfigManager::byKey('maxExecTimeScript', 60) != '') {
    $maxExecTimeScript = ConfigManager::byKey('maxExecTimeScript', 60);
}
set_time_limit($maxExecTimeScript);

$listenerId = Utils::init('listener_id');
$eventId = Utils::init('event_id');
if ($listenerId == '') {
    foreach (CmdManager::byValue($eventId, 'info') as $cmd) {
        $cmd->event($cmd->execute(), null, 2);
    }
} else {
    try {
        if ($listenerId == '') {
            throw new CoreException(__('scripts.listener-id-cannot-be-empty'));
        }
        $listener = ListenerManager::byId($listenerId);
        if (!is_object($listener)) {
            throw new CoreException(__('scripts.listener-not-found') . $listenerId);
        }
    } catch (\Exception $e) {
        LogHelper::addError(Utils::init('plugin_id', 'plugin'), $e->getMessage());
        die($e->getMessage());
    }
    $listener->execute($eventId, trim(Utils::init('value'), "'"), trim(Utils::init('datetime'), "'"));
}
