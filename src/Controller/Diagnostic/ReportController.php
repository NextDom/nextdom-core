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
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Render;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ViewManager;

/**
 * Class ReportController
 * @package NextDom\Controller\Diagnostic
 */
class ReportController extends BaseController
{
    /**
     * Render report page
     *
     * @param array $pageData Page data
     *
     * @return string Content of report page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        $pageData['JS_END_POOL'][] = '/public/js/desktop/diagnostic/report.js';
        $reportPath = NEXTDOM_DATA . '/data/custom/report/';
        $pageData['reportViews'] = [];
        $allViews = ViewManager::all();
        foreach ($allViews as $view) {
            $viewData = [];
            $viewData['id'] = $view->getId();
            $viewData['icon'] = $view->getDisplay('icon');
            $viewData['name'] = $view->getName();
            $viewData['number'] = count(FileSystemHelper::ls($reportPath . '/view/' . $view->getId(), '*'));
            $pageData['reportViews'][] = $viewData;
        }
        $pageData['reportPlans'] = [];
        $allPlanHeader = PlanHeaderManager::all();
        foreach ($allPlanHeader as $plan) {
            $planData = [];
            $planData['id'] = $plan->getId();
            $planData['icon'] = $plan->getConfiguration('icon');
            $planData['name'] = $plan->getName();
            $planData['number'] = count(FileSystemHelper::ls($reportPath . '/plan/' . $plan->getId(), '*'));
            $pageData['reportPlans'][] = $planData;
        }
        $pageData['reportPlugins'] = [];
        $pluginManagerList = PluginManager::listPlugin(true);
        foreach ($pluginManagerList as $plugin) {
            if ($plugin->getDisplay() != '') {
                $pluginData = [];
                $pluginData['id'] = $plugin->getId();
                $pluginData['name'] = $plugin->getName();
                $pluginData['number'] = count(FileSystemHelper::ls($reportPath . '/plugin/' . $plugin->getId(), '*'));
                $pageData['reportPlugins'][] = $pluginData;
            }
        }

        return Render::getInstance()->get('/desktop/diagnostic/report.html.twig', $pageData);
    }
}
