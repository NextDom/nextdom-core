<?php

/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Rest;

use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\Scenario;

/**
 * Class ScenarioRest
 *
 * @package NextDom\Rest
 */
class ScenarioRest
{
    /**
     * Default name of group when scenario doesn't have group
     */
    private static $NO_GROUP_CODE = 'no-group';

    /**
     * Get all scenarios
     *
     * @throws \Exception
     */
    public static function getAll()
    {
        $scenarios = ScenarioManager::all();
        return self::prepareResults($scenarios);
    }

    /**
     * Prepare result for response
     *
     * @param Scenario[] $scenarios Array of scenarios to convert
     *
     * @return array
     * @throws \Exception
     */
    private static function prepareResults($scenarios)
    {
        $result = [];
        foreach ($scenarios as $scenario) {
            $scenarioRow = [];
            $scenarioRow['id'] = $scenario->getId();
            $scenarioRow['name'] = $scenario->getName();
            $scenarioRow['displayIcon'] = $scenario->getDisplay('icon');
            $scenarioRow['state'] = $scenario->getState();
            $scenarioRow['active'] = $scenario->isActive();
            $result[] = $scenarioRow;
        }
        return $result;
    }

    /**
     * Get all scenarios ordered by group
     *
     * @throws \Exception
     */
    public static function getAllByGroup()
    {
        $scenarios = ScenarioManager::all();
        return self::prepareResultsOrderedByGroup($scenarios);
    }

    /**
     * Prepare result for response\n
     * Associative array by group with following keys :
     *  - id
     *  - name
     *  - displayIcon
     *
     * @param Scenario[] $scenarios Array of scenarios to convert
     *
     * @return array List of scenarios grouped
     *
     * @throws \Exception
     */
    private static function prepareResultsOrderedByGroup($scenarios)
    {
        $result = [];
        foreach ($scenarios as $scenario) {
            $scenarioRow = [];
            $scenarioRow['id'] = $scenario->getId();
            $scenarioRow['name'] = $scenario->getName();
            $scenarioRow['displayIcon'] = $scenario->getDisplay('icon');
            $scenarioRow['state'] = $scenario->getState();
            $scenarioRow['active'] = $scenario->isActive();
            $groupName = $scenario->getGroup();
            if (empty($groupName)) {
                $groupName = self::$NO_GROUP_CODE;
            }
            if (!array_key_exists($groupName, $result)) {
                $result[$groupName] = [];
            }
            $result[$groupName][] = $scenarioRow;
        }
        return $result;
    }

    /**
     * Launch scenario
     *
     * @param int $scenarioId Id of the scenario to start
     *
     * @return bool True if success
     *
     * @throws \Exception
     */
    public static function launch(int $scenarioId)
    {
        $scenario = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            return false;
        }
        if (!$scenario->isActive()) {
            return false;
        }
        $scenario->launch('user', 'Manual launch', 0);
        return true;
    }

    /**
     * Enable scenario
     * @param int $scenarioId Id of the scenario to enable
     * @return bool True on success
     * @throws \Exception
     */
    public static function enable(int $scenarioId)
    {
        return self::changeScenarioActiveState($scenarioId, true);
    }

    /**
     * Disable scenario
     * @param int $scenarioId Id of the scenario to disable
     * @param bool $newState New state
     * @return bool True on success
     * @throws \Exception
     */
    private static function changeScenarioActiveState(int $scenarioId, bool $newState)
    {
        $scenario = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            return false;
        }
        if ($newState) {
            $scenario->setIsActive(1);
        } else {
            $scenario->setIsActive(0);
        }
        $scenario->save();
        return true;
    }

    /**
     * Disable scenario
     * @param int $scenarioId Id of the scenario to disable
     * @return bool True on success
     * @throws \Exception
     */
    public static function disable(int $scenarioId)
    {
        return self::changeScenarioActiveState($scenarioId, false);
    }

    /**
     * Stop scenario
     *
     * @param int $scenarioId Id of the scenario to stop
     *
     * @return bool True if success
     *
     * @throws \Exception
     */
    public static function stop(int $scenarioId)
    {
        $scenario = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            return false;
        }
        $scenario->stop();
        return true;
    }

}