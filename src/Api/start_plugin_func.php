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
 * Execute plugin function
 *
 * Usage :
 *  - start_plugin_func.php plugin_id=PLUGIN_ID function=FUNCTION [ callInstallFunction=1 ]
 *
 * Parameters :
 *  - plugin_id : Plugin ID
 *  - function : Function to call
 *  - callInstallFunction : Call install function
 */

namespace NextDom;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;

require_once __DIR__ . "/../../src/core.php";

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

$pluginId = Utils::init('plugin_id');
try {
    set_time_limit(ConfigManager::byKey('maxExecTimeScript', 'core', 10));

    if ($pluginId == '') {
        throw new CoreException(__('Le plugin ID ne peut être vide'));
    }
    $plugin = PluginManager::byId($pluginId);
    if (!is_object($plugin)) {
        throw new CoreException(__('Plugin non trouvé : ') . $pluginId);
    }
    $function = Utils::init('function');
    if ($function == '') {
        throw new CoreException(__('La fonction ne peut être vide'));
    }
    if (Utils::init('callInstallFunction', 0) == 1) {
        $plugin->callInstallFunction($function, true);
    } else {
        if (!class_exists($pluginId) || !method_exists($pluginId, $function)) {
            throw new CoreException(__('Il n\'existe aucune méthode : ') . $pluginId . '::' . $function);
        }
        $pluginId::$function();
    }
} catch (\Throwable $e) {
    LogHelper::addError($pluginId, $e->getMessage());
    die($e->getMessage());
}
