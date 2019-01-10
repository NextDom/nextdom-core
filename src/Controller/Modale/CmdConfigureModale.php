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

namespace NextDom\Controller\Modale;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Managers\ConfigManager;
use NextDom\Exceptions\CoreException;

class CmdConfigureModale extends BaseAbstractModale
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render command configuration modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render): string
    {
        $pageContent = [];
        $cmdId       = Utils::init('cmd_id');
        $cmd         = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException('Commande non trouvé : ' . $cmdId);
        }
        $cmdInfo = NextDomHelper::toHumanReadable(\utils::o2a($cmd));
        foreach (array('dashboard', 'mobile', 'dview', 'mview', 'dplan') as $value) {
            if (!isset($cmdInfo['html'][$value]) || $cmdInfo['html'][$value] == '') {
                $cmdInfo['html'][$value] = $cmd->getWidgetTemplateCode($value);
            }
        }
        $pageContent['cmdType']                            = $cmd->getType();
        $pageContent['cmdSubType']                         = $cmd->getSubtype();
        $pageContent['cmdWidgetPossibilityCustom']         = $cmd->widgetPossibility('custom');
        $pageContent['cmdWidgetPossibilityCustomHtmlCode'] = $cmd->widgetPossibility('custom::htmlCode');
        $pageContent['cmdShowMinMax']                      = false;
        if ($pageContent['cmdType'] == 'action' && $pageContent['cmdSubType'] == 'select') {
            $pageContent['cmdListValues'] = [];
            $elements                     = explode(';', $cmd->getConfiguration('listValue', ''));
            foreach ($elements as $element) {
                $pageContent['cmdListValues'][] = explode('|', $element);
            }
        }
        if ($pageContent['cmdType'] == 'info') {
            $pageContent['cmdCacheValue']  = $cmd->getCache('value');
            $pageContent['cmdCollectDate'] = $cmd->getCache('collectDate');
            $pageContent['cmdValueDate']   = $cmd->getCache('valueDate');
            if ($cmd->getSubType() == 'numeric') {
                $pageContent['cmdShowMinMax'] = true;
            }
        }
        $pageContent['cmdDirectUrlAccess'] = $cmd->getDirectUrlAccess();
        $pageContent['cmdUsedBy']          = $cmd->getUsedBy();
        $pageContent['cmdGenericTypes']    = NextDomHelper::getConfiguration('cmd::generic_type');

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
            $pageContent['cmdTypeIsHistorized']        = true;
            $pageContent['cmdIsHistorized']            = $cmd->getIsHistorized();
        }

        $pageContent['cmdWidgetCanCustomHtml']               = $cmd->widgetPossibility('custom::htmlCode');
        $pageContent['cmdWidgetCanCustom']                   = $cmd->widgetPossibility('custom');
        $pageContent['cmdWidgetCanCustomWidget']             = $cmd->widgetPossibility('custom::widget');
        $pageContent['cmdWidgetCanCustomWidgetDashboard']    = $cmd->widgetPossibility('custom::widget::dashboard');
        $pageContent['cmdWidgetCanCustomWidgetMobile']       = $cmd->widgetPossibility('custom::widget::mobile');
        $pageContent['cmdWidgetCanCustomVisibility']         = $cmd->widgetPossibility('custom::visibility');
        $pageContent['cmdWidgetCanCustomDisplayName']        = $cmd->widgetPossibility('custom::displayName');
        $pageContent['cmdWidgetCanCustomDisplayIconAndName'] = $cmd->widgetPossibility('custom::displayIconAndName');
        $pageContent['cmdWidgetCanCustomDisplayStats']       = $cmd->widgetPossibility('custom::displayStats');
        $pageContent['cmdWidgetCanCustomOptionalParameters'] = $cmd->widgetPossibility('custom::optionalParameters');
        $pageContent['configDisplayStatsWidget']             = ConfigManager::byKey('displayStatsWidget');
        $pageContent['cmdDisplayParameters']                 = $cmd->getDisplay('parameters');

        $cmdWidgetDashboard = CmdManager::availableWidget('dashboard');
        $cmdWidgetMobile    = CmdManager::availableWidget('mobile');
        if (is_array($cmdWidgetDashboard[$cmd->getType()]) && is_array($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) && count($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) > 0) {
            $pageContent['cmdWidgetDashboard'] = $cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()];
        }
        if (is_array($cmdWidgetMobile[$cmd->getType()]) && is_array($cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()]) && count($cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()]) > 0) {
            $pageContent['cmdWidgetMobile'] = $cmdWidgetMobile[$cmd->getType()][$cmd->getSubType()];
        }

        $pageContent['alertsConfig']       = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageContent['eqLogicDisplayType'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        $pageContent['cmd']                = $cmd;

        Utils::sendVarsToJS([
            'cmdInfo'             => $cmdInfo,
            'cmdInfoSearchString' => urlencode(str_replace('#', '', $cmd->getHumanName()))
        ]);

        return $render->get('/modals/cmd.configure.html.twig', $pageContent);
    }

}
