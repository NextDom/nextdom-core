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

use NextDom\Managers\CmdManager;
use NextDom\Model\Entity\Cmd;

/**
 * Class CmdRest
 *
 * @package NextDom\Rest
 */
class CmdRest
{
    /**
     * @param Cmd[] $cmds Liste of commands
     *
     * @return array API necessary data
     *
     * @throws \Exception
     */
    private static function prepareResults($cmds)
    {
        $result = [];
        foreach ($cmds as $cmd) {
            $cmdRow = [];
            $cmdRow['id'] = $cmd->getId();
            $cmdRow['name'] = $cmd->getName();
            $cmdRow['type'] = $cmd->getType();
            $cmdRow['subType'] = $cmd->getSubType();
            $cmdRow['icon'] = $cmd->getDisplay('icon');
            $cmdRow['genericType'] = $cmd->getGeneric_type();
            $cmdRow['template'] = $cmd->getTemplate('dashboard');
            $cmdRow['cmdValue'] = $cmd->getCmdValue();
            $cmdRow['value'] = $cmd->getValue();
            $cmdRow['visible'] = $cmd->getIsVisible();
            $cmdRow['unite'] = $cmd->getUnite();
            if ($cmdRow['type'] === 'info') {
                try {
                    $cmdRow['state'] = $cmd->execCmd();
                }
                catch (\Exception $e) {

                }
            }
            $result[] = $cmdRow;
        }
        return $result;
    }

    /**
     * Get all commands by eqLogic
     *
     * @param int $eqLogicId EqLogic id linked to commands
     *
     * @return array Array of linked commands
     *
     * @throws \Exception
     */
    public static function byEqLogic(int $eqLogicId)
    {
        $cmds = CmdManager::byEqLogicId($eqLogicId);
        return self::prepareResults($cmds);
    }

    /**
     * Execute command
     *
     * @param int $cmdId Command id to execute
     *
     * @return bool True if success
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function exec(int $cmdId) {
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            return false;
        }
        $cmd->execCmd();
        return true;
    }

}