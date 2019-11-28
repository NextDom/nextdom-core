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

use NextDom\Managers\JeeObjectManager;
use NextDom\Model\Entity\JeeObject;

/**
 * Class RoomRest
 *
 * @package NextDom\Rest
 */
class RoomRest
{
    /**
     * Get tree of rooms from room defined by the user or root room
     *
     * @throws \Exception
     */
    public static function getDefaultTree()
    {
        $authenticator = Authenticator::getInstance();
        $user = $authenticator->getConnectedUser();
        $defaultRoom = JeeObjectManager::getDefaultUserRoom($user);
        return self::getTree($defaultRoom->getId());
    }

    /**
     * Get tree of rooms from specific room
     *
     * @param int $roomId Root rooms
     *
     * @return JeeObjectManager[] Tree of rooms
     * @throws \Exception
     */
    public static function getTree(int $roomId)
    {
        $rootRoom = JeeObjectManager::byId($roomId);
        $result = self::prepareResult($rootRoom);
        return $result;
    }

    /**
     * Prepare result for response\n
     * Associative array with following keys :
     *  - id
     *  - name
     *  - icon
     *  - father (if exists)
     *    - id
     *    - name
     *    - icon
     *  - children (if exists)
     *
     * @param JeeObject $room
     * @param JeeObject|null $father
     *
     * @param bool $addChildren
     * @return array
     *
     * @throws \Exception
     */
    private static function prepareResult(JeeObject $room, JeeObject $father = null, $addChildren = true)
    {
        $result = [];
        $result['id'] = $room->getId();
        $result['name'] = $room->getName();
        $result['icon'] = $room->getDisplay('icon');
        // Avoid sql query
        if ($father === null) {
            $father = $room->getFather();
        }
        if (is_object($father)) {
            $result['father'] = [];
            $result['father']['id'] = $father->getId();
            $result['father']['name'] = $father->getName();
            $result['father']['icon'] = $father->getDisplay('icon');
        }
        if ($addChildren) {
            // Get all children
            $directChildren = $room->getChild();
            if (!empty($directChildren)) {
                $result['children'] = [];
                foreach ($directChildren as $child) {
                    $result['children'][] = self::prepareResult($child, $room);
                }
            }
        }
        return $result;
    }

    /**
     * Get room data
     *
     * @param int $roomId Target room id
     *
     * @return array Room data
     *
     * @throws \Exception
     */
    public static function get(int $roomId)
    {
        $room = JeeObjectManager::byId($roomId);
        return self::prepareResult($room, null, false);
    }

    /**
     * Get roots rooms
     *
     * @return array Data of root rooms
     *
     * @throws \Exception
     */
    public static function getRoots()
    {
        $rootRooms = JeeObjectManager::getRootObjects(true, false);
        $result = [];
        $result['id'] = null;
        $result['name'] = null;
        $result['icon'] = null;
        $result['children'] = [];
        foreach ($rootRooms as $room) {
            $result['children'][] = self::prepareResult($room);
        }
        return $result;
    }

    /**
     * Get HTML summary from list of rooms passed in argument separated by ;
     * @param string $roomsList List of rooms Id separated by ;
     * @return string[]
     * @throws \Exception
     */
    public static function getRoomsSummary(string $roomsList)
    {
        $result = [];
        $roomsList = explode(';', $roomsList);
        if (is_array($roomsList) && count($roomsList) > 1) {
            foreach ($roomsList as $roomId) {
                $result[$roomId] = self::getRoomSummary(intval($roomId));
            }
        }
        return $result;
    }

    /**
     * Get HTML room summary
     * @param int $roomId Target room id
     * @return bool|string HTML summary or false if no summary
     * @throws \Exception
     */
    public static function getRoomSummary(int $roomId)
    {
        $room = JeeObjectManager::byId($roomId);
        if (is_object($room)) {
            return $room->getHtmlSummary();
        }
        return false;
    }
}