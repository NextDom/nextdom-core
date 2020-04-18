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

use Exception;
use NextDom\Managers\JeeObjectManager;
use NextDom\Model\Entity\JeeObject;

/**
 * Class RoomRest
 *
 * @package NextDom\Rest
 */
class SummaryRest
{
    /**
     * Get all rooms and data
     */
    public static function getAll()
    {
        $result = [];
        // Limit number of SQL queries
        $rooms = JeeObjectManager::all();
        $eqLogics = EqLogicRest::getAll();
        $cmds = CmdRest::getAll();
        // For cmd location
        $eqLogicsToRooms = [];
        foreach ($rooms as $room) {
            $roomData = [];
            $roomData['id'] = $room->getId();
            $roomData['name'] = $room->getName();
            $roomData['icon'] = $room->getDisplay('icon');
            $roomData['eqLogics'] = [];
            $result[$roomData['id']] = $roomData;
        }
        if (is_array($eqLogics)) {
            foreach ($eqLogics as $eqLogic) {
                $result[$eqLogic['objectId']]['eqLogics'][$eqLogic['id']] = $eqLogic;
                $result[$eqLogic['objectId']]['eqLogics'][$eqLogic['id']]['cmds'] = [];
                $eqLogicsToRooms[$eqLogic['id']] = $eqLogic['objectId'];
            }
        }
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                $eqLogicId = $cmd['eqLogicId'];
                $roomId = $eqLogicsToRooms[$eqLogicId];
                $result[$roomId]['eqLogics'][$eqLogicId]['cmds'][$cmd['id']] = $cmd;
            }
        }
        return $result;
    }
    
    /**
     * Get tree of rooms from room defined by the user or root room
     *
     * @throws Exception
     */
    public static function getDefaultRoomTree()
    {
        $authenticator = Authenticator::getInstance();
        $user = $authenticator->getConnectedUser();
        $defaultRoom = JeeObjectManager::getDefaultUserRoom($user);
        if ($defaultRoom === false) {
            return false;
        } else {
            return self::getRoomTree($defaultRoom->getId());
        }
    }

    /**
     * Get tree of rooms from specific room
     *
     * @param int $roomId
     *
     * @return JeeObjectManager[] Tree of rooms
     *
     * @throws Exception
     */
    public static function getRoomTree(int $roomId)
    {
        $rootRoom = JeeObjectManager::byId($roomId);
        $rootRoom->getChilds();
        $result = self::prepareResult($rootRoom);
        return $result;
    }

    /**
     * Prepare result for response\n
     * Associative array with following keys :
     *  - id
     *  - name
     *  - icon
     *  - eqLogics (if exists)
     *  - children (if exists)
     *
     * @param JeeObject $room Room object to prepare
     *
     * @return array Data for response
     *
     * @throws Exception
     */
    private static function prepareResult(JeeObject $room)
    {
        $result = [];
        $result['id'] = $room->getId();
        $result['name'] = $room->getName();
        $result['icon'] = $room->getDisplay('icon');
        // Get eqLogics attached to the room
        $eqLogicsData = self::addEqLogicsInformations($room->getId());
        if (!empty($eqLogicsData)) {
            $result['eqLogics'] = $eqLogicsData;
        }
        // Get all children
        $directChildren = $room->getChild();
        if (!empty($directChildren)) {
            $result['children'] = [];
            foreach ($directChildren as $child) {
                $result['children'][] = self::prepareResult($child);
            }
        }
        return $result;
    }

    /**
     * Add eqLogics data in rooms
     *
     * @param int $roomId Room ID
     *
     * @return array List of rooms with eqLogics data
     *
     * @throws Exception
     */
    private static function addEqLogicsInformations(int $roomId)
    {
        $result = [];
        $eqLogics = EqLogicRest::getVisibleByRoom($roomId);
        if (!empty($eqLogics)) {
            // Get commands attached to the room
            foreach ($eqLogics as $eqLogic) {
                $cmds = CmdRest::getByEqLogic($eqLogic['id']);
                if (!empty($cmds)) {
                    $eqLogic['cmds'] = $cmds;
                    $result[] = $eqLogic;
                }
            }
        }
        return $result;
    }
}