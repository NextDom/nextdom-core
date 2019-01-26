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

class ScenarioExpressionEnum extends Enum
{
    const ICON = 'icon';
    const WAIT = 'wait';
    const SLEEP = 'sleep';
    const STOP = 'stop';
    const LOG = 'log';
    const MESSAGE = 'message';
    const ALERT = 'alert';
    const POPUP = 'popup';
    const EQUIPMENT = 'equipment';
    CONST EQUIPEMENT = 'equipement';
    const GOTODESIGN = 'gotodesign';
    const SCENARIO = 'scenario';
    const VARIABLE = 'variable';
    const DELETE_VARIABLE = 'delete_variable';
    const ASK = 'ask';
    const NEXTDOM_POWEROFF = 'nextdom_poweroff';
    const SCENARIO_RETURN = 'scenario_return';
    const REMOVE_INAT = 'remove_inat';
    const REPORT = 'report';
    const TAG = 'tag';
    const EVENT = 'event';
    const CMD = 'cmd';
}
