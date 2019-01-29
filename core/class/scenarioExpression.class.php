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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Enums\ScenarioExpressionEnum;

class scenarioExpression extends \NextDom\Model\Entity\ScenarioExpression
{
    public static function byId($_id)
    {
        return ScenarioExpressionManager::byId($_id);
    }

    public static function all()
    {
        return ScenarioExpressionManager::all();
    }

    public static function byscenarioSubElementId($_scenarioSubElementId)
    {
        return ScenarioExpressionManager::byScenarioSubElementId($_scenarioSubElementId);
    }

    public static function searchExpression($_expression, $_options = null, $_and = true)
    {
        return ScenarioExpressionManager::searchExpression($_expression, $_options, $_and);
    }

    public static function byElement($_element_id)
    {
        return ScenarioExpressionManager::byElement($_element_id);
    }

    public static function getExpressionOptions($_expression, $_options)
    {
        return ScenarioExpressionManager::getExpressionOptions($_expression, $_options);
    }

    public static function humanAction($_action)
    {
        return ScenarioExpressionManager::humanAction($_action);
    }

    public static function rand($_min, $_max)
    {
        return ScenarioExpressionManager::rand($_min, $_max);
    }

    public static function randText($_sValue)
    {
        return ScenarioExpressionManager::randText($_sValue);
    }

    public static function scenario($_scenario)
    {
        return ScenarioExpressionManager::scenario($_scenario);
    }

    public static function eqEnable($_eqLogic_id)
    {
        return ScenarioExpressionManager::eqEnable($_eqLogic_id);
    }

    public static function average($_cmd_id, $_period = '1 hour')
    {
        return ScenarioExpressionManager::average($_cmd_id, $_period);
    }

    public static function averageBetween($_cmd_id, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::averageBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function max($_cmd_id, $_period = '1 hour')
    {
        return ScenarioExpressionManager::max($_cmd_id, $_period);
    }

    public static function maxBetween($_cmd_id, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::maxBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function wait($_condition, $_timeout = 7200)
    {
        return ScenarioExpressionManager::wait($_condition, $_timeout);
    }

    public static function min($_cmd_id, $_period = '1 hour')
    {
        return ScenarioExpressionManager::min($_cmd_id, $_period);
    }

    public static function minBetween($_cmd_id, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::minBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function median()
    {
        return ScenarioExpressionManager::median();
    }

    public static function tendance($_cmd_id, $_period = '1 hour', $_threshold = '')
    {
        return ScenarioExpressionManager::tendance($_cmd_id, $_period, $_threshold);
    }

    public static function lastStateDuration($_cmd_id, $_value = null)
    {
        return ScenarioExpressionManager::lastStateDuration($_cmd_id, $_value);
    }

    public static function stateChanges($_cmd_id, $_value = null, $_period = '1 hour')
    {
        return ScenarioExpressionManager::stateChanges($_cmd_id, $_value, $_period);
    }

    public static function stateChangesBetween($_cmd_id, $_value, $_startDate, $_endDate = null)
    {
        return ScenarioExpressionManager::stateChangesBetween($_cmd_id, $_value, $_startDate, $_endDate);
    }

    public static function duration($_cmd_id, $_value, $_period = '1 hour')
    {
        return ScenarioExpressionManager::duration($_cmd_id, $_value, $_period);
    }

    public static function durationBetween($_cmd_id, $_value, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::durationBetween($_cmd_id, $_value, $_startDate, $_endDate);
    }

    public static function lastBetween($_cmd_id, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::lastBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function statistics($_cmd_id, $_calc, $_period = '1 hour')
    {
        return ScenarioExpressionManager::statistics($_cmd_id, $_calc, $_period);
    }

    public static function statisticsBetween($_cmd_id, $_calc, $_startDate, $_endDate)
    {
        return ScenarioExpressionManager::statisticsBetween($_cmd_id, $_calc, $_startDate, $_endDate);
    }

    public static function variable($_name, $_default = '')
    {
        return ScenarioExpressionManager::variable($_name, $_default);
    }

    public static function stateDuration($_cmd_id, $_value = null)
    {
        return ScenarioExpressionManager::stateDuration($_cmd_id, $_value);
    }

    public static function lastChangeStateDuration($_cmd_id, $_value)
    {
        return ScenarioExpressionManager::lastChangeStateDuration($_cmd_id, $_value);
    }

    public static function odd($_value)
    {
        return ScenarioExpressionManager::odd($_value);
    }

    public static function lastScenarioExecution($_scenario_id)
    {
        return ScenarioExpressionManager::lastScenarioExecution($_scenario_id);
    }

    public static function collectDate($_cmd_id, $_format = 'Y-m-d H:i:s')
    {
        return ScenarioExpressionManager::collectDate($_cmd_id, $_format);
    }

    public static function valueDate($_cmd_id, $_format = 'Y-m-d H:i:s')
    {
        return ScenarioExpressionManager::valueDate($_cmd_id, $_format);
    }

    public static function lastCommunication($_eqLogic_id, $_format = 'Y-m-d H:i:s') {
        return ScenarioExpressionManager::lastCommunication($_eqLogic_id, $_format);
    }

    public static function value($_cmd_id) {
        return ScenarioExpressionManager::value($_cmd_id);
    }

    public static function randomColor($_rangeLower, $_rangeHighter)
    {
        return ScenarioExpressionManager::randomColor($_rangeLower, $_rangeHighter);
    }

    public static function trigger($_name = '', &$_scenario = null)
    {
        return ScenarioExpressionManager::trigger($_name, $_scenario);
    }

    public static function triggerValue(&$_scenario = null)
    {
        return ScenarioExpressionManager::triggerValue($_scenario);
    }

    public static function round($_value, $_decimal = 0)
    {
        return ScenarioExpressionManager::round($_value, $_decimal);
    }

    public static function time_op($_time, $_value)
    {
        return ScenarioExpressionManager::time_op($_time, $_value);
    }

    public static function time_between($_time, $_start, $_end)
    {
        return ScenarioExpressionManager::time_between($_time, $_start, $_end);
    }

    public static function time_diff($_date1, $_date2, $_format = 'd')
    {
        return ScenarioExpressionManager::time_diff($_date1, $_date2, $_format);
    }

    public static function time($_value)
    {
        return ScenarioExpressionManager::time($_value);
    }

    public static function formatTime($_time)
    {
        return ScenarioExpressionManager::formatTime($_time);
    }

    public static function name($_type, $_cmd_id)
    {
        return ScenarioExpressionManager::name($_type, $_cmd_id);
    }

    public static function getRequestTags($_expression)
    {
        return ScenarioExpressionManager::getRequestTags($_expression);
    }

    public static function tag(&$_scenario = null, $_name, $_default = '')
    {
        return ScenarioExpressionManager::tag($_scenario, $_name, $_default);
    }

    public static function setTags($_expression, &$_scenario = null, $_quote = false, $_nbCall = 0)
    {
        return ScenarioExpressionManager::setTags($_expression, $_scenario, $_quote, $_nbCall);
    }

    public static function createAndExec($_type, $_cmd, $_options = null)
    {
        return ScenarioExpressionManager::createAndExec($_type, $_cmd, $_options);
    }
}