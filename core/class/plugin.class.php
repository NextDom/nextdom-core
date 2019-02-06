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
require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

use NextDom\Managers\PluginManager;

class plugin extends \NextDom\Model\Entity\Plugin
{
    public static function byId($id)
    {
        return PluginManager::byId($id);
    }

    public static function getPathById($id)
    {
        return PluginManager::getPathById($id);
    }

    public static function listPlugin($activateOnly = false, $orderByCaterogy = false, $translate = true, $nameOnly = false)
    {
        return PluginManager::listPlugin($activateOnly, $orderByCaterogy, $nameOnly);
    }

    public static function orderPlugin($a, $b)
    {
        return PluginManager::orderPlugin($a, $b);
    }

    public static function cron()
    {
        PluginManager::cron();
    }

    public static function cron5()
    {
        PluginManager::cron5();
    }

    public static function cron15()
    {
        PluginManager::cron15();
    }

    public static function cron30()
    {
        PluginManager::cron30();
    }

    public static function cronDaily()
    {
        PluginManager::cronDaily();
    }

    public static function cronHourly()
    {
        PluginManager::cronHourly();
    }

    public static function heartbeat()
    {
        PluginManager::heartbeat();
    }

    public static function start()
    {
        PluginManager::start();
    }

    public static function stop()
    {
        PluginManager::stop();
    }

    public static function checkDeamon()
    {
        PluginManager::checkDeamon();
    }
}
