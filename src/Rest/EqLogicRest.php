<?php

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
     * @param EqLogic[] $eqLogics
     */
    private static function prepareResults($eqLogics)
    {
        $result = [];
        foreach ($eqLogics as $eqLogic) {
            $eqLogicRow = [];
            $eqLogicRow['id'] = $eqLogic->getId();
            $eqLogicRow['type'] = $eqLogic->getEqType_name();
            $eqLogicRow['name'] = $eqLogic->getName();
            $eqLogicRow['objectId'] = $eqLogic->getObject_id();
            $eqLogicRow['enable'] = $eqLogic->getIsEnable() == 1 ? true : false;
            $eqLogicRow['visible'] = $eqLogic->getIsVisible() == 1 ? true : false;
            $result[] = $eqLogicRow;
        }
        return $result;
    }

    /**
     * Get all eqLogic
     *
     * @throws \Exception
     */
    public static function getAll()
    {
        $eqLogics = EqLogicManager::all();
        return self::prepareResults($eqLogics);
    }

    public static function getByRoom(int $roomId)
    {
        $eqLogics = EqLogicManager::byObjectId($roomId, true, true);
        return self::prepareResults($eqLogics);
    }
}