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

require_once __DIR__ . '/../../../../core/php/core.inc.php';

class plugin4tests extends eqLogic
{
    public static function cron() {
        log::add('plugin4tests', 'info', 'CRON TEST');
    }

    public static function cron5() {
        log::add('plugin4tests', 'error', 'CRON ERROR');
    }

    public function preInsert()
    {
        
    }

    public function postInsert()
    {
        
    }

    public function preSave()
    {
        
    }

    public function postSave()
    {
        
    }

    public function preUpdate()
    {
        
    }

    public function postUpdate()
    {
        
    }

    public function preRemove()
    {
        
    }

    public function postRemove()
    {
        
    }
}


class plugin4testsCmd extends cmd
{

    public function execute($_options = array())
    {
        log::add('plugin4tests', 'info', 'Command tested');
    }
}
