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

use NextDom\Managers\UpdateManager;

class update extends \NextDom\Model\Entity\Update
{
    public static function checkAllUpdate($_filter = '', $_findNewObject = true)
    {
        UpdateManager::checkAllUpdate($_filter, $_findNewObject);
    }

    public static function listRepo()
    {
        return UpdateManager::listRepo();
    }

    public static function repoById($_id)
    {
        return UpdateManager::repoById($_id);
    }

    public static function updateAll($_filter = '')
    {
        return UpdateManager::updateAll($_filter);
    }

    public static function byId($_id)
    {
        return UpdateManager::byId($_id);
    }

    public static function byStatus($_status)
    {
        return UpdateManager::byStatus($_status);
    }

    public static function byLogicalId($_logicalId)
    {
        return UpdateManager::byLogicalId($_logicalId);
    }

    public static function byType($_type)
    {
        return UpdateManager::byType($_type);
    }

    public static function byTypeAndLogicalId($_type, $_logicalId)
    {
        return UpdateManager::byTypeAndLogicalId($_type, $_logicalId);
    }

    public static function all($_filter = '')
    {
        return UpdateManager::all($_filter);
    }

    public static function nbNeedUpdate()
    {
        return UpdateManager::nbNeedUpdate();
    }

    public static function findNewUpdateObject()
    {
        UpdateManager::findNewUpdateObject();
    }

    public static function listCoreUpdate()
    {
        return UpdateManager::listCoreUpdate();
    }
}
