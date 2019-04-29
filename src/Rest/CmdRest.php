<?php

namespace NextDom\Rest;


use NextDom\Managers\CmdManager;

/**
 * Class CmdRest
 *
 * @package NextDom\Rest
 */
class CmdRest
{
    /**
     * @param Cmd[] $eqLogics
     */
    private static function prepareResults($cmds)
    {
        $result = [];
        foreach ($cmds as $cmd) {
            $cmdRow = [];
            $cmdRow['id'] = $cmd->getId();
            $cmdRow['name'] = $cmd->getName();
            $result[] = $cmdRow;
        }
        return $result;
    }

    /**
     * Get all commands by eqLogic
     *
     * @throws \Exception
     */
    public static function byEqLogic(int $eqLogicId)
    {
        $cmds = CmdManager::byEqLogicId($eqLogicId);
        return self::prepareResults($cmds);
    }

}