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

class PagesController
{
    const routesList = [
        'dashboard'      => Controller\DashBoardController::class,
        'scenario'       => Controller\ScenarioController::class,
        'administration' => Controller\AdministrationController::class,
        'backup'         => Controller\BackupController::class,
        'object'         => Controller\ObjectController::class,
        'message'        => Controller\MessageController::class,
        'cron'           => Controller\CronController::class,
        'update'         => Controller\UpdateController::class,
        'system'         => Controller\SystemController::class,
        'database'       => Controller\DatabaseController::class,
        'display'        => Controller\DisplayController::class,
        'log'            => Controller\LogController::class,
        'report'         => Controller\ReportController::class,
        'plugin'         => Controller\PluginListController::class,
        'editor'         => Controller\EditorController::class,
        'migration'      => Controller\MigrationController::class,
        'history'        => Controller\HistoryController::class,
        'timeline'       => Controller\TimelineController::class,
        'shutdown'       => Controller\ShutdownController::class,
        'health'         => Controller\HealthController::class,
        'profils'        => Controller\ProfilsController::class,
        'view'           => Controller\ViewController::class,
        'view_edit'      => Controller\ViewEditController::class,
        'eqAnalyse'      => Controller\EqAnalyzeController::class,
        'plan'           => Controller\PlanController::class,
        'plan3d'         => Controller\Plan3DController::class,
        'market'         => Controller\MarketController::class,
        'reboot'         => Controller\RebootController::class,
        'network'        => Controller\NetworkController::class,
        'cache'          => Controller\CacheController::class,
        'general'        => Controller\GeneralController::class,
        'log_admin'      => Controller\LogAdminController::class,
        'realtime'       => Controller\RealtimeController::class,
        'custom'         => Controller\CustomController::class,
        'api'            => Controller\ApiController::class,
        'commandes'      => Controller\CommandeController::class,
        'osdb'           => Controller\OsDbController::class,
        'reports_admin'  => Controller\ReportAdminController::class,
        'eqlogic'        => Controller\EqlogicController::class,
        'interact'       => Controller\InteractController::class,
        'interact_admin' => Controller\InteractAdminController::class,
        'links'          => Controller\LinksController::class,
        'security'       => Controller\SecurityController::class,
        'summary'        => Controller\SummaryController::class,
        'update_admin'   => Controller\UpdateAdminController::class,
        'users'          => Controller\UsersController::class,
        'note'           => Controller\NoteController::class,
        'panel'          => Controller\PanelPageController::class
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
            $route = Controller\PluginController::class;
        }
        return $route;
    }
 
    

}
