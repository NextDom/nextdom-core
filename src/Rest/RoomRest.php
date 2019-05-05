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
     * @param JeeObject $room
     * @return array
     * @throws \Exception
     */
    private static function prepareResult(JeeObject $room)
    {
        $result = [];
        $result['id'] = $room->getId();
        $result['name'] = $room->getName();
        $result['icon'] = $room->getDisplay('icon');
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
     * @param int $rootRoomId Root rooms
     *
     * @return JeeObjectManager[] Tree of rooms
     * @throws \Exception
     */
    public static function getTree(int $roomId)
    {
        $rootRoom = JeeObjectManager::byId($roomId);
        $rootRoom->getChilds();
        $result = self::prepareResult($rootRoom);
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
    public static function get(int $roomId) {
        $room = JeeObjectManager::byId($roomId);
        return self::prepareResult($room);
    }

    /**
     * Get roots rooms
     *
     * @return array Data of root rooms
     *
     * @throws \Exception
     */
    public static function getRoots() {
        $rootRooms = JeeObjectManager::getRootObjects(true, false);
        $result = [];
        foreach ($rootRooms as $room) {
            $result[] = self::prepareResult($room);
        }
        return $result;
    }
}