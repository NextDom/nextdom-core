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

require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Helpers\SystemHelper;

class system
{

    public static function loadCommand()
    {
        return SystemHelper::loadCommand();
    }

    public static function getDistrib()
    {
        return SystemHelper::getDistrib();
    }

    public static function get($_key = '')
    {
        return SystemHelper::getCommand($_key);
    }

    public static function getCmdSudo()
    {
        return SystemHelper::getCmdSudo();
    }

    public static function fuserk($_port, $_protocol = 'tcp')
    {
        if (file_exists($_port)) {
            SystemHelper::killProcessesWhichUsingFile($_port);
        }
        else {
            SystemHelper::killProcessesWhichUsingPort($_port, $_protocol);
        }
    }

    public static function ps($_find, $_without = null)
    {
        return SystemHelper::ps($_find, $_without);
    }

    public static function kill($_find = '', $_kill9 = true)
    {
        return SystemHelper::kill($_find, $_kill9);
    }

    public static function php($arguments, $_sudo = false)
    {
        return SystemHelper::php($arguments, $_sudo);
    }

    public static function getWWWUid()
    {
        return SystemHelper::getWWWUid();
    }

    public static function getWWWGid()
    {
        return SystemHelper::getWWWGid();
    }
}
