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

namespace NextDom\Enums;

/**
 * Class NextDomObj
 * @package NextDom\Enums
 */
class NextDomObj extends Enum
{
    const CMD = 'cmd';
    const EQLOGIC = 'eqLogic';
    const DATASTORE = 'dataStore';
    const INTERACT = 'interact';
    const INTERACT_DEF = 'interactDef';
    const INTERACT_QUERY = 'interactQuery';
    const JEE_OBJECT = 'jeeObject';
    const OBJECT = 'object';
    const PLAN = 'plan';
    const PLAN_OBJECT = 'planObject';
    const PLUGIN = 'plugin';
    const SCENARIO = 'scenario';
    const SCENARIOS = 'scenarios';
    const UPDATE = 'update';
    const VIEW = 'view';
}
