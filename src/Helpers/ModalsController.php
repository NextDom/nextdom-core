<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

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
 */

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;
use NextDom\Managers\CmdManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;

class ModalsController
{
    const routesList = [
        'about' => 'aboutModal',
        'cmd.configure' => 'cmdConfigureModal',
        'dataStore.management' => 'dataStoreManagementModal',
        'expression.test' => 'expressionTestModal',
        'graph.link' => 'graphLinkModal',
        'log.display' => 'logDisplayModal',
        'plan.configure' => 'planConfigureModal',
        'planHeader.configure' => 'planHeaderConfigureModal',
        'scenario.export' => 'scenarioExportModal',
        'scenario.log.execution' => 'scenarioLogExecutionModal',
        'scenario.summary' => 'scenarioSummaryModal',
        'welcome' => 'welcomeModal'
    ];

    /**
     * Get static method of page by his code
     *
     * @param string $page Page code
     *
     * @return mixed|null Static method or null
     */
    public static function getRoute(string $page)
    {
        $route = null;
        if (array_key_exists($page, self::routesList)) {
            $route = self::routesList[$page];
        }
        return $route;
    }

    /**
     * Render about
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function aboutModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/about.html.twig');
    }

    /**
     * Render command configuration modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cmdConfigureModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $cmdId = Utils::init('cmd_id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new \Exception('Commande non trouvé : ' . $cmdId);
        }
        $cmdInfo = \nextdom::toHumanReadable(\utils::o2a($cmd));
        foreach (array('dashboard', 'mobile', 'dview', 'mview', 'dplan') as $value) {
            if (!isset($cmdInfo['html'][$value]) || $cmdInfo['html'][$value] == '') {
                $cmdInfo['html'][$value] = $cmd->getWidgetTemplateCode($value);
            }
        }
        $pageContent['cmdType'] = $cmd->getType();
        $pageContent['cmdSubType'] = $cmd->getSubtype();
        $pageContent['cmdWidgetPossibilityCustom'] = $cmd->widgetPossibility('custom');
        $pageContent['cmdWidgetPossibilityCustomHtmlCode'] = $cmd->widgetPossibility('custom::htmlCode');
        if ($pageContent['cmdType'] == 'action' && $pageContent['cmdSubType'] == 'select') {
            $pageContent['cmdListValues'] = [];
            $elements = explode(';', $cmd->getConfiguration('listValue', ''));
            foreach ($elements as $element) {
                $pageContent['cmdListValues'][] = explode('|', $element);
            }
        }
        if ($pageContent['cmdType'] == 'info') {
            $pageContent['cmdCacheValue'] = $cmd->getCache('value');
            $pageContent['cmdCollectDate'] = $cmd->getCache('collectDate');
            $pageContent['cmdValueDate'] = $cmd->getCache('valueDate');
        }
        $pageContent['cmdDirectUrlAccess'] = $cmd->getDirectUrlAccess();
        $pageContent['cmdUsedBy'] = $cmd->getUsedBy();
        $pageContent['cmdGenericTypes'] = \nextdom::getConfiguration('cmd::generic_type');

        $pageContent['cmdGenericTypeInformations'] = array();
        foreach (\nextdom::getConfiguration('cmd::generic_type') as $key => $info) {
            if ($cmd->getType() == 'info' && $info['type'] == 'Action') {
                continue;
            } elseif ($cmd->getType() == 'action' && $info['type'] == 'Info') {
                continue;
            } elseif (isset($info['ignore']) && $info['ignore']) {
                continue;
            }
            $info['key'] = $key;
            if (!isset($pageContent['cmdGenericTypeInformations'][$info['family']])) {
                $pageContent['cmdGenericTypeInformations'][$info['family']][0] = $info;
            } else {
                array_push($pageContent['cmdGenericTypeInformations'][$info['family']], $info);
            }
        }
        ksort($pageContent['cmdGenericTypeInformations']);
        foreach (array_keys($pageContent['cmdGenericTypeInformations']) as $key) {
            usort($pageContent['cmdGenericTypeInformations'][$key], function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        $pageContent['cmdTypeIsHistorized'] = $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['visible'];
        $pageContent['cmdIsHistorized'] = $cmd->getIsHistorized();

        $pageContent['cmdWidgetCanCustomHtml'] = $cmd->widgetPossibility('custom::htmlCode');
        $pageContent['cmdWidgetCanCustom'] = $cmd->widgetPossibility('custom');
        $pageContent['cmdWidgetCanCustomWidget'] = $cmd->widgetPossibility('custom::widget');
        $pageContent['cmdWidgetCanCustomWidgetDashboard'] = $cmd->widgetPossibility('custom::widget::dashboard');
        $pageContent['cmdWidgetCanCustomWidgetMobile'] = $cmd->widgetPossibility('custom::widget::mobile');
        $pageContent['cmdWidgetCanCustomVisibility'] = $cmd->widgetPossibility('custom::visibility');
        $pageContent['cmdWidgetCanCustomDisplayName'] = $cmd->widgetPossibility('custom::displayName');
        $pageContent['cmdWidgetCanCustomDisplayIconAndName'] = $cmd->widgetPossibility('custom::displayIconAndName');
        $pageContent['cmdWidgetCanCustomDisplayStats'] = $cmd->widgetPossibility('custom::displayStats');
        $pageContent['cmdWidgetCanCustomOptionalParameters'] = $cmd->widgetPossibility('custom::optionalParameters');
        $pageContent['configDisplayStatsWidget'] = \config::byKey('displayStatsWidget');
        $pageContent['cmdDisplayParameters'] = $cmd->getDisplay('parameters');

        $cmdWidgetDashboard = CmdManager::availableWidget('dashboard');
        $cmdWidgetMobile = CmdManager::availableWidget('mobile');
        if (is_array($cmdWidgetDashboard[$cmd->getType()]) && is_array($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) && count($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) > 0) {
            $pageContent['cmdWidgetDashboard'] = $cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()];
        }
        if (is_array($cmdWidgetMobile[$cmd->getType()]) && is_array($cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()]) && count($cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()]) > 0) {
            $pageContent['cmdWidgetMobile'] = $cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()];
        }

        $pageContent['alertsConfig'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageContent['eqLogicDisplayType'] = \nextdom::getConfiguration('eqLogic:displayType');

        Utils::sendVarsToJS([
            'cmdInfo' => $cmdInfo,
            'cmdInfoSearchString' => urlencode(str_replace('#', '', $cmd->getHumanName()))
        ]);

        $render->show('/modals/cmd.configure.html.twig', $pageContent);
    }

    /**
     * Render data store management modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function dataStoreManagementModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        Utils::sendVarsToJS(['dataStore_type' => Utils::init('type'),
                             'dataStore_link_id', Utils::init('link_id', -1)]);

        $render->show('/modals/dataStore.management.html.twig');
    }

    /**
     * Render expression test modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function expressionTestModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $render->show('/modals/expression.test.html.twig');
    }

    /**
     * Render graph link modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function graphLinkModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $configData = \config::byKeys(
            ['graphlink::prerender', 'graphlink::render'],
            'core',
            [
                'graphlink::prerender' => 30,
                'graphlink::render' => 3000
            ]);
        Utils::sendVarsToJS([
            'prerenderGraph' => $configData['graphlink::prerender'],
            'renderGraph' => $configData['graphlink::render'],
            'filterTypeGraph' => Utils::init('filter_type'),
            'filterIdGraph' => Utils::init('filter_id')
        ]);

        $render->show('/modals/graph.link.html.twig');
    }

    /**
     * Render log display modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function logDisplayModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        Utils::sendVarsToJS([
            'realtime_name' => Utils::init('log', 'event'),
            'log_default_search' => Utils::init('search', '')
        ]);
        $render->show('/modals/log.display.html.twig');
    }

    /**
     * Render plan configure modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function planConfigureModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $pageContent['planObject'] = \plan::byId(Utils::init('id'));
        if (!is_object($pageContent['planObject'])) {
            throw new \Exception('Impossible de trouver le design');
        }
        $pageContent['planLink'] = $pageContent['planObject']->getLink();
        $pageContent['jeeObjects'] = JeeObjectManager::all();
        $pageContent['views'] = \view::all();
        $pageContent['plans'] = \planHeader::all();
        Utils::sendVarToJS('id', $pageContent['planObject']->getId());

        $render->show('/modals/plan.configure.html.twig', $pageContent);
    }

    /**
     * Render plan header configure modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function planHeaderConfigureModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $planHeader = \planHeader::byId(Utils::init('planHeader_id'));
        if (!is_object($planHeader)) {
            throw new \Exception('Impossible de trouver le plan');
        }
        Utils::sendVarsToJS(['id' => $planHeader->getId(),
                             'planHeader' => \utils::o2a($planHeader)]);

        $render->show('/modals/planHeader.configure.html.twig');
    }

    /**
     * Render scenario export modal
     *
     * @param Render $render Render engine
     *
     * @return string Scenario export modal
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function scenarioExportModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();
        $scenario = ScenarioManager::byId(init('scenario_id'));

        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario introuvable'));
        }

        $pageContent = [];
        $pageContent['scenarioExportData'] = $scenario->export();

        $render->show('/modals/scenario.export.html.twig', $pageContent);
    }

    /**
     * Render scenario log execution modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function scenarioLogExecutionModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $scenarioId = Utils::init('scenario_id');
        $scenario = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            throw new CoreException(__('Aucun scénario ne correspondant à : ') . $scenarioId);
        }
        Utils::sendVarToJs('scenarioLog_scenario_id', $scenarioId);

        $pageContent = [];
        $pageContent['scenarioId'] = $scenarioId;
        $pageContent['scenarioHumanName'] = $scenario->getHumanName();
        $render->show('/modals/scenario.log.execution.html.twig', $pageContent);
    }

    /**
     * Render scenario summary modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function scenarioSummaryModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/scenario.summary.html.twig');
    }

    /**
     * Render welcome modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function welcomeModal(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['productName'] = \config::byKey('product_name');
        $render->show('/modals/welcome.html.twig', $pageContent);
    }
}
