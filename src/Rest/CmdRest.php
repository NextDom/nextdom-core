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

use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
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
     * Get all commands linked to an eqLogic
     *
     * @param int $eqLogicId EqLogic id
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
     * Prepare result for response\n
     * Associative array with following keys :
     *  - id
     *  - name
     *  - type
     *  - subType
     *  - icon
     *  - genericType
     *  - template
     *  - cmdValue
     *  - value
     *  - visible
     *  - unite
     *
     * @param Cmd[] $cmds List of commands
     *
     * @return array Associative array with all commands data prepared
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
            $cmdRow['logicalId'] = $cmd->getLogicalId();
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
     *
     * @param Cmd $cmd Command with data
     *
     * @return array Specials data
     */
    private static function getSpecialData($cmd)
    {
        $result = [];
        if ($cmd->isType(CmdType::INFO)) {
            try {
                $result['state'] = $cmd->execCmd();
                if ($cmd->isSubType(CmdSubType::NUMERIC) && empty($result['state'])) {
                    $result['state'] = 0;
                }
            } catch (\Exception $e) {

            }
        } elseif ($cmd->isType(CmdType::ACTION)) {
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
     * Get all commands visible linked to an eqLogic
     *
     * @param int $eqLogicId EqLogic id
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
     * Execute command\n
     * Options must be stored in $_POST of the request.
     *
     * @param Request $request Query data
     * @param int $cmdId Command id to execute
     *
     * @return bool True if success
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
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