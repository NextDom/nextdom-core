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
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;

class CmdConfigureHistory extends BaseAbstractModal
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render command configure history modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render): string
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

        $pageContent = [];
        $pageContent['dataCount'] = $dataCount;

        return $render->get('/modals/cmd.configureHistory.html.twig', $pageContent);
    }

}
     
