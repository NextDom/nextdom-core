<?php

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

    public static function get(int $roomId) {
        $room = JeeObjectManager::byId($roomId);
        return self::prepareResult($room);
    }

    public static function getRoots() {
        $rootRooms = JeeObjectManager::getRootObjects(true, false);
        $result = [];
        foreach ($rootRooms as $room) {
            $result[] = self::prepareResult($room);
        }
        return $result;
    }
}