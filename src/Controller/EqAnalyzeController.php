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

namespace NextDom\Controller;
 
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class EqAnalyzeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render eqLogic analyze page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of eqLogic analyze page
     *
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        global $NEXTDOM_INTERNAL_CONFIG;

        $pageContent['eqAnalyzeEqLogicList'] = [];

        $eqLogicMangerAll = EqLogicManager::all();
        foreach ($eqLogicMangerAll as $eqLogic) {
            $battery_type = str_replace(array('(', ')'), ['', ''], $eqLogic->getConfiguration('battery_type', ''));
            if ($eqLogic->getStatus('battery', -2) != -2) {
                $pageContent['eqAnalyzeEqLogicList'][] = $eqLogic;
            }
        }
        usort($pageContent['eqAnalyzeEqLogicList'], function ($a, $b) {
            $result = 0;
            if ($a->getStatus('battery') < $b->getStatus('battery')) {
                $result = -1;
            } elseif ($a->getStatus('battery') > $b->getStatus('battery')) {
                $result = 1;
            }
            return $result;
        });

        $cmdDataArray = [];
        foreach ($eqLogicMangerAll as $eqLogic) {
            $cmdData = [];
            $cmdData['eqLogic'] = $eqLogic;
            $cmdData['infoCmds'] = [];
            $cmdData['actionCmds'] = [];

            $eqlogicGetCmdInfo = $eqLogic->getCmd('info');
            foreach ($eqlogicGetCmdInfo as $cmd) {
                if (count($cmd->getConfiguration('actionCheckCmd', array())) > 0) {
                    $data = [];
                    $data['cmd'] = $cmd;
                    $data['actions'] = [];
                    foreach ($cmd->getConfiguration('actionCheckCmd') as $actionCmd) {
                        $data['actions'][] = ScenarioExpressionManager::humanAction($actionCmd);
                    }
                    $cmdData['infoCmds'][] = $data;
                }
            }

            $eqLogicGetCmdAction = $eqLogic->getCmd('action');
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
        $pageContent['eqAnalyzeCmdData'] = $cmdDataArray;
//TODO: Imbriquer les boucles quand le fonctionnement sera sûr
        $pageContent['eqAnalyzeAlerts'] = [];

        $eqLogicManagerAll = EqLogicManager::all();
        foreach ($eqLogicManagerAll as $eqLogic) {
            $hasSomeAlerts = 0;

            $listCmds = [];
            $eqLogicGetCmdInfo = $eqLogic->getCmd('info');
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
                                $during = '';
                                if ($cmdalert->getAlert($level . 'during', '') == '') {
                                    $during = ' effet immédiat';
                                } else {
                                    $during = ' pendant plus de ' . $cmdalert->getAlert($level . 'during', '') . ' minute(s)';
                                }
                                $alertData['msg'] = ucfirst($level) . ' si ' . \nextdom::toHumanReadable(str_replace('#value#', '<b>' . $cmdalert->getName() . '</b>', $cmdalert->getAlert($level . 'if', ''))) . $during . '</br>';
                            }
                        }
                    }
                }
                $pageContent['eqAnalyzeAlerts'][] = $alertData;
            }
        }

        $pageContent['eqAnalyzeNextDomDeadCmd'] = \nextdom::deadCmd();
        $pageContent['eqAnalyzeCmdDeadCmd'] = CmdManager::deadCmd();
        $pageContent['eqAnalyzeJeeObjectDeadCmd'] = JeeObjectManager::deadCmd();
        $pageContent['eqAnalyzeScenarioDeadCmd'] = ScenarioManager::consystencyCheck(true);
        $pageContent['eqAnalyzeInteractDefDeadCmd'] = \interactDef::deadCmd();
        $pageContent['eqAnalyzePluginDeadCmd'] = [];

        $pluginManagerListPluginTrue = PluginManager::listPlugin(true);
        foreach ($pluginManagerListPluginTrue as $plugin) {
            $pluginId = $plugin->getId();
            if (method_exists($pluginId, 'deadCmd')) {
                $pageContent['eqAnalyzePluginDeadCmd'][] = $pluginId::deadCmd();
            }
        }
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/diagnostic/eqAnalyse.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/diagnostic/eqAnalyze.html.twig', $pageContent);
    }

    
}
