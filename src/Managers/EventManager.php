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
 * NextDom Software is free software: you can redistribute it and/or modify
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

namespace NextDom\Managers;

use NextDom\Enums\DateFormat;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;

/**
 * Class EventManager
 *
 * @package NextDom\Managers
 */
class EventManager
{
    /**
     * @var int Max events processed each time
     */
    protected static $MAX_EVENTS_BY_PROCESS = 250;
    /**
     * @var mixed Event lock file
     */
    protected static $eventLockFile = null;

    /**
     * Add event in cache
     *
     * @param string $eventName
     * @param array $options
     * @throws \Exception
     */
    public static function add($eventName, $options = [])
    {
        $waitIfLocked = true;
        $fd = self::getEventLockFile();
        if (flock($fd, LOCK_EX, $waitIfLocked)) {
            $cache = CacheManager::byKey('event');
            $value = json_decode($cache->getValue('[]'), true);
            if (!is_array($value)) {
                $value = [];
            }
            $value[] = ['datetime' => Utils::getMicrotime(), 'name' => $eventName, 'option' => $options];
            CacheManager::set('event', json_encode(self::cleanEvent($value)));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Get event cache file object
     *
     * @return bool|null|resource
     * @throws \Exception
     */
    protected static function getEventLockFile()
    {
        if (self::$eventLockFile === null) {
            self::$eventLockFile = fopen(NextDomHelper::getTmpFolder() . '/event_cache_lock', 'w');
            chmod(NextDomHelper::getTmpFolder() . '/event_cache_lock', 0666);
        }
        return self::$eventLockFile;
    }

    /**
     * Get the last MAX_EVENTS_BY_PROCESS events
     * @param $events
     * @return array
     */
    protected static function cleanEvent($events)
    {
        $events = array_slice(array_values($events), -self::$MAX_EVENTS_BY_PROCESS, self::$MAX_EVENTS_BY_PROCESS);
        $find = [];
        $currentTime = strtotime(DateFormat::NOW) + 300;
        foreach (array_values($events) as $key => $event) {
            if ($event['datetime'] > $currentTime) {
                unset($events[$key]);
                continue;
            }
            if ($event['name'] == 'eqLogic::update') {
                $id = $event['name'] . '::' . $event['option']['eqLogic_id'];
            } elseif ($event['name'] == 'cmd::update') {
                $id = $event['name'] . '::' . $event['option']['cmd_id'];
            } elseif ($event['name'] == 'scenario::update') {
                $id = $event['name'] . '::' . $event['option']['scenario_id'];
            } elseif ($event['name'] == 'jeeObject::summary::update') {
                $id = $event['name'] . '::' . $event['option']['object_id'];
                if (is_array($event['option']['keys']) && count($event['option']['keys']) > 0) {
                    foreach ($event['option']['keys'] as $optionKey => $value) {
                        $id .= $optionKey;
                    }
                }
            } else {
                continue;
            }
            if ($id != '' && isset($find[$id]) && $find[$id] > $event['datetime']) {
                unset($events[$key]);
                continue;
            }
            $find[$id] = $event['datetime'];
        }
        return array_values($events);
    }

    /**
     * Add multiple events in cache
     *
     * @param string $eventName
     * @param array $values
     * @throws \Exception
     */
    public static function adds($eventName, $values = [])
    {
        $waitIfLocked = true;
        $fd = self::getEventLockFile();
        if (flock($fd, LOCK_EX, $waitIfLocked)) {
            $cache = CacheManager::byKey('event');
            $value_src = json_decode($cache->getValue('[]'), true);
            if (!is_array($value_src)) {
                $value_src = [];
            }
            $value = [];
            foreach ($values as $option) {
                $value[] = ['datetime' => Utils::getMicrotime(), 'name' => $eventName, 'option' => $option];
            }
            CacheManager::set('event', json_encode(self::cleanEvent(array_merge($value_src, $value))));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Method used for sorting event by datetime
     *
     * @param mixed $eventA First event to compare
     * @param mixed $eventB Second event to compare
     *
     * @return int Result at usort PHP function format
     */
    public static function orderEvent($eventA, $eventB)
    {
        return ($eventA['datetime'] - $eventB['datetime']);
    }

    /**
     * Get new events since a datetime
     *
     * @param mixed $_datetime Event time
     * @param null $_longPolling Wait for new events
     * @param null $_filter Event filter
     * @return array
     * @throws \Exception
     */
    public static function changes($_datetime, $_longPolling = null, $_filter = null)
    {
        $return = self::filterEvent(self::changesSince($_datetime), $_filter);
        if ($_longPolling === null || count($return['result']) > 0) {
            return $return;
        }
        $waitTime = ConfigManager::byKey('event::waitPollingTime');
        $i = 0;
        $max_cycle = $_longPolling / $waitTime;
        while (count($return['result']) == 0 && $i < $max_cycle) {
            if ($waitTime < 1) {
                usleep(1000000 * $waitTime);
            } else {
                sleep(round($waitTime));
            }
            sleep(1);
            $return = self::filterEvent(self::changesSince($_datetime), $_filter);
            $i++;
        }
        return $return;
    }

    /**
     * Get events filtered by name
     *
     * @param array $_data
     * @param null $_filter
     * @return array Filtered events
     * @throws \Exception
     */
    protected static function filterEvent($_data = [], $_filter = null)
    {
        if ($_filter == null) {
            return $_data;
        }
        $filters = ($_filter !== null) ? CacheManager::byKey($_filter . '::event')->getValue([]) : [];
        $return = ['datetime' => $_data['datetime'], 'result' => []];
        foreach ($_data['result'] as $value) {
            if ($_filter !== null && isset($_filter::$_listenEvents) && !in_array($value['name'], $_filter::$_listenEvents)) {
                continue;
            }
            if (count($filters) != 0 && $value['name'] == 'cmd::update' && !in_array($value['option']['cmd_id'], $filters)) {
                continue;
            }
            $return['result'][] = $value;
        }

        return $return;
    }

    /**
     * Get events whose state has changed since a datetime
     *
     * @param mixed $_datetime Limit datetime
     *
     * @return array Associative array with all events
     * @throws \Exception
     */
    protected static function changesSince($_datetime)
    {
        $return = ['datetime' => $_datetime, 'result' => []];
        $cache = CacheManager::byKey('event');
        $events = json_decode($cache->getValue('[]'), true);
        if (!is_array($events)) {
            $events = [];
        }
        $values = array_reverse($events);
        if (count($values) > 0) {
            $return['datetime'] = $values[0]['datetime'];
            foreach ($values as $value) {
                if ($value['datetime'] <= $_datetime) {
                    break;
                }
                $return['result'][] = $value;
            }
        }
        $return['result'] = array_reverse($return['result']);
        return $return;
    }
}
