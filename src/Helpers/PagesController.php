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


abstract class PagesController
{
    const routesList = [
        'dashboard'      => '\NextDom\Controller\DashBoardController::dashboard',
        'scenario'       => '\NextDom\Controller\ScenarioController::scenario',
        'administration' => '\NextDom\Controller\AdministrationController::administration',
        'backup'         => '\NextDom\Controller\BackupController::backup',
        'object'         => '\NextDom\Controller\ObjectController::object',
        'message'        => '\NextDom\Controller\MessageController::message',
        'cron'           => '\NextDom\Controller\CronController::cron',
        'update'         => '\NextDom\Controller\UpdateController::update',
        'system'         => '\NextDom\Controller\SystemController::system',
        'database'       => '\NextDom\Controller\DatabaseController::database',
        'display'        => '\NextDom\Controller\DisplayController::display',
        'log'            => '\NextDom\Controller\LogController::log',
        'report'         => '\NextDom\Controller\ReportController::report',
        'plugin'         => '\NextDom\Controller\PluginController::plugin',
        'editor'         => '\NextDom\Controller\EditorController::editor',
        'migration'      => '\NextDom\Controller\MigrationController::migration',
        'history'        => '\NextDom\Controller\HistoryController::history',
        'shutdown'       => '\NextDom\Controller\SystemController::shutdown',
        'health'         => '\NextDom\Controller\HealthController::health',
        'profils'        => '\NextDom\Controller\ProfilsController::profils',
        'view'           => '\NextDom\Controller\ViewsController::view',
        'view_edit'      => '\NextDom\Controller\ViewsController::viewEdit',
        'eqAnalyse'      => '\NextDom\Controller\EqAnalyzeController::eqAnalyze',
        'plan'           => '\NextDom\Controller\PlanController::plan',
        'plan3d'         => '\NextDom\Controller\PlanController::plan3d',
        'market'         => '\NextDom\Controller\MarketController::market',
        'reboot'         => '\NextDom\Controller\SystemController::reboot',
        'network'        => '\NextDom\Controller\NetworkController::network',
        'cache'          => '\NextDom\Controller\CacheController::cache',
        'general'        => '\NextDom\Controller\GeneralController::general',
        'log_admin'      => '\NextDom\Controller\LogController::logAdmin',
        'realtime'       => '\NextDom\Controller\RealtimeController::realtime',
        'custom'         => '\NextDom\Controller\CustomController::custom',
        'api'            => '\NextDom\Controller\ApiController::Api',
        'commandes'      => '\NextDom\Controller\CommandeController::commandes',
        'osdb'           => '\NextDom\Controller\SystemController::osdb',
        'reports_admin'  => '\NextDom\Controller\ReportController::reportsAdmin',
        'eqlogic'        => '\NextDom\Controller\EqlogicController::eqlogic',
        'interact'       => '\NextDom\Controller\InteractController::interact',
        'interact_admin' => '\NextDom\Controller\InteractController::interactAdmin',
        'links'          => '\NextDom\Controller\LinksController::links',
        'security'       => '\NextDom\Controller\SecurityController::security',
        'summary'        => '\NextDom\Controller\SummaryController::summary',
        'update_admin'   => '\NextDom\Controller\UpdateController::updateAdmin',
        'users'          => '\NextDom\Controller\UsersController::users',
        'note'           => '\NextDom\Controller\NoteController::note',
        'pluginRoute'    => '\NextDom\Controller\PluginController::pluginRoute'

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

    public static function panelPage(Render $render, array &$pageContent): string
    {
        $plugin = PluginManager::byId(Utils::init('m'));
        $page = Utils::init('p');

        ob_start();
        \include_file('desktop', $page, 'php', $plugin->getId(), true);
        return ob_get_clean();
    }

}
