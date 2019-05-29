<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.

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


namespace NextDom\Managers;

use NextDom\Exceptions\CoreException;
use NextDom\Model\Entity\Cron;

/**
 * Class ConsistencyManager
 * @package NextDom\Managers
 */
class ConsistencyManager
{
    private static $defaultSummary = array(
        'security' => array('key' => 'security', 'name' => 'Alerte', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-alerte2"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'motion' => array('key' => 'motion', 'name' => 'Mouvement', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-mouvement"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'door' => array('key' => 'door', 'name' => 'Porte', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-porte-ouverte"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'windows' => array('key' => 'windows', 'name' => 'Fenêtre', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-fenetre-ouverte"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'shutter' => array('key' => 'shutter', 'name' => 'Volet', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-volet-ouvert"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'light' => array('key' => 'light', 'name' => 'Lumière', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-lumiere-on"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'outlet' => array('key' => 'outlet', 'name' => 'Prise', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-prise"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false),
        'temperature' => array('key' => 'temperature', 'name' => 'Température', 'calcul' => 'avg', 'icon' => '<i class="icon divers-thermometer31"></i>', 'unit' => '°C', 'allowDisplayZero' => true),
        'humidity' => array('key' => 'humidity', 'name' => 'Humidité', 'calcul' => 'avg', 'icon' => '<i class="fa fa-tint"></i>', 'unit' => '%', 'allowDisplayZero' => true),
        'luminosity' => array('key' => 'luminosity', 'name' => 'Luminosité', 'calcul' => 'avg', 'icon' => '<i class="icon meteo-soleil"></i>', 'unit' => 'lx', 'allowDisplayZero' => false),
        'power' => array('key' => 'power', 'name' => 'Puissance', 'calcul' => 'sum', 'icon' => '<i class="fa fa-bolt"></i>', 'unit' => 'W', 'allowDisplayZero' => false),
    );

    public static function checkConsistency()
    {
        try {
            self::ensureConfiguration();
            CronManager::clean();
            self::deleteDeprecatedCrons();
            self::ensureCrons();
            self::cleanWidgetCache();
            self::saveObjects();
            self::resetCommandsActionID();
            self::ensureUserFunctionExists();
        } catch (\Exception $e) {
            throw new CoreException("error while checking system consistency: " . $e->getMessage());
        }
    }

    private static function ensureConfiguration()
    {
        $summary = ConfigManager::byKey("object:summary");
        if (!is_array($summary)) {
            ConfigManager::save("object:summary", self::$defaultSummary);
        }

        ConfigManager::save('hardware_name', '');
        if ("" == ConfigManager::byKey('api')) {
            ConfigManager::save('api', ConfigManager::genKey());
        }
    }

    private static function deleteDeprecatedCrons()
    {
        $cronTasksToRemove = [
            ['target_class' => 'nextdom', 'action' => 'persist'],
            ['target_class' => 'history', 'action' => 'historize'],
            ['target_class' => 'cmd', 'action' => 'collect'],
            ['target_class' => 'nextdom', 'action' => 'updateSystem'],
            ['target_class' => 'nextdom', 'action' => 'checkAndCollect'],
            ['target_class' => 'DB', 'action' => 'optimize'],
        ];
        foreach ($cronTasksToRemove as $cronTask) {
            $cron = CronManager::byClassAndFunction($cronTask['target_class'], $cronTask['action']);
            if (true == is_object($cron)) {
                $cron->remove();
            }
        }
    }

    private static function ensureCrons()
    {
        foreach (self::getDefaultCrons() as $c_class => $c_data) {
            foreach ($c_data as $c_name => $c_config) {
                try {
                    $cron = CronManager::byClassAndFunction($c_class, $c_name);
                    if (false == is_object($cron)) {
                        $cron = new Cron();
                    }
                    $cron->setClass($c_class);
                    $cron->setFunction($c_name);
                    $cron->setSchedule($c_config["schedule"]);
                    $cron->setTimeout($c_config["timeout"]);
                    $cron->setDeamon(0);
                    if (true == array_key_exists("enabled", $c_config)) {
                        $cron->setEnable($c_config["enabled"]);
                    }
                    $cron->save();
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @return array
     */
    private static function getDefaultCrons()
    {
        return array(
            "nextdom" => array(
                "backup" => array(
                    "schedule" => mt_rand(10, 59) . ' 0' . mt_rand(0, 7) . ' * * *',
                    "timeout" => 60,
                    "enabled" => 1
                ),
                "cronDaily" => array(
                    "schedule" => "00 00 * * * *",
                    "timeout" => 240,
                    "enabled" => 1
                ),
                "cronHourly" => array(
                    "schedule" => "00 * * * * *",
                    "timeout" => 60,
                    "enabled" => 1
                ),
                "cron5" => array(
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5
                ),
                "cron" => array(
                    "schedule" => "* * * * * *",
                    "timeout" => 2
                ),
            ),
            "plugin" => array(
                "cronDaily" => array(
                    "schedule" => "00 00 * * * *",
                    "timeout" => 240,
                    "enabled" => 1
                ),
                "cronHourly" => array(
                    "schedule" => "00 * * * * *",
                    "timeout" => 60,
                    "enabled" => 1
                ),
                "cron" => array(
                    "schedule" => "* * * * * *",
                    "timeout" => 2
                ),
                "cron5" => array(
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5,
                    "enabled" => 1
                ),
                "cron15" => array(
                    "schedule" => "*/15 * * * * *",
                    "timeout" => 15
                ),
                "cron30" => array(
                    "schedule" => "*/15 * * * * *",
                    "timeout" => 30
                ),
                "checkDeamon" => array(
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5
                ),
                "heartbeat" => array(
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 10,
                    "enabled" => 1
                ),
            ),
            "scenario" => array(
                "check" => array(
                    "schedule" => "* * * * * *",
                    "timeout" => 30,
                    "enabled" => 1
                ),
                "control" => array(
                    "schedule" => "* * * * * *",
                    "timeout" => 30,
                    "enabled" => 1
                ),
            ),
            "cache" => array(
                "persist" => array(
                    "schedule" => "*/30 * * * * *",
                    "timeout" => 30
                ),
            ),
            "history" => array(
                "archive" => array(
                    "schedule" => "00 5 * * * *",
                    "timeout" => 240
                ),
            ),
        );
    }

    private static function cleanWidgetCache()
    {
        foreach (EqLogicManager::all() as $c_item) {
            try {
                $c_item->emptyCacheWidget();
            } catch (\Exception $e) {
            }
        }
    }

    private static function saveObjects()
    {
        try {
            foreach (ObjectManager::all() as $c_item) {
                $c_item->save();
            }
        } catch (\Exception $e) {
        }
    }

    private static function resetCommandsActionID()
    {
        foreach (CmdManager::all() as $c_cmd) {
            try {
                $value = $c_cmd->getConfiguration("nextdomCheckCmdCmdActionId");
                if ("" != $value) {
                    $c_cmd->setConfiguration("nextdomCheckCmdCmdActionId", "");
                }
                $c_cmd->save();
            } catch (\Exception $e) {
            }
        }
    }

    private static function ensureUserFunctionExists()
    {
        $source = sprintf("%s/data/php/user.function.class.sample.php", NEXTDOM_DATA);
        $dest = sprintf("%s/data/php/user.function.class.php", NEXTDOM_DATA);

        if ((false == file_exists($dest)) &&
            (true == file_exists($source))) {
            copy($source, $dest);
        }
    }
}
