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
use NextDom\Helpers\Utils;
use NextDom\Helpers\NextDomHelper;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\EqLogicManager;


class EqLogicConfigure extends BaseAbstractModale
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }
    
    /**
     * Render eqLogic management modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function get(Render $render)
    {

        $eqLogicId = Utils::init('eqLogic_id');
        $eqLogic = EqLogicManager::byId($eqLogicId);
        if (!is_object($eqLogic)) {
            throw new CoreException('EqLogic non trouvé : ' . $eqLogicId);
        }

        Utils::sendVarsToJS(
            ['eqLogicInfo' => \utils::o2a($eqLogic),
                'eqLogicInfoSearchString' => urlencode(str_replace('#', '', $eqLogic->getHumanName()))]);

        $pageContent = [];
        $pageContent['widgetPossibilityCustom']                   = $eqLogic->widgetPossibility('custom');
        $pageContent['widgetPossibilityCustomLayout']             = $eqLogic->widgetPossibility('custom::layout');
        $pageContent['widgetPossibilityCustomVisibility']         = $eqLogic->widgetPossibility('custom::visibility');
        $pageContent['widgetPossibilityCustomDisplayName']        = $eqLogic->widgetPossibility('custom::displayName');
        $pageContent['widgetPossibilityCustomDisplayObjectName']  = $eqLogic->widgetPossibility('custom::displayObjectName');
        $pageContent['widgetPossibilityCustomBackgroundColor']    = $eqLogic->widgetPossibility('custom::background-color');
        $pageContent['widgetPossibilityCustomBackgroundOpacity']  = $eqLogic->widgetPossibility('custom::background-opacity');
        $pageContent['widgetPossibilityCustomTextColor']          = $eqLogic->widgetPossibility('custom::text-color');
        $pageContent['widgetPossibilityCustomBorder']             = $eqLogic->widgetPossibility('custom::border');
        $pageContent['widgetPossibilityCustomBorderRadius']       = $eqLogic->widgetPossibility('custom::border-radius');
        $pageContent['widgetPossibilityCustomOptionalParameters'] = $eqLogic->widgetPossibility('custom::optionalParameters');

        $pageContent['statusNumberTryWithoutSuccess'] = $eqLogic->getStatus('numberTryWithoutSuccess', 0);
        $pageContent['statusLastCommunication'] = $eqLogic->getStatus('lastCommunication');
        $pageContent['cmdsList'] = $eqLogic->getCmd();
        $pageContent['eqLogicConfigurationDisplayType'] = [];
        $pageContent['eqLogicDisplayParameters'] = $eqLogic->getDisplay('parameters');

        foreach (NextDomHelper::getConfiguration('eqLogic:displayType') as $key => $value) {
            $eqLogicDisplayType = [];
            $eqLogicDisplayType['key']  = $key;
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
        $pageContent['displayDashboardNbLine']   = $eqLogic->getDisplay('layout::dashboard::table::nbLine', 1);
        $pageContent['displayDashboardNbColumn'] = $eqLogic->getDisplay('layout::dashboard::table::nbColumn', 1);

      return $render->get('/modals/eqLogic.configure.html.twig', $pageContent);
    }

}
