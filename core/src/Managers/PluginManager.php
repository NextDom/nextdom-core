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

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Enums\DaemonStateEnum;
use NextDom\Enums\PluginManagerCronEnum;

class PluginManager
{
    private static $cache = array();
    private static $enabledPlugins = null;

    /**
     * Obtenir un plugin à partir de son identifiant
     *
     * @param string $id Identifiant du plugin
     *
     * @return mixed|\plugin Plugin
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        if (is_string($id) && isset(self::$cache[$id])) {
            return self::$cache[$id];
        }
        if (!file_exists($id) || strpos($id, '/') === false) {
            $id = self::getPathById($id);
        }
        if (!file_exists($id)) {
            throw new \Exception('Plugin introuvable : ' . $id);
        }
        $data = json_decode(file_get_contents($id), true);
        if (!is_array($data)) {
            throw new \Exception('Plugin introuvable (json invalide) : ' . $id . ' => ' . print_r($data, true));
        }
        $plugin = new \plugin();
        $plugin->initPluginFromData($data);
        self::$cache[$plugin->getId()] = $plugin;
        return $plugin;
    }

    /**
     * Obtenir le chemin du fichier info.json à partir de l'identifiant du plugin
     *
     * @param string $id Identifiant du plugin
     * @return string Chemin vers le fichier info.json
     */
    public static function getPathById(string $id): string
    {
        return NEXTDOM_ROOT . '/plugins/' . $id . '/plugin_info/info.json';
    }

