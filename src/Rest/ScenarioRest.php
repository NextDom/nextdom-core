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
     */
    private static function prepareResults($scenarios)
    {
        $result = [];
        foreach ($scenarios as $scenario) {
            $scenarioRow = [];
            $scenarioRow['id'] = $scenario->getId();
            $scenarioRow['name'] = $scenario->getName();
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
     * Prepare results for response with groups
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
        $scenario->launch('user', 'Manual launch', 0);
        return true;
    }

}