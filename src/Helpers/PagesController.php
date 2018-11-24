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

use NextDom\Managers\PluginManager;
use NextDom\Controller;

abstract class PagesController
{
    const routesList = [
        'dashboard'      => Controller\DashBoardController::class . '::dashboard',
        'scenario'       => Controller\ScenarioController::class . '::scenario',
        'administration' => Controller\AdministrationController::class . '::administration',
        'backup'         => Controller\BackupController::class . '::backup',
        'object'         => Controller\ObjectController::class . '::object',
        'message'        => Controller\MessageController::class . '::message',
        'cron'           => Controller\CronController::class . '::cron',
        'update'         => Controller\UpdateController::class . '::update',
        'system'         => Controller\SystemController::class . '::system',
        'database'       => Controller\DatabaseController::class . '::database',
        'display'        => Controller\DisplayController::class . '::display',
        'log'            => Controller\LogController::class . '::log',
        'report'         => Controller\ReportController::class . '::report',
        'plugin'         => Controller\PluginController::class . '::plugin',
        'editor'         => Controller\EditorController::class . '::editor',
        'migration'      => Controller\MigrationController::class . '::migration',
        'history'        => Controller\HistoryController::class . '::history',
        'timeline'       => Controller\HistoryController::class . '::history',
        'shutdown'       => Controller\SystemTimeline::class . '::timeline',
        'health'         => Controller\HealthController::class . '::health',
        'profils'        => Controller\ProfilsController::class . '::profils',
        'view'           => Controller\ViewsController::class . '::view',
        'view_edit'      => Controller\ViewsController::class . '::viewEdit',
        'eqAnalyse'      => Controller\EqAnalyzeController::class . '::eqAnalyze',
        'plan'           => Controller\PlanController::class . '::plan',
        'plan3d'         => Controller\PlanController::class . '::plan3d',
        'market'         => Controller\MarketController::class . '::market',
        'reboot'         => Controller\SystemController::class . '::reboot',
        'network'        => Controller\NetworkController::class . '::network',
        'cache'          => Controller\CacheController::class . '::cache',
        'general'        => Controller\GeneralController::class . '::general',
        'log_admin'      => Controller\LogController::class . '::logAdmin',
        'realtime'       => Controller\RealtimeController::class . '::realtime',
        'custom'         => Controller\CustomController::class . '::custom',
        'api'            => Controller\ApiController::class . '::Api',
        'commandes'      => Controller\CommandeController::class . '::commandes',
        'osdb'           => Controller\SystemController::class . '::osdb',
        'reports_admin'  => Controller\ReportController::class . '::reportsAdmin',
        'eqlogic'        => Controller\EqlogicController::class . '::eqlogic',
        'interact'       => Controller\InteractController::class . '::interact',
        'interact_admin' => Controller\InteractController::class . '::interactAdmin',
        'links'          => Controller\LinksController::class . '::links',
        'security'       => Controller\SecurityController::class . '::security',
        'summary'        => Controller\SummaryController::class . '::summary',
        'update_admin'   => Controller\UpdateController::class . '::updateAdmin',
        'users'          => Controller\UsersController::class . '::users',
        'note'           => Controller\NoteController::class . '::note',
        'pluginRoute'    => Controller\PluginController::class . '::pluginRoute'

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
        } elseif (in_array($page, PluginManager::listPlugin(true, false, true))) {
            $route = 'pluginRoute';
        }
        return $route;
    }
 
    /**
     * Render for all plugins pages
     *
     * @param Render $render Render engine (unused)
     * @param array $pageContent Page data (unused)
     * @return string Plugin page
     * @throws \Exception
     */
    public static function pluginRoute(Render $render, array &$pageContent): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        \include_file('desktop', $page, 'php', $plugin->getId(), true);
        return ob_get_clean();
    }

    /**
     * TODO this methode are used ?
     * 
     * @param \NextDom\Helpers\Render $render
     * @param array $pageContent
     * @return string
     */
    public static function panelPage(Render $render, array &$pageContent): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        \include_file('desktop', $page, 'php', $plugin->getId(), true);
        return ob_get_clean();
    }

}
