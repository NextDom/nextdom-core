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

namespace NextDom\Controller\Diagnostic;

use NextDom\Controller\BaseController;
use NextDom\Enums\ControllerData;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;

/**
 * Class LogController
 * @package NextDom\Controller\Diagnostic
 */
class LogController extends BaseController
{
    /**
     * Render log page
     *
     * @param array $pageData Page data
     *
     * @return string Content of log page
     *
     */
    public static function get(&$pageData): string
    {

        $currentLogfile = Utils::init('logfile');
        $logFilesList = [];
        $dir = opendir(NEXTDOM_LOG);
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && $file != '.htaccess' && !is_dir(NEXTDOM_LOG . '/' . $file)) {
                $logFilesList[] = $file;
            }
        }
        $pageData['logFilesList'] = [];
        foreach ($logFilesList as $logFile) {
            $logFileData = [];
            $logFileData['name'] = $logFile;
            if ($currentLogfile == $logFile) {
                $logFileData['active'] = true;
            } else {
                $logFileData['active'] = false;
            }
            $logFileData['size'] = round(filesize(NEXTDOM_LOG . '/' . $logFile) / 1024);
            $logFileData['icon'] = 'check';
            $logFileData['color'] = 'green';
            $logFileData['timing'] = date("YmdHis", filemtime(NEXTDOM_LOG . '/' . $logFile));
            if ($logFileData['size'] < 10000) {
                if (shell_exec('grep -c -E "\[ERROR\]|\[error\]" ' . NEXTDOM_LOG . '/' . $logFile) != 0) {
                    $logFileData['icon'] = 'exclamation-triangle';
                    $logFileData['color'] = 'red';
                } elseif (shell_exec('grep -c -E "\[WARNING\]" ' . NEXTDOM_LOG . '/' . $logFile) != 0) {
                    $logFileData['icon'] = 'exclamation-circle';
                    $logFileData['color'] = 'orange';
                }
            } else {
                $logFileData['icon'] = 'exclamation-triangle';
                $logFileData['color'] = 'red';
            }
            $pageData['logFilesList'][] = $logFileData;
        }

        $pageData[ControllerData::JS_END_POOL][] = '/public/js/desktop/diagnostic/log.js';

        return Render::getInstance()->get('/desktop/diagnostic/log.html.twig', $pageData);
    }


}
