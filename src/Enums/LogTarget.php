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
 * Class LogTarget
 * @package NextDom\Enums
 */
class LogTarget extends Enum
{
    const BACKUP = 'backup';
    const CMD = 'cmd';
    const CONNECTION = 'connection';
    const CRON = 'cron';
    const EVENT = 'event';
    const INTERACT = 'interact';
    const JEE_EVENT = 'jeeEvent';
    const LISTENER = 'listener';
    const MARKET = 'market';
    const MIGRATION = 'migration';
    const NETWORK = 'network';
    const NEXTDOM = 'nextdom';
    const PLUGIN = 'plugin';
    const REPORT = 'report';
    const RESTORE = 'restore';
    const STARTING = 'starting';
    const SCENARIO = 'scenario';
    const UPDATE = 'update';
}
