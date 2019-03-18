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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;

class CmdConfigureHistory extends BaseAbstractModal
{
    /**
     * Render command configure history modal (scenario)
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $dataCount = ['history' => 0, 'timeline' => 0];
        $listCmd = array();

        foreach (CmdManager::all() as $cmd) {
            $info_cmd = Utils::o2a($cmd);
            $info_cmd['humanName'] = $cmd->getHumanName(true);
            $eqLogic = $cmd->getEqLogic();
            $info_cmd['plugins'] = $eqLogic->getEqType_name();
            $listCmd[] = $info_cmd;
            if ($cmd->getIsHistorized() == 1) {
                $dataCount['history']++;
            }
            if ($cmd->getConfiguration('timeline::enable') == 1) {
                $dataCount['timeline']++;
            }
        }
        Utils::sendVarToJs('cmds_history_configure', $listCmd);

        $pageData = [];
        $pageData['dataCount'] = $dataCount;

        return Render::getInstance()->get('/modals/cmd.configureHistory.html.twig', $pageData);
    }

}
     
