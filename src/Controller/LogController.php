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
use NextDom\Helpers\Utils;

class LogController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render log page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of log page
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {
        // TODO utiliser log::getpathLog
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/diagnostic/log.js';
        $currentLogfile = Utils::init('logfile');
        $logFilesList = [];
        $dir = opendir('/var/log/nextdom/');
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && $file != '.htaccess' && !is_dir('/var/log/nextdom/' . $file)) {
                $logFilesList[] = $file;
            }
        }
        natcasesort($logFilesList);
        $pageContent['logFilesList'] = [];
        foreach ($logFilesList as $logFile) {
            $logFileData = [];
            $logFileData['name'] = $logFile;
            $logFileData['icon'] = 'check';
            $logFileData['color'] = 'green';
            if (shell_exec('grep -c -E "\[ERROR\]|\[error\]" ' . '/var/log/nextdom/' . $logFile) != 0) {
                $logFileData['icon'] = 'exclamation-triangle';
                $logFileData['color'] = 'red';
            } elseif (shell_exec('grep -c -E "\[WARNING\]" ' . '/var/log/nextdom/' . $logFile) != 0) {
                $logFileData['icon'] = 'exclamation-circle';
                $logFileData['color'] = 'orange';
            }
            if ($currentLogfile == $logFile) {
                $logFileData['active'] = true;
            } else {
                $logFileData['active'] = false;
            }
            $logFileData['size'] = round(filesize('/var/log/nextdom/' . $logFile) / 1024);
            $pageContent['logFilesList'][] = $logFileData;
        }
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/diagnostic/logs-view.html.twig', $pageContent);
    }


}
