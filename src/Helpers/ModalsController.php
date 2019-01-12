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

use NextDom\Controller\Modale;

class ModalsController
{
    const routesList = [
        'about'                      => Modale\AboutModale::class,
        'action.insert'              => Modale\ActionInsertModale::class,
        'cmd.configure'              => Modale\CmdConfigureModale::class,
        'cmd.configureHistory'       => Modale\CmdConfigureHistory::class,
        'cmd.graph.select'           => Modale\CmdGraphSelect::class,
        'cmd.history'                => Modale\CmdHistory::class,
        'cmd.human.insert'           => Modale\CmdHumanInsert::class,
        'cmd.selectMultiple'         => Modale\CmdSelectMultiple::class,
        'cron.human.insert'          => Modale\CronHumanInsert::class,
        'dataStore.human.insert'     => Modale\DataStoreHumanInsert::class,
        'dataStore.management'       => Modale\DataStoreManageme::class,
        'eqLogic.configure'          => Modale\EqLogicConfigure::class,
        'eqLogic.displayWidget'      => Modals\EqLogicDisplayWidget::class,
        'eqLogic.human.insert'       => Modale\EqLogicHumanInsert::class,
        'expression.test'            => Modale\ExpressionTest::class,
        'graph.link'                 => Modale\GraphLink::class,
        'history.calcul'             => Modale\HistoryCalcul::class,
        'icon.selector'              => Modale\IconSelector::class,
        'interact.query.display'     => Modale\InteractQueryDisplay::class,
        'interact.test'              => Modale\InteractTest::class,
        'log.display'                => Modale\LogDisplay::class,
        'nextdom.benchmark'          => Modale\NextdomBenchmark::class,
        'node.manager'               => Modale\NoteManager::class,
        'object.configure'           => Modale\ObjectConfigure::class,
        'object.display'             => Modale\ObjectDisplay::class,
        'object.summary'             => Modale\ObjectSummary::class,
        'plan.configure'             => Modale\PlanConfigure::class,
        'planHeader.configure'       => Modale\PlanHeaderConfigure::class,
        'plan3d.configure'           => Modale\Plan3dConfigure::class,
        'plan3dHeader.configure'     => Modale\Plan3dHeaderConfigure::class,
        'plugin.deamon'              => Modale\PluginDaemon::class,
        'plugin.dependancy'          => Modale\PluginDependency::class,
        'plugin.Market'              => Modale\PluginMarket::class,
        'remove.history'             => Modale\RemoveHistory::class,
        'report.bug'                 => Modale\ReportBug::class,
        'scenario.export'            => Modale\ScenarioExport::class,
        'scenario.human.insert'      => Modale\ScenarioHumanInsert::class,
        'scenario.jsonEdit'          => Modale\ScenarioJsonEdit::class,
        'scenario.log.execution'     => Modale\ScenarioLogExecution::class,
        'scenario.summary'           => Modale\ScenarioSummary::class,
        'scenario.template'          => Modale\ScenarioTemplate::class,
        'twoFactor.authentification' => Modale\TwoFactorAuthentification::class ,
        'update.add'                 => Modale\UpdateAdd::class,
        'update.display'             => Modale\UpdateDisplay::class,
        'update.list'                => Modale\UpdateList::class,
        'update.send'                => Modale\UpdateSend::class,
        'user.rights'                => Modale\UserRights::class,
        'view.configure'             => Modale\ViewConfigure,
        'welcome'                    => Modale\WelcomeModale::class,
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
     * Show repo modal from code
     *
     * @param string $type Modal type
     *
     * @throws CoreException If repo is disabled
     */
    public static function showRepoModal($type)
    {
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

}
