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

namespace NextDom\Controller\Diagnostic;

use NextDom\Controller\BaseController;
use NextDom\Enums\CmdType;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;

/**
 * Class EqAnalyzeController
 * @package NextDom\Controller\Diagnostic
 */
class EqAnalyzeController extends BaseController
{
    /**
     * Render eqLogic analyze page
     *
     * @param array $pageData Page data
     *
     * @return string Content of eqLogic analyze page
     *
     * @throws \ReflectionException
     */
    public static function get(&$pageData): string
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $pageData['eqAnalyzeEqLogicList'] = [];

        $eqLogics = EqLogicManager::all();
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getStatus('battery', -2) != -2) {
                $pageData['eqAnalyzeEqLogicList'][] = $eqLogic;
            }
        }
        usort($pageData['eqAnalyzeEqLogicList'], function ($a, $b) {
            $result = 0;
            if ($a->getStatus('battery') < $b->getStatus('battery')) {
                $result = -1;
            } elseif ($a->getStatus('battery') > $b->getStatus('battery')) {
                $result = 1;
            }
            return $result;
        });

        $cmdDataArray = [];
        foreach ($eqLogics as $eqLogic) {
            $cmdData = [];
            $cmdData['eqLogic'] = $eqLogic;
            $cmdData['infoCmds'] = [];
            $cmdData['actionCmds'] = [];

            $eqlogicGetCmdInfo = $eqLogic->getCmd(CmdType::INFO);
            foreach ($eqlogicGetCmdInfo as $cmd) {
                if (count($cmd->getConfiguration('actionCheckCmd', [])) > 0) {
                    $data = [];
                    $data['cmd'] = $cmd;
                    $data['actions'] = [];
                    foreach ($cmd->getConfiguration('actionCheckCmd') as $actionCmd) {
                        $data['actions'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                    $cmdData['infoCmds'][] = $data;
                }
            }

            $eqLogicGetCmdAction = $eqLogic->getCmd(CmdType::ACTION);
            foreach ($eqLogicGetCmdAction as $cmd) {
                $actionCmdData = [];
                $actionCmdData['cmd'] = $cmd;

                $cmdGetConfigurationNextdomPreExecCmd = [];
                if (count($cmd->getConfiguration('nextdomPreExecCmd', [])) > 0) {
                    $actionCmdData['preExecCmds'] = [];

                    $cmdGetConfigurationNextdomPreExecCmd = $cmd->getConfiguration('nextdomPreExecCmd');
                    foreach ($cmdGetConfigurationNextdomPreExecCmd as $actionCmd) {
                        $actionCmdData['preExecCmds'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                }
                if (count($cmd->getConfiguration('nextdomPostExecCmd', [])) > 0) {
                    $actionCmdData['postExecCmds'] = [];
                    foreach ($cmdGetConfigurationNextdomPreExecCmd as $actionCmd) {
                        $actionCmdData['postExecCmds'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                }
                $cmdData['actionCmds'][] = $actionCmdData;
            }
            $cmdDataArray[] = $cmdData;
        }
        $pageData['eqAnalyzeCmdData'] = $cmdDataArray;
//@TODO: Imbriquer les boucles quand le fonctionnement sera sûr
        $pageData['eqAnalyzeAlerts'] = [];

        $eqLogicManagerAll = EqLogicManager::all();
        foreach ($eqLogicManagerAll as $eqLogic) {
            $hasSomeAlerts = 0;

            $listCmds = [];
            $eqLogicGetCmdInfo = $eqLogic->getCmd(CmdType::INFO);
            foreach ($eqLogicGetCmdInfo as $cmd) {
                foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {

                    if ($value['check']) {
                        if ($cmd->getAlert($level . 'if', '') != '') {
                            $hasSomeAlerts += 1;
                            if (!in_array($cmd, $listCmds)) {
                                $listCmds[] = $cmd;
                            }
                        }
                    }
                }
            }

            if ($eqLogic->getConfiguration('battery_warning_threshold', '') != '') {
                $hasSomeAlerts += 1;
            }

            if ($eqLogic->getConfiguration('battery_danger_threshold', '') != '') {
                $hasSomeAlerts += 1;
            }

            if ($eqLogic->getTimeout('')) {
                $hasSomeAlerts += 1;
            }

            if ($hasSomeAlerts != 0) {
                $alertData = [];
                $alertData['eqLogic'] = $eqLogic;

                foreach ($listCmds as $cmdalert) {
                    foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
                        if ($value['check']) {
                            if ($cmdalert->getAlert($level . 'if', '') != '') {
                                if ($cmdalert->getAlert($level . 'during', '') == '') {
                                    $during = ' effet immédiat';
                                } else {
                                    $during = ' pendant plus de ' . $cmdalert->getAlert($level . 'during', '') . ' minute(s)';
                                }
                                $alertData['msg'] = ucfirst($level) . ' si ' . NextDomHelper::toHumanReadable(str_replace('#value#', '<b>' . $cmdalert->getName() . '</b>', $cmdalert->getAlert($level . 'if', ''))) . $during . '</br>';
                            }
                        }
                    }
                }
                $pageData['eqAnalyzeAlerts'][] = $alertData;
            }
        }

        $pageData['eqAnalyzeNextDomDeadCmd'] = NextDomHelper::getDeadCmd();
        $pageData['eqAnalyzeCmdDeadCmd'] = CmdManager::deadCmd();
        $pageData['eqAnalyzeJeeObjectDeadCmd'] = JeeObjectManager::deadCmd();
        $pageData['eqAnalyzeScenarioDeadCmd'] = ScenarioManager::consystencyCheck(true);
        $pageData['eqAnalyzeInteractDefDeadCmd'] = InteractDefManager::deadCmd();
        $pageData['eqAnalyzePluginDeadCmd'] = [];

        $pluginManagerListPluginTrue = PluginManager::listPlugin(true);
        foreach ($pluginManagerListPluginTrue as $plugin) {
            $pluginId = $plugin->getId();
            if (method_exists($pluginId, 'deadCmd')) {
                $pageData['eqAnalyzePluginDeadCmd'][] = $pluginId::deadCmd();
            }
        }
        $pageData['JS_END_POOL'][] = '/public/js/desktop/diagnostic/eqAnalyse.js';

        return Render::getInstance()->get('/desktop/diagnostic/eqAnalyze.html.twig', $pageData);
    }


}
