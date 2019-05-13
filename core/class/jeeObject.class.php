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

use NextDom\Managers\ObjectManager;

require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

class jeeObject extends \NextDom\Model\Entity\JeeObject
{
    public static function byId($_id)
    {
        return ObjectManager::byId($_id);
    }

    public static function byName($_name)
    {
        return ObjectManager::byName($_name);
    }

    public static function all($_onlyVisible = false)
    {
        return ObjectManager::all($_onlyVisible);
    }

    public static function rootObject($_all = false, $_onlyVisible = false)
    {
        return ObjectManager::getRootObjects($_all, $_onlyVisible);
    }

    public static function buildTree($_object = null, $_visible = true)
    {
        return ObjectManager::buildTree($_object, $_visible);
    }

    public static function fullData($_restrict = array())
    {
        return ObjectManager::fullData($_restrict);
    }

    public static function searchConfiguration($_search)
    {
        return ObjectManager::searchConfiguration($_search);
    }

    public static function deadCmd()
    {
        return ObjectManager::deadCmd();
    }

    public static function checkSummaryUpdate($_cmd_id)
    {
        ObjectManager::checkSummaryUpdate($_cmd_id);
    }

    public static function getGlobalSummary($_key)
    {
        return ObjectManager::getGlobalSummary($_key);
    }

    public static function getGlobalHtmlSummary($_key)
    {
        return ObjectManager::getGlobalHtmlSummary($_key);
    }

    public static function createSummaryToVirtual($_key = '')
    {
        ObjectManager::createSummaryToVirtual($_key);
    }
}
