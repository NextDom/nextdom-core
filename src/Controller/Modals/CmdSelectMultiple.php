<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modals;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;

/**
 * Class CmdSelectMultiple
 * @package NextDom\Controller\Modals
 */
class CmdSelectMultiple extends BaseAbstractModal
{
    /**
     * Render command select multiple modal (scenario)
     *
     * @return string
     * @throws CoreException
     */
    public static function get(): string
    {
        $cmdId = Utils::initInt('cmd_id', -1);
        $selectedCmd = CmdManager::byId($cmdId);
        if (is_object($selectedCmd)) {
            $cmdType = $selectedCmd->getType();
            $cmdSubType = $selectedCmd->getSubType();
        }
        else {
            $cmdType = Utils::initStr('type');
            $cmdSubType = Utils::initStr('subtype');
            $cmdName = Utils::initStr('name');
        }
        $cmdList = CmdManager::byTypeSubType($cmdType, $cmdSubType);
        $pageData = [];
        $pageData['cmds'] = [];
        foreach ($cmdList as $cmd) {
            $data = [];
            $data['cmdId'] = $cmd->getId();
            $data['cmdName'] = $cmd->getName();
            $data['selected'] = ($data['cmdId'] == $cmdId) || ($cmdId === -1 && ($cmd->getTemplate()['dashboard'] === 'custom::' . $cmdName || $cmd->getTemplate()['mobile'] === 'custom::' . $cmdName));
            $data['object'] = '';
            $data['eqLogic'] = '';
            $linkedEqLogic = $cmd->getEqLogic();
            if (is_object($linkedEqLogic)) {
                $data['eqLogic'] = $linkedEqLogic->getName();
                $linkedObject = $linkedEqLogic->getObject();
                if (is_object($linkedEqLogic)) {
                    $data['object'] = $linkedObject->getName();
                }
            }
            $pageData['cmds'][] = $data;
        }
        return Render::getInstance()->get('/modals/cmd.selectMultiple.html.twig', $pageData);
    }
}
