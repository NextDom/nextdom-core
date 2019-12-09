<?php

/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Rest;

use NextDom\Managers\EqLogicManager;
use NextDom\Model\Entity\EqLogic;

/**
 * Class EqLogicRest
 *
 * @package NextDom\Rest
 */
class EqLogicRest
{
    /**
     * Get all eqLogics
     *
     * @throws \Exception
     */
    public static function getAll()
    {
        $eqLogics = EqLogicManager::all();
        return self::prepareResults($eqLogics);
    }

    /**
     * Prepare result for response\n
     * Associative array with following keys :
     *  - id
     *  - name
     *  - type (plugin id)
     *  - objectId
     *  - enable
     *  - visible
     *  - configuration
     *
     * @param EqLogic[] $eqLogics Array of eqLogics to convert
     *
     * @return array
     */
    private static function prepareResults($eqLogics)
    {
        $result = [];
        foreach ($eqLogics as $eqLogic) {
            $eqLogicRow = [];
            $eqLogicRow['id'] = $eqLogic->getId();
            $eqLogicRow['name'] = $eqLogic->getName();
            $eqLogicRow['type'] = $eqLogic->getEqType_name();
            $eqLogicRow['objectId'] = $eqLogic->getObject_id();
            $eqLogicRow['enable'] = $eqLogic->isEnabled();
            $eqLogicRow['visible'] = $eqLogic->isVisible();
            $eqLogicRow['configuration'] = $eqLogic->getConfiguration();
            $result[] = $eqLogicRow;
        }
        return $result;
    }

    /**
     * Get all eqLogics in room
     *
     * @param int $roomId Id of the room
     *
     * @return array List of eqLogics data in the room
     *
     * @throws \Exception
     */
    public static function getByRoom(int $roomId)
    {
        $eqLogics = EqLogicManager::byObjectId($roomId);
        return self::prepareResults($eqLogics);
    }

    /**
     * Get all visibles eqLogics in room
     *
     * @param int $roomId Id of the room
     *
     * @return array List of eqLogics data in the room
     *
     * @throws \Exception
     */
    public static function getVisibleByRoom(int $roomId)
    {
        $eqLogics = EqLogicManager::byObjectId($roomId, true, true);
        return self::prepareResults($eqLogics);
    }
}