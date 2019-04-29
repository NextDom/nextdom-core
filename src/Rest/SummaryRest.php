<?php

namespace NextDom\Rest;


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

    private static function addEqLogicsInformations(int $roomId)
    {
        $result = [];
        $eqLogics = EqLogicRest::getByRoom($roomId);
        if (!empty($eqLogics)) {
            // Get commands attached to the room
            foreach ($eqLogics as $eqLogic) {
                $cmds = CmdRest::byEqLogic($eqLogic['id']);
                if (!empty($cmds)) {
                    $eqLogic['cmds'] = $cmds;
                    $result[] = $eqLogic;
                }
            }
        }
        return $result;
    }

    /**
     * Get tree of rooms from room defined by the user or root room
     *
     * @throws \Exception
     */
    public static function getDefaultRoomTree()
    {
        $authenticator = Authenticator::getInstance();
        $user = $authenticator->getConnectedUser();
        $defaultRoom = JeeObjectManager::getDefaultUserRoom($user);
        return self::getRoomTree($defaultRoom->getId());
    }

    /**
     * Get tree of rooms from specific room
     *
     * @param int $rootRoomId Root rooms
     *
     * @return JeeObjectManager[] Tree of rooms
     * @throws \Exception
     */
    public static function getRoomTree(int $roomId)
    {
        $rootRoom = JeeObjectManager::byId($roomId);
        $rootRoom->getChilds();
        $result = self::prepareResult($rootRoom);
        return $result;
    }
}