    /**
     * Obtenir la liste des plugins
     *
     * @param bool $activatedOnly Filtrer uniquement les plugins activés
     * @param bool $orderByCategory Trier par catégorie
     * @param bool $nameOnly Obtenir uniquement les noms des plugins
     *
     * @return array Liste des plugins
     *
     * @throws \Exception
     */
    public static function listPlugin(bool $activatedOnly = false,
                                      bool $orderByCategory = false,
                                      bool $nameOnly = false): array
    {
        $listPlugin = array();
        if ($activatedOnly) {
            $sql = "SELECT plugin
                    FROM config
                    WHERE `key` = 'active'
                    AND `value` = '1'";
            $queryResults = \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL);
            if ($nameOnly) {
                foreach ($queryResults as $row) {
                    $listPlugin[] = $row['plugin'];
                }
                return $listPlugin;
            } else {
                foreach ($queryResults as $row) {
                    try {
                        $listPlugin[] = self::byId($row['plugin']);
                    } catch (\Exception $e) {
                        \log::add('plugin', 'error', $e->getMessage(), 'pluginNotFound::' . $row['plugin']);
                    } catch (\Error $e) {
                        \log::add('plugin', 'error', $e->getMessage(), 'pluginNotFound::' . $row['plugin']);
                    }
                }
            }
        } else {
            $rootPluginPath = NEXTDOM_ROOT . '/plugins';
            foreach (\ls($rootPluginPath, '*') as $dirPlugin) {
                if (is_dir($rootPluginPath . '/' . $dirPlugin)) {
                    $pathInfoPlugin = $rootPluginPath . '/' . $dirPlugin . 'plugin_info/info.json';
                    if (file_exists($pathInfoPlugin)) {
                        try {
                            $listPlugin[] = self::byId($pathInfoPlugin);
                        } catch (\Exception $e) {
                            \log::add('plugin', 'error', $e->getMessage(), 'pluginNotFound::' . $pathInfoPlugin);
                        } catch (\Error $e) {
                            \log::add('plugin', 'error', $e->getMessage(), 'pluginNotFound::' . $pathInfoPlugin);
                        }
                    }
                }
            }
        }
        $returnValue = array();
        if ($orderByCategory) {
            if (count($listPlugin) > 0) {
                foreach ($listPlugin as $plugin) {
                    $category = $plugin->getCategory();
                    if ($category == '') {
                        $category = \__('Autre', __FILE__);
                    }
                    if (!isset($returnValue[$category])) {
                        $returnValue[$category] = array();
                    }
                    $returnValue[$category][] = $plugin;
                }
                foreach ($returnValue as &$category) {
                    usort($category, 'plugin::orderPlugin');
                }
                ksort($returnValue);
            }
        } else {
            if (isset($listPlugin) && is_array($listPlugin) && count($listPlugin) > 0) {
                usort($listPlugin, 'plugin::orderPlugin');
                $returnValue = $listPlugin;
            }
        }
        return $returnValue;
    }

    /**
     * Comparaison entre 2 plugins pour un tri
     *
     * @param $firstPlugin
     * @param $secondPluginName
     *
     * @return int Résultat de la comparaison
     */
    public static function orderPlugin(\plugin $firstPlugin, \plugin $secondPluginName): int
    {
        return strcmp(strtolower($firstPlugin->getName()), strtolower($secondPluginName->getName()));
    }

    /**
     * Tâche exécutée toutes les minutes
     *
     * @throws \Exception
     */
    public static function cron()
    {
        self::startCronTask(PluginManagerCronEnum::CRON);
    }

    /**
     * Tâche exécutée toutes les 5 minutes
     *
     * @throws \Exception
     */
    public static function cron5()
    {
        self::startCronTask(PluginManagerCronEnum::CRON_5);
    }

    /**
     * Tâche exécutée toutes les 15 minutes
     *
     * @throws \Exception
     */
    public static function cron15()
    {
        self::startCronTask(PluginManagerCronEnum::CRON_15);
    }

    /**
     * Tâche exécutée toutes les 30 minutes
     *
     * @throws \Exception
     */
    public static function cron30()
    {
        self::startCronTask(PluginManagerCronEnum::CRON_30);
    }

    /**
     * Tâche exécutée tous les jours
     *
     * @throws \Exception
     */
    public static function cronDaily()
    {
        self::startCronTask(PluginManagerCronEnum::CRON_DAILY);
    }

    /**
     * Tâche exécutée toutes les heures
     *
     * @throws \Exception
     */
    public static function cronHourly()
    {
        self::startCronTask(PluginManagerCronEnum::CRON_HOURLY);
    }

    /**
     * Démarre un tâche cron
     *
     * @param string $cronType Type de tâche cron, voir PluginManagerCronEnum
     * // TODO Rajouter un test sur l'enum ???
     * @throws \Exception
     */
    private static function startCronTask(string $cronType = '')
    {
        $cache = \cache::byKey('plugin::' . $cronType . '::inprogress');
        if ($cache->getValue(0) > 3) {
            \message::add('core', \__('La tache plugin::' . $cronType . ' n\'arrive pas à finir à cause du plugin : ') . \cache::byKey('plugin::'.$cronType.'::last')->getValue() . \__(' nous vous conseillons de désactiver le plugin et de contacter l\'auteur'));
        }
        \cache::set('plugin::'.$cronType.'::inprogress', $cache->getValue(0) + 1);
        foreach (self::listPlugin(true) as $plugin) {
            if (method_exists($plugin->getId(), $cronType)) {
                if (\config::byKey('functionality::cron::enable', $plugin->getId(), 1) == 1) {
                    $pluginId = $plugin->getId();
                    \cache::set('plugin::'.$cronType.'::last', $pluginId);
                    try {
                        $pluginId::$cronType();
                    } catch (\Exception $e) {
                        \log::add($pluginId, 'error', \__('Erreur sur la fonction cron du plugin : ', __FILE__) . $e->getMessage());
                    } catch (\Error $e) {
                        \log::add($pluginId, 'error', \__('Erreur sur la fonction cron du plugin : ', __FILE__) . $e->getMessage());
                    }
                }
            }
        }
        \cache::set('plugin::'.$cronType.'::inprogress', 0);
    }

    /**
     * Démarre les daemons des plugins
     *
     * @throws \Exception
     */
    public static function start()
    {
        foreach (self::listPlugin(true) as $plugin) {
            $plugin->deamon_start(false, true);
            if (method_exists($plugin->getId(), 'start')) {
                $pluginId = $plugin->getId();
                try {
                    $pluginId::start();
                } catch (\Exception $e) {
                    \log::add($pluginId, 'error', \__('Erreur sur la fonction start du plugin : ', __FILE__) . $e->getMessage());
                } catch (\Error $e) {
                    \log::add($pluginId, 'error', \__('Erreur sur la fonction start du plugin : ', __FILE__) . $e->getMessage());
                }
            }
        }
    }

    /**
     * Arrête les daemons des plugins
     *
     * @throws \Exception
     */
    public static function stop()
    {
        foreach (self::listPlugin(true) as $plugin) {
            $plugin->deamon_stop();
            if (method_exists($plugin->getId(), 'stop')) {
                $pluginId = $plugin->getId();
                try {
                    $pluginId::stop();
                } catch (\Exception $e) {
                    \log::add($pluginId, 'error', \__('Erreur sur la fonction stop du plugin : ', __FILE__) . $e->getMessage());
                } catch (\Error $e) {
                    \log::add($pluginId, 'error', \__('Erreur sur la fonction stop du plugin : ', __FILE__) . $e->getMessage());
                }
            }
        }
    }

    /**
     * Test le daemon TODO ??
     *
     * @throws \Exception
     */
    public static function checkDeamon()
    {
        foreach (self::listPlugin(true) as $plugin) {
            if (\config::byKey('deamonAutoMode', $plugin->getId(), 1) != 1) {
                continue;
            }
            $dependancy_info = $plugin->dependancy_info();
            if ($dependancy_info['state'] == DaemonStateEnum::NOT_OK) {
                try {
                    $plugin->dependancy_install();
                } catch (\Exception $e) {

                }
            } else if ($dependancy_info['state'] == DaemonStateEnum::IN_PROGRESS && $dependancy_info['duration'] > $plugin->getMaxDependancyInstallTime()) {
                if (isset($dependancy_info['progress_file']) && file_exists($dependancy_info['progress_file'])) {
                    shell_exec('rm ' . $dependancy_info['progress_file']);
                }
                \config::save('deamonAutoMode', 0, $plugin->getId());
                \log::add($plugin->getId(), 'error', \__('Attention : l\'installation des dépendances a dépassé le temps maximum autorisé : ', __FILE__) . $plugin->getMaxDependancyInstallTime() . 'min');
            }
            try {
                $plugin->deamon_start(false, true);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * Test si le plugin est actif
     * TODO: Doit passer en static
     * @return int
     */
    public static function isActive($id)
    {
        $result = 0;
        if (self::$enabledPlugins === null) {
            self::$enabledPlugins = \config::getPluginEnable();
        }
        if (isset(self::$enabledPlugins[$id])) {
            $result = self::$enabledPlugins[$id];
        }
        return $result;
    }
}