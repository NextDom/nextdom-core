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
 * Execute scenario or element of a scenario
 *
 * Usage :
 *  - jeeScenario scenario_id=SCENARIO_ID scenarioElement_id=SCENARIO_ELEMENT_ID tags=TAGS
 *  - jeeScenario scenario_id=SCENARIO_ID trigger=TRIGGER message=MESSAGE
 *
 * Parameters :
 *  - SCENARIO_ID : Id of the scenario to execute
 *  - SCENARIO_ELEMENT_ID : Id of the specific element to execute
 *  - TAGS : ???
 *  - TRIGGER : Trigger that started the scenario
 *  - MESSAGE : ???
 */

namespace NextDom;

use NextDom\Enums\ScenarioState;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Helpers\Utils;

/**
 * Block this script if open from webpage
 */
if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Statut: 404 Page non trouvée");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo '<h1>' . __('core.error-404') . '</h1>';
    exit();
}

require_once __DIR__ . "/../../src/core.php";

/**
 * Parse arguments
 */
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

$scenarioId = Utils::init('scenario_id');
if (Utils::init('scenarioElement_id') != '') {
    // Execute an element of the scenario
    ScenarioManager::doIn([
        'scenario_id' => $scenarioId,
        'scenarioElement_id' => Utils::init('scenarioElement_id'),
        'second' => 0,
        'tags' => json_decode(Utils::init('tags'), true)
    ]);
} else {
    // Execute a scenario
    $scenario = ScenarioManager::byId(intval($scenarioId));

    // Scenario not found
    if (!is_object($scenario)) {
        $errorMsg = __('scripts.scenario-not-found') . $scenarioId;
        LogHelper::addInfo('scenario', $errorMsg);
        die($errorMsg."\n");
    }

    if (is_numeric($scenario->getTimeout()) && $scenario->getTimeout() != '' && $scenario->getTimeout() !== 0) {
        set_time_limit($scenario->getTimeout(ConfigManager::byKey('maxExecTimeScript', 'core', 1) * 60));
    }

    try {
        // If scenario is in progress, wait 1 second. If scenario is in progress again, stop the script
        if ($scenario->getState() === ScenarioState::IN_PROGRESS && $scenario->getConfiguration('allowMultiInstance', 0) === 0) {
            sleep(1);
            if ($scenario->getState() == ScenarioState::IN_PROGRESS) {
                die();
            }
        }
        $scenario->execute(Utils::init('trigger'), Utils::init('message'));
    } catch (Exception $e) {
        LogHelper::addError('scenario', __('scripts.scenario') . $scenario->getHumanName() . '. ' . __('scripts.error') . $e->getMessage());
        $scenario->setState('error');
        $scenario->setLog(__('scripts.error') . $e->getMessage());
        $scenario->setPID('');
        $scenario->persistLog();
        die();
    }
}
