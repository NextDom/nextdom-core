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

namespace NextDom\Controller;

use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;

class HealthController extends BaseController
{
    /**
     * Render health page
     *
     * @param array $pageData Page data
     *
     * @return string Content of health page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {

        $pageData['healthInformations'] = NextDomHelper::health();
        $pageData['healthPluginsInformations'] = [];
        $pageData['healthPluginDataToShow'] = false;
        $pageData['healthTotalNOk'] = 0;
        $pageData['healthTotalPending'] = 0;
        // Santé pour chaque plugin
        foreach (PluginManager::listPlugin(true) as $plugin) {
            $pluginData = [];
            $pluginData['hasSpecificHealth'] = false;
            // Test si le plugin offre une information spécifique pour sa santé
            if (file_exists(dirname(PluginManager::getPathById($plugin->getId())) . '/../desktop/modal/health.php')) {
                $pluginData['hasSpecificHealth'] = true;
            }
            /**
             * Ajouter des informations sur la santé si le plugin
             *  - A des dépendances
             *  - A un daemon
             *  - A une méthode health
             *  - A une informations de santé spécifique
             */
            if ($plugin->getHasDependency() == 1 || $plugin->getHasOwnDeamon() == 1 || method_exists($plugin->getId(), 'health') || $pluginData['hasSpecificHealth']) {
                // Etat pour savoir si des données doivent être affichées
                $pageData['healthPluginDataToShow'] = true;
                // Objet du plugin
                $pluginData['plugin'] = $plugin;
                // Port si nécessaire
                $pluginData['port'] = false;
                // Nombre d'erreur
                $pluginData['nOk'] = 0;
                // Nombre d'état en attente
                $pluginData['pending'] = 0;
                // Etat pour savoir si le plugin a des dépendances
                $pluginData['hasDependency'] = false;
                // Etat pour savoir si le plugin a un daemon
                $pluginData['hasOwnDaemon'] = false;
                // Etat pour savoir si le tableau doit être affiché
                $pluginData['showOnlyTable'] = false;
                // Le port du plugin est stocké dans la configuration
                $port = ConfigManager::byKey('port', $plugin->getId());
                // Si un port est configuré, stockage pour la vue
                if ($port != '') {
                    $pluginData['port'] = $port;
                }
                // Si le plugin a des dépendances, un daemon, une méthode health ou des informations de santé
                // Affiche le tableau
                if ($plugin->getHasDependency() == 1 || $plugin->getHasOwnDeamon() == 1 || method_exists($plugin->getId(), 'health')) {
                    $pluginData['showOnlyTable'] = true;
                }
                // Si le plugin a des dépendances
                if ($plugin->getHasDependency() == 1) {
                    $pluginData['hasDependency'] = true;
                    $dependencyInfo = $plugin->getDependencyInfo();
                    // récupération des informations sur ses dépendances
                    if (isset($dependencyInfo['state'])) {
                        $pluginData['dependencyState'] = $dependencyInfo['state'];
                        // Erreur
                        if ($pluginData['dependencyState'] == 'nok') {
                            $pluginData['nOk']++;
                            // En cours
                        } elseif ($pluginData['dependencyState'] == 'in_progress') {
                            $pluginData['pending']++;
                            // Autres
                        } elseif ($pluginData['dependencyState'] != 'ok') {
                            $pluginData['nOk']++;
                        }
                    }
                }
                // Si le plugin a un daemon
                if ($plugin->getHasOwnDeamon() == 1) {
                    $pluginData['hasOwnDaemon'] = true;
                    $daemonInfo = $plugin->deamon_info();
                    // Statut du lancement automatique
                    $pluginData['daemonAuto'] = $daemonInfo['auto'];
                    if (isset($daemonInfo['launchable'])) {
                        // Erreur si le daemon doit est lançable et qu'il n'est pas lancé
                        $pluginData['daemonLaunchable'] = $daemonInfo['launchable'];
                        if ($pluginData['daemonLaunchable'] == 'nok' && $pluginData['daemonAuto'] == 1) {
                            $pluginData['nOk']++;
                        }
                    }
                    $pluginData['daemonLaunchableMessage'] = $daemonInfo['launchable_message'];
                    $pluginData['daemonState'] = $daemonInfo['state'];
                    // Erreur si le daemon doit se lancer en automatique et qu'il n'est pas lancé
                    if ($pluginData['daemonState'] == 'nok' && $pluginData['daemonAuto'] == 1) {
                        $pluginData['nOk']++;
                    }
                }
                // Données fournies par le plugin
                if (method_exists($plugin->getId(), 'health')) {
                    $pluginData['health'] = [];
                    // Récupère l'ensemble des données et boucle
                    foreach ($plugin->getId()::health() as $pluginHealthData) {
                        $pluginData['health'][] = [
                            'test' => $pluginHealthData['test'],
                            'state' => $pluginHealthData['state'],
                            'result' => $pluginHealthData['result'],
                            'advice' => $pluginHealthData['advice']
                        ];
                        if ($pluginHealthData['state'] === 'nok' || $pluginHealthData['state'] == false) {
                            $pluginData['nOk']++;
                        }
                    }
                }
                if ($pluginData['nOk'] > 0) {
                    $pageData['healthTotalNOk']++;
                }
                if ($pluginData['pending'] > 0) {
                    $pageData['healthTotalPending']++;
                }
                $pageData['healthPluginsInformations'][] = $pluginData;
            }
        }
        $pageData['JS_END_POOL'][] = '/public/js/desktop/diagnostic/health.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return Render::getInstance()->get('/desktop/diagnostic/health.html.twig', $pageData);
    }
}
