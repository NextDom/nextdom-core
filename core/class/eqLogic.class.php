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

use NextDom\Managers\EqLogicManager;
use NextDom\Enums\EqLogicViewTypeEnum;

/**
 * Class eqLogic interface for Jeedom plugins
 */
class eqLogic extends NextDom\Model\Entity\EqLogic
{
    public static function getAllTags() {
        return EqLogicManager::getAllTags();
    }

    public static function byId($_id)
    {
        return EqLogicManager::byId($_id);
    }

    public static function all($_onlyEnable = false)
    {
        return EqLogicManager::all($_onlyEnable);
    }

    public static function byEqRealId($_eqReal_id)
    {
        return EqLogicManager::byEqRealId($_eqReal_id);
    }

    public static function byObjectId($_object_id, $_onlyEnable = true, $_onlyVisible = false, $_eqType_name = null, $_logicalId = null, $_orderByName = false)
    {
        return EqLogicManager::byObjectId($_object_id, $_onlyEnable, $_onlyVisible, $_eqType_name, $_logicalId, $_orderByName);
    }

    public static function byLogicalId($_logicalId, $_eqType_name, $_multiple = false)
    {
        return EqLogicManager::byLogicalId($_logicalId, $_eqType_name, $_multiple);
    }

    public static function byType($_eqType_name, $_onlyEnable = false)
    {
        return EqLogicManager::byType($_eqType_name, $_onlyEnable);
    }

    public static function byCategorie($_category)
    {
        return EqLogicManager::byCategory($_category);
    }

    public static function byTypeAndSearhConfiguration($_eqType_name, $_configuration)
    {
        return EqLogicManager::byTypeAndSearhConfiguration($_eqType_name, $_configuration);
    }

    public static function searchConfiguration($_configuration, $_type = null)
    {
        return EqLogicManager::searchConfiguration($_configuration, $_type);
    }

    public static function listByTypeAndCmdType($_eqType_name, $_typeCmd, $subTypeCmd = '')
    {
        return EqLogicManager::listByTypeAndCmdType($_eqType_name, $_typeCmd, $subTypeCmd);
    }

    public static function listByObjectAndCmdType($_object_id, $_typeCmd, $subTypeCmd = '')
    {
        return EqLogicManager::listByObjectAndCmdType($_object_id, $_typeCmd, $subTypeCmd);
    }

    public static function allType()
    {
        return EqLogicManager::allType();
    }

    public static function checkAlive()
    {
        EqLogicManager::checkAlive();
    }

    public static function byTimeout($_timeout = 0, $_onlyEnable = false)
    {
        return EqLogicManager::byTimeout($_timeout, $_onlyEnable);
    }

    public static function byObjectNameEqLogicName($_object_name, $_eqLogic_name)
    {
        return EqLogicManager::byObjectNameEqLogicName($_object_name, $_eqLogic_name);
    }

    public static function toHumanReadable($_input)
    {
        return EqLogicManager::toHumanReadable($_input);
    }

    public static function fromHumanReadable($_input)
    {
        return EqLogicManager::fromHumanReadable($_input);
    }

    public static function clearCacheWidget()
    {
        EqLogicManager::clearCacheWidget();
    }

    public static function generateHtmlTable($_nbLine, $_nbColumn, $_options = array())
    {
        return EqLogicManager::generateHtmlTable($_nbLine, $_nbColumn, $_options);
    }
}
