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
use NextDom\Managers\UpdateManager;
use NextDom\Controller\Modal;

class ModalsController
{
    const routesList = [
        'about'                      => Modal\About::class,
        'action.insert'              => Modal\ActionInsert::class,
        'cmd.configure'              => Modal\CmdConfigure::class,
        'cmd.configureHistory'       => Modal\CmdConfigureHistory::class,
        'cmd.graph.select'           => Modal\CmdGraphSelect::class,
        'cmd.history'                => Modal\CmdHistory::class,
        'cmd.human.insert'           => Modal\CmdHumanInsert::class,
        'cmd.selectMultiple'         => Modal\CmdSelectMultiple::class,
        'cron.human.insert'          => Modal\CronHumanInsert::class,
        'dataStore.human.insert'     => Modal\DataStoreHumanInsert::class,
        'dataStore.management'       => Modal\DataStoreManagement::class,
        'eqLogic.configure'          => Modal\EqLogicConfigure::class,
        'eqLogic.displayWidget'      => Modal\EqLogicDisplayWidget::class,
        'eqLogic.human.insert'       => Modal\EqLogicHumanInsert::class,
        'expression.test'            => Modal\ExpressionTest::class,
        'graph.link'                 => Modal\GraphLink::class,
        'history.calcul'             => Modal\HistoryCalcul::class,
        'icon.selector'              => Modal\IconSelector::class,
        'interact.query.display'     => Modal\InteractQueryDisplay::class,
        'interact.test'              => Modal\InteractTest::class,
        'log.display'                => Modal\LogDisplay::class,
        'nextdom.benchmark'          => Modal\NextdomBenchmark::class,
        'node.manager'               => Modal\NoteManager::class,
        'object.configure'           => Modal\ObjectConfigure::class,
        'object.display'             => Modal\ObjectDisplay::class,
        'object.summary'             => Modal\ObjectSummary::class,
        'plan.configure'             => Modal\PlanConfigure::class,
        'planHeader.configure'       => Modal\PlanHeaderConfigure::class,
        'plan3d.configure'           => Modal\Plan3dConfigure::class,
        'plan3dHeader.configure'     => Modal\Plan3dHeaderConfigure::class,
        'plugin.deamon'              => Modal\PluginDaemon::class,
        'plugin.dependancy'          => Modal\PluginDependency::class,
        'plugin.Market'              => Modal\PluginMarket::class,
        'remove.history'             => Modal\RemoveHistory::class,
        'report.bug'                 => Modal\ReportBug::class,
        'scenario.export'            => Modal\ScenarioExport::class,
        'scenario.human.insert'      => Modal\ScenarioHumanInsert::class,
        'scenario.jsonEdit'          => Modal\ScenarioJsonEdit::class,
        'scenario.log.execution'     => Modal\ScenarioLogExecution::class,
        'scenario.summary'           => Modal\ScenarioSummary::class,
        'scenario.template'          => Modal\ScenarioTemplate::class,
        'twoFactor.authentification' => Modal\TwoFactorAuthentification::class,
        'update.add'                 => Modal\UpdateAdd::class,
        'update.display'             => Modal\UpdateDisplay::class,
        'update.list'                => Modal\UpdateList::class,
        'update.send'                => Modal\UpdateSend::class,
        'user.rights'                => Modal\UserRights::class,
        'view.configure'             => Modal\ViewConfigure::class,
        'welcome'                    => Modal\Welcome::class,
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

}
