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
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;

/**
 * Class CmdConfigure
 * @package NextDom\Controller\Modals
 */
class CmdConfigure extends BaseAbstractModal
{
    /**
     * Render command configuration modal
     *
     * @return string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function get(): string
    {
        $pageData = [];
        $cmdId = Utils::init('cmd_id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande non trouvé : ') . $cmdId);
        }
        $cmdInfo = NextDomHelper::toHumanReadable(Utils::o2a($cmd));
        foreach (array('dashboard', 'dview', 'mview', 'dplan') as $value) {
            if (!isset($cmdInfo['html'][$value]) || $cmdInfo['html'][$value] == '') {
                $cmdInfo['html'][$value] = $cmd->getWidgetTemplateCode($value);
            }
        }
        $pageData['cmdType'] = $cmd->getType();
        $pageData['cmdSubType'] = $cmd->getSubtype();
        $pageData['cmdWidgetPossibilityCustom'] = $cmd->widgetPossibility('custom');
        $pageData['cmdWidgetPossibilityCustomHtmlCode'] = $cmd->widgetPossibility('custom::htmlCode');
        $pageData['cmdShowMinMax'] = false;
        if ($pageData['cmdType'] == 'action' && $pageData['cmdSubType'] == 'select') {
            $pageData['cmdListValues'] = [];
            $elements = explode(';', $cmd->getConfiguration('listValue', ''));
            foreach ($elements as $element) {
                $pageData['cmdListValues'][] = explode('|', $element);
            }
        }
        if ($pageData['cmdType'] == 'info') {
            $pageData['cmdCacheValue'] = $cmd->getCache('value');
            $pageData['cmdCollectDate'] = $cmd->getCache('collectDate');
            $pageData['cmdValueDate'] = $cmd->getCache('valueDate');
            if ($cmd->getSubType() == 'numeric') {
                $pageData['cmdShowMinMax'] = true;
            }
        }
        $pageData['cmdDirectUrlAccess'] = $cmd->getDirectUrlAccess();
        $pageData['cmdUsedBy'] = $cmd->getUsedBy();
        $pageData['cmdGenericTypes'] = NextDomHelper::getConfiguration('cmd::generic_type');

        $pageData['cmdGenericTypeInformations'] = array();
        foreach (NextDomHelper::getConfiguration('cmd::generic_type') as $key => $info) {
            if (strtolower($cmd->getType()) != strtolower($info['type'])) {
                continue;
            } elseif (isset($info['ignore']) && $info['ignore']) {
                continue;
            }
            $info['key'] = $key;
            if (!isset($pageData['cmdGenericTypeInformations'][$info['family']])) {
                $pageData['cmdGenericTypeInformations'][$info['family']][0] = $info;
            } else {
                array_push($pageData['cmdGenericTypeInformations'][$info['family']], $info);
            }
        }
        ksort($pageData['cmdGenericTypeInformations']);
        foreach (array_keys($pageData['cmdGenericTypeInformations']) as $key) {
            usort($pageData['cmdGenericTypeInformations'][$key], function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        $pageData['cmdTypeIsHistorized'] = false;
        if ($cmd->getType() == 'info' && $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['visible']) {
            $pageData['cmdIsHistorizedCanBeSmooth'] = $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['canBeSmooth'];
            $pageData['cmdTypeIsHistorized'] = true;
            $pageData['cmdIsHistorized'] = $cmd->getIsHistorized();
        }

        $pageData['cmdWidgetCanCustomHtml'] = $cmd->widgetPossibility('custom::htmlCode');
        if ($pageData['cmdWidgetCanCustomHtml']) {
            $html = array();
            foreach (array('dashboard', 'dview', 'mview', 'dplan') as $value) {
                if ($cmd->getHtml($value) == '') {
                    $html[$value] = str_replace('textarea>', 'textarea$>', $cmd->getWidgetTemplateCode($value));
                } else {
                    $html[$value] = str_replace('textarea>', 'textarea$>', $cmd->getHtml($value));
                }
            }
            $pageData['cmdWidgetCustomHtmlValues'] = $html;
        }
        $pageData['cmdWidgetCanCustom'] = $cmd->widgetPossibility('custom');
        $pageData['cmdWidgetCanCustomWidget'] = $cmd->widgetPossibility('custom::widget');
        $pageData['cmdWidgetCanCustomWidgetDashboard'] = $cmd->widgetPossibility('custom::widget::dashboard');
        $pageData['cmdWidgetCanCustomVisibility'] = $cmd->widgetPossibility('custom::visibility');
        $pageData['cmdWidgetCanCustomDisplayName'] = $cmd->widgetPossibility('custom::displayName');
        $pageData['cmdWidgetCanCustomDisplayIconAndName'] = $cmd->widgetPossibility('custom::displayIconAndName');
        $pageData['cmdWidgetCanCustomDisplayStats'] = $cmd->widgetPossibility('custom::displayStats');
        $pageData['cmdWidgetCanCustomOptionalParameters'] = $cmd->widgetPossibility('custom::optionalParameters');
        $pageData['configDisplayStatsWidget'] = ConfigManager::byKey('displayStatsWidget');
        $pageData['cmdDisplayParameters'] = $cmd->getDisplay('parameters');

        $cmdWidgetDashboard = CmdManager::availableWidget('dashboard');
        if (is_array($cmdWidgetDashboard[$cmd->getType()]) && is_array($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) && count($cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()]) > 0) {
            $pageData['cmdWidgetDashboard'] = $cmdWidgetDashboard[$cmd->getType()][$cmd->getSubType()];
        }

        $pageData['alertsConfig'] = $NEXTDOM_INTERNAL_CONFIG['alerts'];
        $pageData['eqLogicDisplayType'] = NextDomHelper::getConfiguration('eqLogic:displayType');
        $pageData['cmd'] = $cmd;

        Utils::sendVarsToJS([
            'cmdInfo' => $cmdInfo,
            'cmdInfoSearchString' => urlencode(str_replace('#', '', $cmd->getHumanName()))
        ]);

        return Render::getInstance()->get('/modals/cmd.configure.html.twig', $pageData);
    }

}
