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
use NextDom\Managers\AjaxManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UpdateManager;
use Sabre\CalDAV\Plugin;

class ModalsController
{
    const routesList = [
        'about' => 'about',
        'action.insert' => 'actionInsert',
        'cmd.configure' => 'cmdConfigure',
        'cmd.configureHistory' => 'cmdConfigureHistory',
        'cmd.human.insert' => 'cmdHumanInsert',
        'cmd.selectMultiple' => 'cmdSelectMultiple',
        'cron.human.insert' => 'cronHumanInsert',
        'dataStore.human.insert' => 'dataStoreHumanInsert',
        'dataStore.management' => 'dataStoreManagement',
        'eqLogic.configure' => 'eqLogicConfigure',
        'eqLogic.human.insert' => 'eqLogicHumanInsert',
        'expression.test' => 'expressionTest',
        'graph.link' => 'graphLink',
        'history.calcul' => 'historyCalcul',
        'icon.selector' => 'iconSelector',
        'interact.query.display' => 'interactQueryDisplay',
        'interact.test' => 'interactTest',
        'log.display' => 'logDisplay',
        'nextdom.benchmark' => 'nextdomBenchmark',
        'object.configure' => 'objectConfigure',
        'object.display' => 'objectDisplay',
        'object.summary' => 'objectSummary',
        'plan.configure' => 'planConfigure',
        'planHeader.configure' => 'planHeaderConfigure',
        'plan3d.configure' => 'plan3dConfigure',
        'plugin.deamon' => 'pluginDaemon',
        'plugin.dependancy' => 'pluginDependency',
        'remove.history' => 'removeHistory',
        'report.bug' => 'reportBug',
        'scenario.export' => 'scenarioExport',
        'scenario.human.insert' => 'scenarioHumanInsert',
        'scenario.log.execution' => 'scenarioLogExecution',
        'scenario.summary' => 'scenarioSummary',
        'scenario.template' => 'scenarioTemplate',
        'update.add' => 'updateAdd',
        'update.display' => 'updateDisplay',
        'update.list' => 'updateList',
        'update.send' => 'updateSend',
        'user.rights' => 'userRights',
        'view.configure' => 'viewConfigure',
        'welcome' => 'welcome'
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
    public static function about(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/about.html.twig');
    }

    /**
     * Render action insert modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function actionInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['productName'] = ConfigManager::byKey('product_name');

        $render->show('/modals/action.insert.html.twig', $pageContent);
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
    public static function cmdConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $cmdId = Utils::init('cmd_id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException('Commande non trouvé : ' . $cmdId);
        }
        $cmdInfo = NextDomHelper::toHumanReadable(\utils::o2a($cmd));
        foreach (array('dashboard', 'mobile', 'dview', 'mview', 'dplan') as $value) {
            if (!isset($cmdInfo['html'][$value]) || $cmdInfo['html'][$value] == '') {
                $cmdInfo['html'][$value] = $cmd->getWidgetTemplateCode($value);
            }
        }
        $pageContent['cmdType'] = $cmd->getType();
        $pageContent['cmdSubType'] = $cmd->getSubtype();
        $pageContent['cmdWidgetPossibilityCustom'] = $cmd->widgetPossibility('custom');
        $pageContent['cmdWidgetPossibilityCustomHtmlCode'] = $cmd->widgetPossibility('custom::htmlCode');
        $pageContent['cmdShowMinMax'] = false;
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
            if ($cmd->getSubType() == 'numeric') {
                $pageContent['cmdShowMinMax'] = true;
            }
        }
        $pageContent['cmdDirectUrlAccess'] = $cmd->getDirectUrlAccess();
        $pageContent['cmdUsedBy'] = $cmd->getUsedBy();
        $pageContent['cmdGenericTypes'] = NextDomHelper::getConfiguration('cmd::generic_type');

        $pageContent['cmdGenericTypeInformations'] = array();
        foreach (NextDomHelper::getConfiguration('cmd::generic_type') as $key => $info) {
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
        $pageContent['cmdTypeIsHistorized'] = false;
        if ($cmd->getType() == 'info' && $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['visible']) {
            $pageContent['cmdIsHistorizedCanBeSmooth'] = $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['canBeSmooth'];
            $pageContent['cmdTypeIsHistorized'] = true;
            $pageContent['cmdIsHistorized'] = $cmd->getIsHistorized();
        }

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
        $pageContent['configDisplayStatsWidget'] = ConfigManager::byKey('displayStatsWidget');
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
        $pageContent['eqLogicDisplayType'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        $pageContent['cmd'] = $cmd;

        Utils::sendVarsToJS([
            'cmdInfo' => $cmdInfo,
            'cmdInfoSearchString' => urlencode(str_replace('#', '', $cmd->getHumanName()))
        ]);

        $render->show('/modals/cmd.configure.html.twig', $pageContent);
    }

    /**
     * Render command configure history modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cmdConfigureHistory(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $dataCount = array('history' => 0, 'timeline' => 0);
        $listCmd = array();
        foreach (CmdManager::all() as $cmd) {
            $info_cmd = \utils::o2a($cmd);
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

        $render->show('/modals/cmd.configureHistory.html.twig', $pageContent);
    }

    /**
     * Render command human insert modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cmdHumanInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['jeeObjects'] = JeeObjectManager::all();

        $render->show('/modals/cmd.human.insert.html.twig', $pageContent);
    }

    /**
     * Render command select multiple modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cmdSelectMultiple(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $cmdId = Utils::init('cmd_id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException('Commande non trouvée : ' . $cmdId);
        }

        $pageContent = [];
        $pageContent['currentCmd'] = $cmd;
        $pageContent['cmds'] = CmdManager::byTypeSubType($cmd->getType(), $cmd->getSubType());

        $render->show('/modals/cmd.selectMultiple.html.twig', $pageContent);
    }

    /**
     * Render action insert modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function cronHumanInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/cron.human.insert.html.twig');
    }

    /**
     * Render data store human insert modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function dataStoreHumanInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['dataStoreByType'] = \dataStore::byTypeLinkId(init('type', 'scenario'));

        $render->show('/modals/dataStore.human.insert.html.twig', $pageContent);
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
    public static function dataStoreManagement(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        Utils::sendVarsToJS(['dataStore_type' => Utils::init('type'),
            'dataStore_link_id' => Utils::init('link_id', -1)]);

        $render->show('/modals/dataStore.management.html.twig');
    }

    /**
     * Render eqLogic human insert modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function eqLogicHumanInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['jeeObjects'] = JeeObjectManager::all();

        $render->show('/modals/eqLogic.human.insert.html.twig', $pageContent);
    }

    /**
     * Render eqLogic management modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function eqLogicConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $eqLogicId = Utils::init('eqLogic_id');
        $eqLogic = EqLogicManager::byId($eqLogicId);
        if (!is_object($eqLogic)) {
            throw new CoreException('EqLogic non trouvé : ' . $eqLogicId);
        }

        Utils::sendVarsToJS(
            ['eqLogicInfo' => \utils::o2a($eqLogic),
                'eqLogicInfoSearchString' => urlencode(str_replace('#', '', $eqLogic->getHumanName()))]);

        $pageContent = [];
        $pageContent['widgetPossibilityCustom'] = $eqLogic->widgetPossibility('custom');
        $pageContent['widgetPossibilityCustomLayout'] = $eqLogic->widgetPossibility('custom::layout');
        $pageContent['widgetPossibilityCustomVisibility'] = $eqLogic->widgetPossibility('custom::visibility');
        $pageContent['widgetPossibilityCustomDisplayName'] = $eqLogic->widgetPossibility('custom::displayName');
        $pageContent['widgetPossibilityCustomDisplayObjectName'] = $eqLogic->widgetPossibility('custom::displayObjectName');
        $pageContent['widgetPossibilityCustomBackgroundColor'] = $eqLogic->widgetPossibility('custom::background-color');
        $pageContent['widgetPossibilityCustomBackgroundOpacity'] = $eqLogic->widgetPossibility('custom::background-opacity');
        $pageContent['widgetPossibilityCustomTextColor'] = $eqLogic->widgetPossibility('custom::text-color');
        $pageContent['widgetPossibilityCustomBorder'] = $eqLogic->widgetPossibility('custom::border');
        $pageContent['widgetPossibilityCustomBorderRadius'] = $eqLogic->widgetPossibility('custom::border-radius');
        $pageContent['widgetPossibilityCustomOptionalParameters'] = $eqLogic->widgetPossibility('custom::optionalParameters');

        $pageContent['statusNumberTryWithoutSuccess'] = $eqLogic->getStatus('numberTryWithoutSuccess', 0);
        $pageContent['statusLastCommunication'] = $eqLogic->getStatus('lastCommunication');
        $pageContent['cmdsList'] = $eqLogic->getCmd();
        $pageContent['eqLogicConfigurationDisplayType'] = [];
        $pageContent['eqLogicDisplayParameters'] = $eqLogic->getDisplay('parameters');

        foreach (NextDomHelper::getConfiguration('eqLogic:displayType') as $key => $value) {
            $eqLogicDisplayType = [];
            $eqLogicDisplayType['key'] = $key;
            $eqLogicDisplayType['name'] = $value['name'];
            $eqLogicDisplayType['customVisibility'] = false;
            if ($pageContent['widgetPossibilityCustomVisibility'] && $eqLogic->widgetPossibility('custom::visibility::' . $key)) {
                $eqLogicDisplayType['customVisibility'] = true;
            }
            $eqLogicDisplayType['customDisplayName'] = false;
            if ($pageContent['widgetPossibilityCustomDisplayName'] && $eqLogic->widgetPossibility('custom::displayName::' . $key)) {
                $eqLogicDisplayType['customDisplayName'] = true;
            }
            $eqLogicDisplayType['customDisplayObjectName'] = false;
            if ($pageContent['widgetPossibilityCustomDisplayObjectName'] && $eqLogic->widgetPossibility('custom::displayObjectName::' . $key)) {
                $eqLogicDisplayType['customDisplayObjectName'] = true;
            }
            $eqLogicDisplayType['customBackgroundColor'] = false;
            if ($pageContent['widgetPossibilityCustomBackgroundColor'] && $eqLogic->widgetPossibility('custom::background-color::' . $key)) {
                $eqLogicDisplayType['backgroundColor'] = $eqLogic->getBackgroundColor($key);
                $eqLogicDisplayType['customBackgroundColor'] = true;
            }
            $eqLogicDisplayType['customBackgroundOpacity'] = false;
            if ($pageContent['widgetPossibilityCustomBackgroundOpacity'] && $eqLogic->widgetPossibility('custom::background-opacity::' . $key)) {
                $eqLogicDisplayType['customBackgroundOpacity'] = true;
            }
            $eqLogicDisplayType['customTextColor'] = false;
            if ($pageContent['widgetPossibilityCustomTextColor'] && $eqLogic->widgetPossibility('custom::text-color::' . $key)) {
                $eqLogicDisplayType['customTextColor'] = true;
            }
            $eqLogicDisplayType['customBorder'] = false;
            if ($pageContent['widgetPossibilityCustomBorder'] && $eqLogic->widgetPossibility('custom::border::' . $key)) {
                $eqLogicDisplayType['customBorder'] = true;
            }
            $eqLogicDisplayType['customBorderRadius'] = false;
            if ($pageContent['widgetPossibilityCustomBorderRadius'] && $eqLogic->widgetPossibility('custom::border-radius::' . $key)) {
                $eqLogicDisplayType['customBorderRadius'] = true;
            }
            array_push($pageContent['eqLogicConfigurationDisplayType'], $eqLogicDisplayType);
        }
        if (is_array($eqLogic->widgetPossibility('parameters'))) {
            $pageContent['parameters'] = [];
            foreach ($eqLogic->widgetPossibility('parameters') as $parameterKey => $parameterData) {
                $param = [];
                $param['key'] = $parameterKey;
                $param['name'] = $parameterData['name'];
                $param['advancedParam'] = false;
                if (!isset($parameterData['allow_displayType'])) {
                    continue;
                }
                if (!isset($parameterData['type'])) {
                    continue;
                }
                if (is_array($parameterData['allow_displayType']) && !in_array($parameterKey, $parameterData['allow_displayType'])) {
                    continue;
                }
                if ($parameterData['allow_displayType'] === false) {
                    continue;
                }
                $param['advancedParam'] = true;
                $param['display'] = '';
                $param['default'] = '';
                if (isset($parameterData['default'])) {
                    $param['default'] = $parameterData['default'];
                    $param['display'] = 'display:none;';
                }
                $param['type'] = $parameterData['type'];
                if ($param['type'] == 'color') {
                    $param['allowTransparent'] = $parameterData['allow_transparent'];
                }

                array_push($pageContent['parameters'], $param);
            }
        }

        $pageContent['dashboardCmd'] = array();
        foreach ($eqLogic->getCmd(null, null, true) as $cmd) {
            $line = $eqLogic->getDisplay('layout::dashboard::table::cmd::' . $cmd->getId() . '::line', 1);
            $column = $eqLogic->getDisplay('layout::dashboard::table::cmd::' . $cmd->getId() . '::column', 1);
            if (!isset($pageContent['dashboardCmd'][$line])) {
                $pageContent['dashboardCmd'][$line] = array();
            }
            if (!isset($pageContent['dashboardCmd'][$line][$column])) {
                $pageContent['dashboardCmd'][$line][$column] = array();
            }
            $pageContent['dashboardCmd'][$line][$column][] = $cmd;
        }
        $pageContent['displayDashboardNbLine'] = $eqLogic->getDisplay('layout::dashboard::table::nbLine', 1);
        $pageContent['displayDashboardNbColumn'] = $eqLogic->getDisplay('layout::dashboard::table::nbColumn', 1);

        $render->show('/modals/eqLogic.configure.html.twig', $pageContent);
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
    public static function expressionTest(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $render->show('/modals/expression.test.html.twig');
    }

    /**
     * Render history calcul modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function historyCalcul(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/history.calcul.html.twig');
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
    public static function graphLink(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $configData = ConfigManager::byKeys(
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
     * Render icon selector modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function iconSelector(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['iconsList'] = [];
        foreach (ls('public/icon', '*') as $dir) {
            if (is_dir('public/icon/' . $dir) && file_exists('public/icon/' . $dir . '/style.css')) {
                $cssContent = file_get_contents('public/icon/' . $dir . '/style.css');
                $research = strtolower(str_replace('/', '', $dir));
                $pageContent['iconsList'][] = self::getIconsData($dir, $cssContent, "/\." . $research . "-(.*?):/");
            }
        }
        $nodeModules = [
//            ['name' => 'Font-Awesome 4', 'path' => 'vendor/node_modules/font-awesome/css/', 'cssFile' => 'font-awesome.css', 'cssPrefix' => 'fa'],
            ['name' => 'Font-Awesome 5', 'path' => 'vendor/node_modules/font-awesome5/css/', 'cssFile' => 'fontawesome-all.css', 'cssPrefix' => 'fa']
        ];
        foreach ($nodeModules as $nodeModule) {
            echo $nodeModule['name'].'<br/>';
            if (is_dir($nodeModule['path']) && file_exists($nodeModule['path'] . $nodeModule['cssFile'])) {
                $cssContent = file_get_contents($nodeModule['path'] . $nodeModule['cssFile']);
                $pageContent['iconsList'][] = self::getIconsData($nodeModule['path'], $cssContent, "/\." . $nodeModule['cssPrefix'] . "-(.*?):/", $nodeModule['name'], $nodeModule['cssPrefix']);
            }
        }
        $render->show('/modals/icon.selector.html.twig', $pageContent);
    }

    /**
     * Get icons data from CSS file
     *
     * @param string $path Path to the CSS file
     * @param string $cssContent Content of the CSS file
     * @param string $matchPattern Pattern for icon matchs
     * @param string|null $name Name of the font
     * @param string|null $cssClass CSS class to add
     * @return array
     */
    private static function getIconsData($path, $cssContent, $matchPattern, $name = null, $cssClass = null) {
        $data = [];
        preg_match_all($matchPattern, $cssContent, $matches, PREG_SET_ORDER);
        if ($name === null) {
            $data['name'] = str_replace('/', '', $path) ;
        }
        else {
            $data['name'] = $name;
        }
        $data['height'] = (ceil(count($matches) / 14) * 40) + 80;
        $data['list'] = [];
        foreach ($matches as $match) {
            if (isset($match[0])) {
                if ($cssClass === null) {
                    $data['list'][] = str_replace(array(':', '.'), '', $match[0]);
                }
                else {
                    $data['list'][] = $cssClass . ' ' . str_replace(array(':', '.'), '', $match[0]);
                }
            }
        }
        return $data;
    }

    /**
     * Render interact query display modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function interactQueryDisplay(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $interactDefId = Utils::init('interactDef_id', '');
        if ($interactDefId == '') {
            throw new CoreException(__('Interact Def ID ne peut être vide'));
        }
        $pageContent['interactQueries'] = \interactQuery::byInteractDefId($interactDefId);
        if (count($pageContent['interactQueries']) == 0) {
            throw new CoreException(__('Aucune phrase trouvée'));
        }

        Utils::sendVarToJS('interactDisplay_interactDef_id', $interactDefId);

        $render->show('/modals/interact.query.display.html.twig');
    }

    /**
     * Render interact tester modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function interactTest(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $render->show('/modals/interact.test.html.twig');
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
    public static function logDisplay(Render $render)
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
     * Render nextdom benchmark modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function nextdomBenchmark(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $pageContent['benchmark'] = NextDomHelper::benchmark();

        $render->show('/modals/nextdom.benchmark.html.twig', $pageContent);
    }

    /**
     * Render object configure modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function objectConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $objectId = Utils::init('object_id');
        $object = JeeObjectManager::byId($objectId);
        if (!is_object($object)) {
            throw new CoreException(__('Objet non trouvé : ') . $objectId);
        }
        Utils::sendVarToJS('objectInfo', \utils::o2a($object));

        $render->show('/modals/object.configure.html.twig');
    }

    /**
     * Render object display modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function objectDisplay(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $cmdClass = Utils::init('class');
        if ($cmdClass == '' || !class_exists($cmdClass)) {
            throw new CoreException(__('La classe demandée n\'existe pas : ') . $cmdClass);
        }
        if (!method_exists($cmdClass, 'byId')) {
            throw new CoreException(__('La classe demandée n\'a pas de méthode byId : ') . $cmdClass);
        }

        $object = $cmdClass::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('L\'objet n\'existe pas : ') . $cmdClass);
        }

        $data = \utils::o2a($object);
        if (count($data) == 0) {
            throw new CoreException(__('L\'objet n\'a aucun élément : ') . print_r($data, true));
        }
        $otherInfo = [];

        if ($cmdClass == 'cron' && $data['class'] == 'scenario' && $data['function'] == 'doIn') {
            $scenario = ScenarioManager::byId($data['option']['scenario_id']);
            //TODO: $array ???
            $scenarioElement = ScenarioElementManager::byId($array['option']['scenarioElement_id']);
            if (is_object($scenarioElement) && is_object($scenario)) {
                $otherInfo['doIn'] = __('Scénario : ') . $scenario->getName() . "\n" . str_replace(array('"'), array("'"), $scenarioElement->export());
            }
        }
        $pageContent = [];
        if (count($otherInfo) > 0) {
            $pageContent['otherData'] = [];
            foreach ($otherInfo as $otherInfoKey => $otherInfoValue) {
                $pageContent['otherData'][$otherInfoKey] = [];
                $pageContent['otherData'][$otherInfoKey]['value'] = $otherInfoValue;
                // TODO: Always long-text ???
                if (is_array($otherInfoValue)) {
                    $pageContent['otherData'][$otherInfoKey]['type'] = 'json';
                    $pageContent['otherData'][$otherInfoKey]['value'] = json_encode($otherInfoValue);
                } else if (strpos($otherInfoValue, "\n")) {
                    $pageContent['otherData'][$otherInfoKey]['type'] = 'long-text';
                } else {
                    $pageContent['otherData'][$otherInfoKey]['type'] = 'simple-text';
                }
            }
        }
        // TODO : Reduce loops
        $pageContent['data'] = [];
        foreach ($data as $dataKey => $dataValue) {
            $pageContent['data'][$dataKey] = [];
            $pageContent['data'][$dataKey]['value'] = $dataValue;
            if (is_array($dataValue)) {
                $pageContent['data'][$dataKey]['type'] = 'json';
                $pageContent['data'][$dataKey]['value'] = json_encode($dataValue);
            } else if (strpos($dataValue, "\n")) {
                $pageContent['data'][$dataKey]['type'] = 'long-text';
            } else {
                $pageContent['data'][$dataKey]['type'] = 'simple-text';
            }
        }
        $render->show('/modals/object.display.html.twig', $pageContent);
    }

    /**
     * Render object summary modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function objectSummary(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['objectsTree'] = JeeObjectManager::buildTree(null, false);
        $pageContent['configObjectSummary'] = [];
        $pageContent['summaryDesktopHidden'] = [];
        $pageContent['summaryMobileHidden'] = [];
        foreach ($pageContent['objectsTree'] as $jeeObject) {
            $jeeObjectId = $jeeObject->getId();
            foreach (ConfigManager::byKey('object:summary') as $key => $value) {
                $title = '';
                if (!isset($jeeObject->getConfiguration('summary')[$key]) || !is_array($jeeObject->getConfiguration('summary')[$key]) || count($jeeObject->getConfiguration('summary')[$key]) == 0) {
                    continue;
                }
                $pageContent['configObjectSummary'][$jeeObjectId] = [];
                foreach ($jeeObject->getConfiguration('summary')[$key] as $summary) {
                    if (CmdManager::byId(str_replace('#', '', $summary['cmd']))) {
                        $title .= '&#10;' . CmdManager::byId(str_replace('#', '', $summary['cmd']))->getHumanName();
                    } else {
                        $title .= '&#10;' . $summary['cmd'];
                    }
                }
                if (count($jeeObject->getConfiguration('summary')[$key]) > 0) {
                    $summary = [];
                    $summary['global'] = $jeeObject->getConfiguration('summary::global::' . $key) == 1;
                    $summary['title'] = $value['name'] . $title;
                    $summary['icon'] = $value['icon'];
                    $summary['count'] = count($jeeObject->getConfiguration('summary')[$key]);
                    $pageContent['configObjectSummary'][$jeeObjectId][] = $summary;
                }
                if ($jeeObject->getConfiguration('summary::hide::desktop::' . $key) == 1) {
                    $pageContent['summaryDesktopHidden'][] = ['name' => $value['name'], 'icon' => $value['icon']];
                }
                if ($jeeObject->getConfiguration('summary::hide::mobile::' . $key) == 1) {
                    $pageContent['summaryMobileHidden'][] = ['name' => $value['name'], 'icon' => $value['icon']];
                }
            }
        }

        $render->show('/modals/object.summary.html.twig', $pageContent);
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
    public static function planConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $pageContent['planObject'] = \plan::byId(Utils::init('id'));
        if (!is_object($pageContent['planObject'])) {
            throw new CoreException('Impossible de trouver le design');
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
    public static function planHeaderConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $planHeader = \planHeader::byId(Utils::init('planHeader_id'));
        if (!is_object($planHeader)) {
            throw new CoreException('Impossible de trouver le plan');
        }
        Utils::sendVarsToJS(['id' => $planHeader->getId(),
            'planHeader' => \utils::o2a($planHeader)]);

        $render->show('/modals/planHeader.configure.html.twig');
    }

    /**
     * Render plan 3d configure modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function plan3dConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $plan3d = \plan3d::byName3dHeaderId(init('name'), init('plan3dHeader_id'));
        if (!is_object($plan3d)) {
            $plan3d = new \plan3d();
            $plan3d->setName(init('name'));
            $plan3d->setPlan3dHeader_id(init('plan3dHeader_id'));
            $plan3d->save();
        }
        $link = $plan3d->getLink();
        Utils::sendVarToJS('id', $plan3d->getId());


        $render->show('/modals/plan3d.configure.html.twig', $pageContent);
    }

    /**
     * Render plugin daemon modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function pluginDaemon(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pluginId = init('plugin_id');
        if (!class_exists($pluginId)) {
            die();
        }
        $plugin = PluginManager::byId($pluginId);
        $daemonInfo = $plugin->deamon_info();
        if (count($daemonInfo) == 0) {
            die();
        }
        $refresh = array();
        $refresh[0] = 0;
        $pageContent['daemonInfoState'] = $daemonInfo['state'];
        $pageContent['daemonInfoLaunchable'] = $daemonInfo['launchable'];
        $pageContent['daemonInfoLaunchableMessage'] = '';
        if (isset($daemonInfo['launchable_message'])) {
            $pageContent['daemonInfoLaunchableMessage'] = $daemonInfo['launchable_message'];
        }
        $pageContent['daemonInfoAuto'] = 1;
        if (isset($daemonInfo['auto'])) {
            $pageContent['daemonInfoAuto'] = $daemonInfo['auto'];
        }
        if (isset($daemonInfo['last_launch'])) {
            $pageContent['daemonInfoLastLaunch'] = $daemonInfo['last_launch'];
        }
        Utils::sendVarsToJs(['plugin_id' => $pluginId, 'refresh_deamon_info' => $refresh]);

        $render->show('/modals/plugin.daemon.html.twig');
    }

    /**
     * Render plugin daemon modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function pluginDependency(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];
        $pluginId = init('plugin_id');
        Utils::sendVarToJs('plugin_id', $pluginId);
        if (!class_exists($pluginId)) {
            die();
        }
        $plugin = PluginManager::byId($pluginId);
        $pageContent['dependencyInfo'] = $plugin->getDependencyInfo();

        $render->show('/modals/plugin.dependency.html.twig');
    }

    /**
     * Render remove history modal
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
    public static function removeHistory(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();
        if (file_exists(NEXTDOM_ROOT . '/data/remove_history.json')) {
            $removeHistory = json_decode(file_get_contents(NEXTDOM_ROOT . '/data/remove_history.json'), true);
        }
        if (!is_array($removeHistory)) {
            $removeHistory = array();
        }

        $pageContent = [];
        $pageContent['removeHistory'] = $removeHistory;

        $render->show('/modals/remove.history.html.twig', $pageContent);
    }

    /**
     * Render report bug modal
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
    public static function reportBug(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        if (ConfigManager::byKey('market::address') == '') {
            throw new CoreException(__('Aucune adresse pour le market n\'est renseignée' ));
        }
        if (ConfigManager::byKey('market::apikey') == '' && ConfigManager::byKey('market::username') == '') {
            throw new CoreException(__('Aucun compte market n\'est renseigné. Veuillez vous enregistrer sur le market, puis renseignez vos identifiants dans') . ConfigManager::byKey('product_name') . __('avant d\'ouvrir un ticket'));
        }
        $render->show('/modals/report.bug.html.twig');
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
    public static function scenarioExport(Render $render)
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
     * Render scenario human insert modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function scenarioHumanInsert(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent = [];
        $pageContent['scenarios'] = ScenarioManager::all();

        $render->show('/modals/scenario.human.insert.html.twig', $pageContent);
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
    public static function scenarioLogExecution(Render $render)
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
    public static function scenarioSummary(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $render->show('/modals/scenario.summary.html.twig');
    }

    /**
     * Render scenario template modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function scenarioTemplate(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $scenarioId = Utils::init('scenario_id');
        $scenario = ScenarioManager::byId($scenarioId);
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario non trouvé : ') . $scenarioId);
        }
        Utils::sendVarToJS('scenario_template_id', $scenarioId);
        $pageContent = [];
        $pageContent['repoList'] = UpdateManager::listRepo();

        $render->show('/modals/scenario.template.html.twig', $pageContent);
    }

    /**
     * Render update add modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function updateAdd(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $pageContent = [];

        $pageContent['repoListType'] = [];
        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            if (ConfigManager::byKey($repoKey . '::enable') == 0) {
                continue;
            }
            $pageContent['repoListType'][$repoKey] = $repoValue['name'];
        }

        $pageContent['repoListConfiguration'] = [];
        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            $pageContent['repoListConfiguration'][$repoKey] = $repoValue;
        }
        $pageContent['ajaxToken'] = AjaxManager::getToken();

        $render->show('/modals/update.add.html.twig', $pageContent);
    }

    /**
     * Render update display modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function updateDisplay(Render $render)
    {
        self::showRepoModal('display');
    }

    /**
     * Render update list modal (market)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function updateList(Render $render)
    {
        self::showRepoModal('list');
    }

    /**
     * Render update send modal (market)
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function updateSend(Render $render)
    {
        self::showRepoModal('send');
    }

    /**
     * Show repo modal from code
     *
     * @param string $type Modal type
     *
     * @throws CoreException If repo is disabled
     */
    private static function showRepoModal($type) {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $repoId = Utils::init('repo', 'market');
        $repo = UpdateManager::repoById($repoId);
        if ($repo['enable'] == 0) {
            throw new CoreException(__('Le dépôt est inactif : ') . $repoId);
        }
        $repoDisplayFile = NEXTDOM_ROOT . '/core/repo/' . $repoId . '.display.repo.php';
        if (file_exists($repoDisplayFile)) {
            \include_file('core', $repoId . '.' . $type, 'repo', '', true);
        }
    }

    /**
     * Render user rights modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function userRights(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $userId = Utils::init('id');
        $user = \user::byId($userId);

        if (!is_object($user)) {
            throw new CoreException(__('Impossible de trouver l\'utilisateur : ') . $userId);
        }
        Utils::sendVarToJs('user_rights', \utils::o2a($user));

        $pageContent = [];
        $pageContent['restrictedUser'] = true;
        if ($user->getProfils() != 'restrict') {
            $pageContent['restrictedUser'] = false;
        }
        $pageContent['eqLogics'] = EqLogicManager::all();
        $pageContent['scenarios'] = ScenarioManager::all();

        $render->show('/modals/user.rights.html.twig', $pageContent);
    }

    /**
     * Render view configure modal
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
    public static function viewConfigure(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedAdminOrFail();

        $view = \view::byId(init('view_id'));
        if (!is_object($view)) {
            throw new CoreException('Impossible de trouver la vue');
        }
        Utils::sendVarsToJS(['id' => $view->getId(), 'view' => \utils::o2a($view)]);

        $render->show('/modals/view.configure.html.twig');
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
    public static function welcome(Render $render)
    {
        Status::initConnectState();
        Status::isConnectedOrFail();

        $pageContent['productName'] = ConfigManager::byKey('product_name');
        $render->show('/modals/welcome.html.twig', $pageContent);
    }
}
