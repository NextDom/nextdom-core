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
 * Class ConfigKey
 * @package NextDom\Enums
 */
class ConfigKey extends Enum
{
    const API = 'api';
    const CMD_PUSH_URL = 'cmdPushUrl';
    const ENABLE_CRON = 'enableCron';
    const ENABLE_SCENARIO = 'enableScenario';
    const HARDWARE_NAME = 'hardware_name';
    const IGNORE_HOUR_CHECK = 'ignoreHourCheck';
    const LOG_LEVEL = 'log::level';
    const MARKET_ADDRESS = 'market::address';
    const NEXTDOM_INSTALL_KEY = 'nextdom::installKey';
    const OBJECT_SUMMARY = 'object:summary';
    const TMP_FOLDER = 'folder::tmp';
    const UPDATE_CMD_TO_VALUE = 'updateCmdToValue';
    const WIDGET_BACKGROUND_OPACITY = 'widget::background-opacity';
}
