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
 * Common configuration keys
 * @package NextDom\Enums
 */
class EqLogicConfigKey extends Enum
{
    const BATTERY_WARNING_THRESHOLD = 'battery_warning_threshold';
    const BATTERY_DANGER_THRESHOLD = 'battery_danger_threshold';
    const BATTERY_TIME = 'batterytime';
    const BATTERY_TYPE = 'battery_type';
    const NO_BATTERY_CHECK = 'noBatterieCheck';
    const REPEAT_EVENT_MANAGEMENT = 'repeatEventManagement';
}
