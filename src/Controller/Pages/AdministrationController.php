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

namespace NextDom\Controller\Pages;

use NextDom\Controller\BaseController;
use NextDom\Enums\ControllerData;
use NextDom\Helpers\Render;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\WidgetManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\ViewManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\NoteManager;
use NextDom\Managers\CronManager;

/**
 * Class AdministrationController
 * @package NextDom\Controller\Pages
 */
class AdministrationController extends BaseController
{
    /**
     * Render administration page
     *
     * @param array $pageData Page data
     *
     * @return string Content of administration page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $pageData['numberOfUpdates'] = UpdateManager::nbNeedUpdate();
        $pageData['scenarioCount'] = ScenarioManager::getCount();
        $pageData['interactCount'] = InteractDefManager::getCount();
        $pageData['pluginsCount'] = count(PluginManager::listPlugin());
        $pageData['objectCount'] = JeeObjectManager::getCount();
        $pageData['noteCount'] = NoteManager::getCount();
        $pageData['widgetCount'] = WidgetManager::getCount();
        $pageData['viewCount'] = ViewManager::getCount();
        $pageData['planHeaderCount'] = PlanHeaderManager::getCount();
        $pageData['cronCountEnable'] = 0;
        $pageData['cronCountDisable'] = 0;
        foreach (CronManager::all() as $cron) {
            if ($cron->getEnable()) {
                $pageData['cronCountEnable']++;
            } else {
                $pageData['cronCountDisable']++;
            }
        }
        self::countErrorLog($pageData);
        self::initMemoryInformations($pageData);
        $uptime = SystemHelper::getUptime() % 31556926;
        $pageData['uptimeDays'] = explode(".", ($uptime / 86400))[0];
        $pageData['uptimeHours'] = explode(".", (($uptime % 86400) / 3600))[0];
        $pageData['uptimeMinutes'] = explode(".", ((($uptime % 86400) % 3600) / 60))[0];
        $pageData['cpuCoresCount'] = SystemHelper::getProcessorCoresCount();
        $pageData['cpuLoad'] = round(100 * (sys_getloadavg()[0] / $pageData['cpuCoresCount']), 2);
        $diskTotal = disk_total_space(NEXTDOM_ROOT);
        $pageData['hddLoad'] = round(100 - 100 * disk_free_space(NEXTDOM_ROOT) / $diskTotal, 2);
        if ($diskTotal < 1024) {
            $diskTotal = $diskTotal . ' B';
        } elseif ($diskTotal < (1024 * 1024)) {
            $diskTotal = round($diskTotal / 1024, 0) . ' KB';
        } elseif ($diskTotal < (1024 * 1024 * 1024)) {
            $diskTotal = round($diskTotal / (1024 * 1024), 0) . ' MB';
        } else {
            $diskTotal = round($diskTotal / (1024 * 1024 * 1024), 0) . ' GB';
        }
        $pageData['hddSize'] = $diskTotal;
        $pageData['httpConnectionsCount'] = SystemHelper::getHttpConnectionsCount();
        $pageData['processCount'] = SystemHelper::getProcessCount();
        $pageData[ControllerData::CSS_POOL][] = '/public/css/pages/administration.css';
        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/pages/administration.js';

        return Render::getInstance()->get('/desktop/pages/administration.html.twig', $pageData);
    }

    /**
     * @param $pageData
     */
    private static function initMemoryInformations(&$pageData)
    {
        $pageData['memoryLoad'] = 100;
        $pageData['swapLoad'] = 100;
        $freeData = trim(shell_exec('free'));
        $freeData = explode("\n", $freeData);
        if (count($freeData) > 2) {
            $memData = array_merge(
                array_filter(
                    explode(' ', $freeData[1]),
                    function ($value) {
                        return $value !== '';
                    }
                )
            );
            $swapData = array_merge(
                array_filter(
                    explode(' ', $freeData[2]),
                    function ($value) {
                        return $value !== '';
                    }
                )
           );
            if ($memData[1] != 0) {
                $pageData['memoryLoad'] = round(100 * $memData[2] / $memData[1], 2);
                if ($memData[1] < 1024) {
                    $memTotal = $memData[1] . ' B';
                } elseif ($memData[1] < (1024 * 1024)) {
                    $memTotal = round($memData[1] / 1024, 0) . ' MB';
                } else {
                    $memTotal = round($memData[1] / 1024 / 1024, 0) . ' GB';
                }
                $pageData['totalMemory'] = $memTotal;
            } else {
                $pageData['memoryLoad'] = 0;
                $pageData['totalMemory'] = 0;
            }
            if ($swapData[1] != 0) {
                $pageData['swapLoad'] = round(100 * $swapData[2] / $swapData[1], 2);
                if ($swapData[1] < 1024) {
                    $swapTotal = $swapData[1] . ' B';
                } elseif ($memData[1] < (1024 * 1024)) {
                    $swapTotal = round($swapData[1] / 1024, 0) . ' MB';
                } else {
                    $swapTotal = round($swapData[1] / 1024 / 1024, 0) . ' GB';
                }
                $pageData['administrationSwapTotal'] = $swapTotal;
            } else {
                $pageData['swapLoad'] = 0;
            }
        }
    }

    /**
     * @param $pageData
     */
    private static function countErrorLog(&$pageData)
    {
        $pageData['logCount'] = 0;
        $logFilesList = [];
        $dir = opendir(NEXTDOM_LOG);
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && $file != '.htaccess' && !is_dir(NEXTDOM_LOG . '/' . $file)) {
                $logFilesList[] = $file;
            }
        }
        foreach ($logFilesList as $logFile) {
            if (round(filesize(NEXTDOM_LOG . '/' . $logFile) / 1024) < 10000) {
                if (shell_exec('grep -c -E "\[ERROR\]|\[error\]" ' . NEXTDOM_LOG . '/' . $logFile) != 0) {
                    $pageData['logCount']++;
                }
            } else {
                $pageData['logCount']++;
            }
        }
    }
}
