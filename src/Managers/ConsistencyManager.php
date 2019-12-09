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
    private static $defaultSummary = [
        'security' => ['key' => 'security', 'name' => 'Alerte', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-alerte2"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'motion' => ['key' => 'motion', 'name' => 'Mouvement', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-mouvement"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'door' => ['key' => 'door', 'name' => 'Porte', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-porte-ouverte"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'windows' => ['key' => 'windows', 'name' => 'Fenêtre', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-fenetre-ouverte"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'shutter' => ['key' => 'shutter', 'name' => 'Volet', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-volet-ouvert"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'light' => ['key' => 'light', 'name' => 'Lumière', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-lumiere-on"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'outlet' => ['key' => 'outlet', 'name' => 'Prise', 'calcul' => 'sum', 'icon' => '<i class="icon nextdom-prise"></i>', 'unit' => '', 'count' => 'binary', 'allowDisplayZero' => false],
        'temperature' => ['key' => 'temperature', 'name' => 'Température', 'calcul' => 'avg', 'icon' => '<i class="icon divers-thermometer31"></i>', 'unit' => '°C', 'allowDisplayZero' => true],
        'humidity' => ['key' => 'humidity', 'name' => 'Humidité', 'calcul' => 'avg', 'icon' => '<i class="fa fa-tint"></i>', 'unit' => '%', 'allowDisplayZero' => true],
        'luminosity' => ['key' => 'luminosity', 'name' => 'Luminosité', 'calcul' => 'avg', 'icon' => '<i class="icon meteo-soleil"></i>', 'unit' => 'lx', 'allowDisplayZero' => false],
        'power' => ['key' => 'power', 'name' => 'Puissance', 'calcul' => 'sum', 'icon' => '<i class="fa fa-bolt"></i>', 'unit' => 'W', 'allowDisplayZero' => false],
    ];

    /**
     * Start consistency check of the system
     *
     * @throws CoreException
     */
    public static function checkConsistency()
    {
        try {
            self::ensureConfiguration();
            CronManager::clean();
            self::removeDeprecatedCrons();
            self::checkAllDefaultCrons();
            self::cleanWidgetCache();
            self::saveObjects();
            self::resetCommandsActionID();
        } catch (\Exception $e) {
            throw new CoreException("error while checking system consistency: " . $e->getMessage());
        }
    }

    /**
     * @TODO: ???
     * @throws \Exception
     */
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

    /**
     * Remove deprecated cron task
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    private static function removeDeprecatedCrons()
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

    /**
     * Check if all default cron task are present and add it
     */
    private static function checkAllDefaultCrons()
    {
        foreach (self::getDefaultCrons() as $cronClass => $cronData) {
            foreach ($cronData as $cronName => $cronConfig) {
                try {
                    $cron = CronManager::byClassAndFunction($cronClass, $cronName);
                    if (false == is_object($cron)) {
                        $cron = new Cron();
                    }
                    $cron->setClass($cronClass);
                    $cron->setFunction($cronName);
                    $cron->setSchedule($cronConfig["schedule"]);
                    $cron->setTimeout($cronConfig["timeout"]);
                    $cron->setDeamon(0);
                    if (true == array_key_exists("enabled", $cronConfig)) {
                        $cron->setEnable($cronConfig["enabled"]);
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
    public static function getDefaultCrons()
    {
        return [
            "nextdom" => [
                "backup" => [
                    "schedule" => mt_rand(10, 59) . ' 0' . mt_rand(0, 7) . ' * * *',
                    "timeout" => 60,
                    "enabled" => 1
                ],
                "cronDaily" => [
                    "schedule" => "00 00 * * * *",
                    "timeout" => 240,
                    "enabled" => 1
                ],
                "cronHourly" => [
                    "schedule" => "00 * * * * *",
                    "timeout" => 60,
                    "enabled" => 1
                ],
                "cron10" => [
                    "schedule" => "*/10 * * * * *",
                    "timeout" => 10
                ],
                "cron5" => [
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5
                ],
                "cron" => [
                    "schedule" => "* * * * * *",
                    "timeout" => 2
                ],
            ],
            "plugin" => [
                "cronDaily" => [
                    "schedule" => "00 00 * * * *",
                    "timeout" => 240,
                    "enabled" => 1
                ],
                "cronHourly" => [
                    "schedule" => "00 * * * * *",
                    "timeout" => 60,
                    "enabled" => 1
                ],
                "cron" => [
                    "schedule" => "* * * * * *",
                    "timeout" => 2
                ],
                "cron5" => [
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5,
                    "enabled" => 1
                ],
                "cron10" => [
                    "schedule" => "*/10 * * * * *",
                    "timeout" => 10
                ],
                "cron15" => [
                    "schedule" => "*/15 * * * * *",
                    "timeout" => 15
                ],
                "cron30" => [
                    "schedule" => "*/15 * * * * *",
                    "timeout" => 30
                ],
                "checkDeamon" => [
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 5
                ],
                "heartbeat" => [
                    "schedule" => "*/5 * * * * *",
                    "timeout" => 10,
                    "enabled" => 1
                ],
            ],
            "scenario" => [
                "check" => [
                    "schedule" => "* * * * * *",
                    "timeout" => 30,
                    "enabled" => 1
                ],
                "control" => [
                    "schedule" => "* * * * * *",
                    "timeout" => 30,
                    "enabled" => 1
                ],
            ],
            "cache" => [
                "persist" => [
                    "schedule" => "*/30 * * * * *",
                    "timeout" => 30
                ],
            ],
            "history" => [
                "archive" => [
                    "schedule" => "00 5 * * * *",
                    "timeout" => 240
                ],
            ],
        ];
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
            foreach (JeeObjectManager::all() as $c_item) {
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
}
