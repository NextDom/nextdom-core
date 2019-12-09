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
use NextDom\Managers\EqLogicManager;


/**
 * Class EqLogicConfigure
 * @package NextDom\Controller\Modals
 */
class EqLogicConfigure extends BaseAbstractModal
{
    /**
     * Render eqLogic management modal
     *
     * @return string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function get(): string
    {


        $eqLogicId = Utils::init('eqLogic_id');
        $eqLogic = EqLogicManager::byId($eqLogicId);
        if (!is_object($eqLogic)) {
            throw new CoreException('EqLogic non trouvé : ' . $eqLogicId);
        }

        Utils::sendVarsToJS(
            ['eqLogicInfo' => Utils::o2a($eqLogic),
                'eqLogicInfoSearchString' => urlencode(str_replace('#', '', $eqLogic->getHumanName()))]);

        $pageData = [];

        $widgetPossibilities = [
            ['code' => 'customVisibility', 'key' => 'custom::visibility'],
            ['code' => 'customDisplayName', 'key' => 'custom::displayName'],
            ['code' => 'customDisplayObjectName', 'key' => 'custom::displayObjectName'],
            ['code' => 'customBackgroundColor', 'key' => 'custom::background-color'],
            ['code' => 'customBackgroundOpacity', 'key' => 'custom::background-opacity'],
            ['code' => 'customTextColor', 'key' => 'custom::text-color'],
            ['code' => 'customBorder', 'key' => 'custom::border'],
            ['code' => 'customBorderRadius', 'key' => 'custom::border-radius'],
            ['code' => 'custom', 'key' => 'custom'],
            ['code' => 'customLayout', 'key' => 'custom::layout'],
            ['code' => 'customOptionalParameters', 'key' => 'custom::optionalParameters']
        ];
        foreach ($widgetPossibilities as $widgetPossibility) {
            $pageData[$widgetPossibility['code']] = $eqLogic->widgetPossibility($widgetPossibility['key']);
        }

        $pageData['statusNumberTryWithoutSuccess'] = $eqLogic->getStatus('numberTryWithoutSuccess', 0);
        $pageData['statusLastCommunication'] = $eqLogic->getStatus('lastCommunication');
        $pageData['cmdsList'] = $eqLogic->getCmd();
        $pageData['eqLogicConfigurationDisplayType'] = [];
        $pageData['eqLogicDisplayParameters'] = $eqLogic->getDisplay('parameters');

        foreach (NextDomHelper::getConfiguration('eqLogic:displayType') as $key => $value) {
            // TODO : A supprimer quand on aura trouvé où est initialisé eqLogic:displayType et retiré mobile
            $eqLogicDisplayType = [];
            $eqLogicDisplayType['key'] = $key;
            $eqLogicDisplayType['name'] = $value['name'];
            foreach ($widgetPossibilities as $widgetPossibility) {
                $eqLogicDisplayType[$widgetPossibility['code']] = false;
                if ($pageData[$widgetPossibility['code']] && $eqLogic->widgetPossibility($widgetPossibility['key'] . '::' . $key)) {
                    if ($widgetPossibility['code'] == 'customBackgroundColor') {
                        $eqLogicDisplayType['backgroundColor'] = $eqLogic->getBackgroundColor($key);
                    }
                    $eqLogicDisplayType[$widgetPossibility['code']] = true;
                }
            }
            array_push($pageData['eqLogicConfigurationDisplayType'], $eqLogicDisplayType);
        }
        if (is_array($eqLogic->widgetPossibility('parameters'))) {
            $pageData['parameters'] = [];
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

                array_push($pageData['parameters'], $param);
            }
        }

        $pageData['dashboardCmd'] = [];

        foreach ($eqLogic->getCmd(null, null, true) as $cmd) {
            $line = $eqLogic->getDisplay('layout::dashboard::table::cmd::' . $cmd->getId() . '::line', 1);
            $column = $eqLogic->getDisplay('layout::dashboard::table::cmd::' . $cmd->getId() . '::column', 1);
            if (!isset($pageData['dashboardCmd'][$line])) {
                $pageData['dashboardCmd'][$line] = [];
            }
            if (!isset($pageData['dashboardCmd'][$line][$column])) {
                $pageData['dashboardCmd'][$line][$column] = [];
            }
            $pageData['dashboardCmd'][$line][$column][] = $cmd;
        }
        $pageData['displayDashboardNbLines'] = $eqLogic->getDisplay('layout::dashboard::table::nbLine', 1);
        $pageData['displayDashboardNbColumns'] = $eqLogic->getDisplay('layout::dashboard::table::nbColumn', 1);

        return Render::getInstance()->get('/modals/eqLogic.configure.html.twig', $pageData);
    }

}
