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
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CmdRest
 *
 * @package NextDom\Rest
 */
class CmdRest
{
    /**
     * Get all commands by eqLogic
     *
     * @param int $eqLogicId EqLogic id linked to commands
     *
     * @return array Array of linked commands
     *
     * @throws \Exception
     */
    public static function getByEqLogic(int $eqLogicId)
    {
        $cmds = CmdManager::byEqLogicId($eqLogicId);
        return self::prepareResults($cmds);
    }

    /**
     * Get all commands visible by eqLogic
     *
     * @param int $eqLogicId EqLogic id linked to commands
     *
     * @return array Array of linked commands
     *
     * @throws \Exception
     */
    public static function getVisibleByEqLogic(int $eqLogicId)
    {
        $cmds = CmdManager::byEqLogicId($eqLogicId, null, true);
        return self::prepareResults($cmds);
    }

    /**
     * Prepare result for response
     *
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
            $cmdRow = array_merge($cmdRow, self::getSpecialData($cmd));
            if (empty($cmdRow['value'])) {
                $cmdRow['value'] = 0;
            }
            $result[] = $cmdRow;
        }
        return $result;
    }

    /**
     * Get specials data depends of type
     * @param Cmd $cmd Command with data
     *
     * @return array Specials data
     */
    private static function getSpecialData($cmd)
    {
        $result = [];
        if ($cmd->getType() === 'info') {
            try {
                $result['state'] = $cmd->execCmd();
                if ($cmd->getSubType() === 'numeric' && empty($result['state'])) {
                    $result['state'] = 0;
                }
            } catch (\Exception $e) {

            }
        }
        elseif ($cmd->getType() === 'action') {
            $configuration = $cmd->getConfiguration();
            if (isset($configuration['minValue'])) {
                $result['minValue'] = $configuration['minValue'];
            }
            if (isset($configuration['maxValue'])) {
                $result['maxValue'] = $configuration['maxValue'];
            }
        }
        return $result;
    }

    /**
     * Execute command
     *
     * @param Request $request Query data
     * @param int $cmdId Command id to execute
     *
     * @return bool True if success
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function exec(Request $request, int $cmdId)
    {
        $options = null;
        // Read post data for options
        $postDataKeys = $request->request->keys();
        if (count($postDataKeys) > 0) {
            $options = [];
            foreach ($postDataKeys as $postDataKey) {
                $options[$postDataKey] = $request->request->get($postDataKey);
            }
        }
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            return false;
        }
        $cmd->execCmd($options);
        return true;
    }

}