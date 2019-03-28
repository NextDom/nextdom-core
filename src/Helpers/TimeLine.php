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

use NextDom\Managers\ConfigManager;

class TimeLine
{
    /**
     * Add an event in the timeline
     *
     * @param $event
     */
    public static function addTimelineEvent($event)
    {
        file_put_contents(NEXTDOM_DATA . '/data/timeline.json', json_encode($event) . "\n", FILE_APPEND);
    }

    /**
     * Get all event in timeline
     *
     * @return array
     * @throws \Exception
     */
    public static function getTimelineEvent(): array
    {
        $path = NEXTDOM_DATA . '/data/timeline.json';
        if (!file_exists($path)) {
            $result = array();
        } else {
            \com_shell::execute(SystemHelper::getCmdSudo() . 'chmod 666 ' . $path . ' > /dev/null 2>&1;echo "$(tail -n ' . ConfigManager::byKey('timeline::maxevent') . ' ' . $path . ')" > ' . $path);
            $lines = explode("\n", trim(file_get_contents($path)));
            $result = array();
            foreach ($lines as $line) {
                $result[] = json_decode($line, true);
            }
        }
        return $result;
    }

    /**
     * Remove event from the timeline
     */
    public static function removeTimelineEvent()
    {
        $path = NEXTDOM_DATA . '/data/timeline.json';
        // TODO: chmod 777
        \com_shell::execute(SystemHelper::getCmdSudo() . 'chmod 666 ' . $path . ' > /dev/null 2>&1;');
        unlink($path);
    }
}
