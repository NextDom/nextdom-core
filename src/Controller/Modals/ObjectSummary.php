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

use NextDom\Helpers\Render;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ObjectManager;

/**
 * Class ObjectSummary
 * @package NextDom\Controller\Modals
 */
class ObjectSummary extends BaseAbstractModal
{
    /**
     * Render object summary modal
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {

        $pageData = [];
        $pageData['objectsTree'] = ObjectManager::buildTree(null, false);
        $pageData['configObjectSummary'] = [];
        $pageData['summaryDesktopHidden'] = [];
        $pageData['summaryMobileHidden'] = [];

        foreach ($pageData['objectsTree'] as $jeeObject) {
            $jeeObjectId = $jeeObject->getId();
            $objectSummaryConfiguration = $jeeObject->getConfiguration('summary');
            foreach (ConfigManager::byKey('object:summary') as $summaryKey => $summaryData) {
                $title = '';
                // Test if jeeObject configuration exists and is valid
                if (!isset($objectSummaryConfiguration[$summaryKey]) ||
                    !is_array($objectSummaryConfiguration[$summaryKey]) ||
                    count($objectSummaryConfiguration[$summaryKey]) === 0) {
                    continue;
                }
                $pageData['configObjectSummary'][$jeeObjectId] = [];
                foreach ($objectSummaryConfiguration[$summaryKey] as $summary) {
                    $cmd = CmdManager::byId(str_replace('#', '', $summary['cmd']));
                    if ($cmd) {
                        $title .= '&#10;' . $cmd->getHumanName();
                    } else {
                        $title .= '&#10;' . $summary['cmd'];
                    }
                }
                if (count($objectSummaryConfiguration[$summaryKey]) > 0) {
                    $summary = [];
                    $summary['global'] = $jeeObject->getConfiguration('summary::global::' . $summaryKey) == 1;
                    $summary['title'] = $summaryData['name'] . $title;
                    $summary['icon'] = $summaryData['icon'];
                    $summary['count'] = count($objectSummaryConfiguration[$summaryKey]);
                    $pageData['configObjectSummary'][$jeeObjectId][] = $summary;
                }
                if ($jeeObject->getConfiguration('summary::hide::desktop::' . $summaryKey) == 1) {
                    $pageData['summaryDesktopHidden'][] = ['name' => $summaryData['name'], 'icon' => $summaryData['icon']];
                }
                if ($jeeObject->getConfiguration('summary::hide::mobile::' . $summaryKey) == 1) {
                    $pageData['summaryMobileHidden'][] = ['name' => $summaryData['name'], 'icon' => $summaryData['icon']];
                }
            }
        }

        return Render::getInstance()->get('/modals/object.summary.html.twig', $pageData);
    }

}
