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


use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\SystemHelper;
use NextDom\Managers\UpdateManager;

class AdministrationController extends BaseController
{
    /**
     * Render administration page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of administration page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        $pageData['numberOfUpdates'] = UpdateManager::nbNeedUpdate();
        $this->initMemoryInformations($pageData);
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
        
        $pageData['JS_END_POOL'][] = '/public/js/desktop/administration.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/administration.html.twig', $pageData);
    }
    
    private function initMemoryInformations(&$pageData) {
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
}