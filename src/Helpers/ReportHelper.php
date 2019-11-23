<?php
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

namespace NextDom\Helpers;

use NextDom\Enums\LogTarget;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;

/**
 * Class ReportHelper
 * @package NextDom\Helpers
 */
class ReportHelper
{
    public static function clean()
    {
        if (!file_exists(NEXTDOM_DATA . '/data/report')) {
            return;
        }
        shell_exec('find ' . NEXTDOM_DATA . '/data/report -type f -mtime +' . ConfigManager::byKey('report::maxdays') . ' -delete');
    }

    /**
     * @param $_url
     * @param $_type
     * @param $_name
     * @param string $_format
     * @param array $_parameter
     * @return string
     * @throws \Exception
     */
    public static function generate($_url, $_type, $_name, $_format = 'png', $_parameter = [])
    {
        if (!is_string($_format)) {
            $_format = 'png';
        }
        $out = NEXTDOM_DATA . '/data/report/';
        $out .= $_type . '/';
        $out .= $_name . '/';
        if (!file_exists($out)) {
            mkdir($out, 0775, true);
        }
        $out .= date('Y_m_d_H_i_s') . '.' . $_format;
        $min_width = (isset($_parameter['width']) && $_parameter['width'] > 800) ? $_parameter['width'] : 1280;
        $min_height = (isset($_parameter['height']) && $_parameter['height'] > 600) ? $_parameter['height'] : 1280;
        $delay = (isset($_parameter['delay']) && $_parameter['delay'] > 1000) ? $_parameter['delay'] : ConfigManager::byKey('report::delay');
        $_url .= '&auth=' . UserManager::getAccessKeyForReport();
        $cmd = 'xvfb-run --server-args="-screen 0, 1920x1280x24" cutycapt --min-width=' . $min_width . ' --min-height=' . $min_height . ' --url="' . $_url . '" --out="' . $out . '"';
        $cmd .= ' --delay=' . $delay;
        $cmd .= ' --print-backgrounds=on';
        LogHelper::addDebug(LogTarget::REPORT, $cmd);
        \com_shell::execute($cmd);
        return $out;
    }
}
