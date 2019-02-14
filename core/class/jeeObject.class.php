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

use NextDom\Managers\JeeObjectManager;

require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

class jeeObject extends \NextDom\Model\Entity\JeeObject
{
    public static function byId($_id)
    {
        return JeeObjectManager::byId($_id);
    }

    public static function byName($_name)
    {
        return JeeObjectManager::byName($_name);
    }

    public static function all($_onlyVisible = false)
    {
        return JeeObjectManager::all($_onlyVisible);
    }

    public static function rootObject($_all = false, $_onlyVisible = false)
    {
        return JeeObjectManager::rootObject($_all, $_onlyVisible);
    }

    public static function buildTree($_object = null, $_visible = true)
    {
        return JeeObjectManager::buildTree($_object, $_visible);
    }

    public static function fullData($_restrict = array())
    {
        return JeeObjectManager::fullData($_restrict);
    }

    public static function searchConfiguration($_search)
    {
        return JeeObjectManager::searchConfiguration($_search);
    }

    public static function deadCmd()
    {
        return JeeObjectManager::deadCmd();
    }

    public static function checkSummaryUpdate($_cmd_id)
    {
        JeeObjectManager::checkSummaryUpdate($_cmd_id);
    }

    public static function getGlobalSummary($_key)
    {
        return JeeObjectManager::getGlobalSummary($_key);
    }

    public static function getGlobalHtmlSummary($_key)
    {
        return JeeObjectManager::getGlobalHtmlSummary($_key);
    }

    public static function createSummaryToVirtual($_key = '')
    {
        JeeObjectManager::createSummaryToVirtual($_key);
    }
}
