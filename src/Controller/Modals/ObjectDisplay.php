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

use NextDom\Enums\AjaxParams;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioManager;

/**
 * Class ObjectDisplay
 * @package NextDom\Controller\Modals
 */
class ObjectDisplay extends BaseAbstractModal
{
    /**
     * Render object display modal
     *
     * @return string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function get(): string
    {
        $cmdClass = Utils::init('class');
        if ($cmdClass == '' || !class_exists($cmdClass)) {
            throw new CoreException(__('La classe demandée n\'existe pas : ') . $cmdClass);
        }
        if (!method_exists($cmdClass, 'byId')) {
            throw new CoreException(__('La classe demandée n\'a pas de méthode byId : ') . $cmdClass);
        }

        $resultObject = $cmdClass::byId(Utils::init(AjaxParams::ID));
        if (!is_object($resultObject)) {
            throw new CoreException(__('L\'objet n\'existe pas : ') . $cmdClass);
        }

        $data = Utils::o2a($resultObject);
        if (count($data) == 0) {
            throw new CoreException(__('L\'objet n\'a aucun élément : ') . print_r($data, true));
        }
        $otherInfo = [];

        if ($cmdClass == 'cron' && $data['class'] == 'scenario' && $data['function'] == 'doIn') {
            $scenario = ScenarioManager::byId($data['option']['scenario_id']);
            //@TODO: $array ???
            $scenarioElement = ScenarioElementManager::byId($data['option']['scenarioElement_id']);
            if (is_object($scenarioElement) && is_object($scenario)) {
                $otherInfo['doIn'] = __('Scénario : ') . $scenario->getName() . "\n" . str_replace(['"'], ["'"], $scenarioElement->export());
            }
        }

        $pageData = [];

        if (count($otherInfo) > 0) {
            $pageData['otherData'] = [];
            foreach ($otherInfo as $otherInfoKey => $otherInfoValue) {
                $pageData['otherData'][$otherInfoKey] = [];
                $pageData['otherData'][$otherInfoKey]['value'] = $otherInfoValue;
                // @TODO: Always long-text ???
                if (is_array($otherInfoValue)) {
                    $pageData['otherData'][$otherInfoKey]['type'] = 'json';
                    $pageData['otherData'][$otherInfoKey]['value'] = json_encode($otherInfoValue);
                } elseif (strpos($otherInfoValue, "\n")) {
                    $pageData['otherData'][$otherInfoKey]['type'] = 'long-text';
                } else {
                    $pageData['otherData'][$otherInfoKey]['type'] = 'simple-text';
                }
            }
        }
        // @TODO : Reduce loops
        $pageData['data'] = [];
        foreach ($data as $dataKey => $dataValue) {
            $pageData['data'][$dataKey] = [];
            $pageData['data'][$dataKey]['value'] = $dataValue;
            if (is_array($dataValue)) {
                $pageData['data'][$dataKey]['type'] = 'json';
                $pageData['data'][$dataKey]['value'] = json_encode($dataValue);
            } elseif (strpos($dataValue, "\n")) {
                $pageData['data'][$dataKey]['type'] = 'long-text';
            } else {
                $pageData['data'][$dataKey]['type'] = 'simple-text';
            }
        }
        return Render::getInstance()->get('/modals/object.display.html.twig', $pageData);
    }

}
