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

use NextDom\Enums\CacheKey;
use NextDom\Enums\Common;
use NextDom\Enums\DateFormat;
use NextDom\Helpers\NextDomHelper;

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
    private static $MAX_EVENTS_BY_PROCESS = 250;
    /**
     * @var mixed Event lock file
     */
    private static $eventLockFile = null;

    /**
     * Get event cache file object
     *
     * @return bool|null|resource
     * @throws \Exception
     */
    public static function getFileDescriptorLock()
    {
        if (self::$eventLockFile === null) {
            self::$eventLockFile = fopen(NextDomHelper::getTmpFolder() . '/event_cache_lock', 'w');
            chmod(NextDomHelper::getTmpFolder() . '/event_cache_lock', 0777);
        }
        return self::$eventLockFile;
    }

    /**
     * Add event in cache
     *
     * @param string $eventCode
     * @param array $eventOptions
     *
     * @throws \Exception
     */
    public static function add($eventCode, $eventOptions = [])
    {
        $waitIfLocked = true;
        $fd = self::getFileDescriptorLock();
        if (flock($fd, LOCK_EX, $waitIfLocked)) {
            $cache = CacheManager::byKey(CacheKey::EVENT);
            $value = json_decode($cache->getValue('[]'), true);
            if (!is_array($value)) {
                $value = [];
            }
            $value[] = [Common::DATETIME => getmicrotime(), Common::NAME => $eventCode, 'option' => $eventOptions];
            CacheManager::set(CacheKey::EVENT, json_encode(self::cleanEvent($value)));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Add multiple events in cache
     *
     * @param string $eventName
     * @param array $values
     * @throws \Exception
     */
    public static function adds($eventCode, $eventOptions = [])
    {
        $waitIfLocked = true;
        $fd = self::getFileDescriptorLock();
        if (flock($fd, LOCK_EX, $waitIfLocked)) {
            $cache = CacheManager::byKey(CacheKey::EVENT);
            $value_src = json_decode($cache->getValue('[]'), true);
            if (!is_array($value_src)) {
                $value_src = array();
            }
            $value = array();
            foreach ($eventOptions as $option) {
                $value[] = array(Common::DATETIME => getmicrotime(), Common::NAME => $eventCode, 'option' => $option);
            }
            CacheManager::set(CacheKey::EVENT, json_encode(self::cleanEvent(array_merge($value_src, $value))));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Clean events
     *
     * @param $events
     * @return array
     */
    public static function cleanEvent($_events)
    {
        $_events = array_slice(array_values($_events), -self::$MAX_EVENTS_BY_PROCESS, self::$MAX_EVENTS_BY_PROCESS);
        $find = [];
        $events = array_values($_events);
        $now = strtotime(DateFormat::NOW) + 300;
        foreach ($events as $key => $event) {
            if ($event[Common::DATETIME] > $now) {
                unset($events[$key]);
                continue;
            }
            if ($event[Common::NAME] == 'eqLogic::update') {
                $id = 'eqLogic::update::' . $event['option']['eqLogic_id'];
            } elseif ($event[Common::NAME] == 'cmd::update') {
                $id = 'cmd::update::' . $event['option']['cmd_id'];
            } elseif ($event[Common::NAME] == 'scenario::update') {
                $id = 'scenario::update::' . $event['option']['scenario_id'];
            } elseif ($event[Common::NAME] == 'jeeObject::summary::update') {
                $id = 'jeeObject::summary::update::' . $event['option']['object_id'];
                if (is_array($event['option']['keys']) && count($event['option']['keys']) > 0) {
                    foreach ($event['option']['keys'] as $key2 => $value) {
                        $id .= $key2;
                    }
                }
            } else {
                continue;
            }
            if (isset($find[$id])) {
                if ($find[$id][Common::DATETIME] > $event[Common::DATETIME]) {
                    unset($events[$key]);
                    continue;
                } else {
                    unset($events[$find[$id]['key']]);
                }
            }
            $find[$id] = [Common::DATETIME => $event[Common::DATETIME], 'key' => $key];
        }
        return array_values($events);
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
        return ($eventA[Common::DATETIME] - $eventB[Common::DATETIME]);
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
        $result = self::filterEvent(self::changesSince($_datetime), $_filter);
        if ($_longPolling === null || count($result[Common::RESULT]) > 0) {
            return $result;
        }
        $waitTime = ConfigManager::byKey('event::waitPollingTime');
        $i = 0;
        $maxCycle = $_longPolling / $waitTime;
        while (count($result[Common::RESULT]) == 0 && $i < $maxCycle) {
            if ($waitTime < 1) {
                usleep(1000000 * $waitTime);
            } else {
                sleep(round($waitTime));
            }
            sleep(1);
            $result = self::filterEvent(self::changesSince($_datetime), $_filter);
            $i++;
        }
        $result[Common::RESULT] = self::cleanEvent($result[Common::RESULT]);
        return $result;
    }

    /**
     * Get events filtered by name
     *
     * @param array $_data
     * @param null $_filter
     * @return array Filtered events
     * @throws \Exception
     */
    private static function filterEvent($_data = [], $_filter = null)
    {
        if ($_filter == null) {
            return $_data;
        }
        $filters = ($_filter !== null) ? CacheManager::byKey($_filter . '::event')->getValue([]) : [];
        $result = [Common::DATETIME => $_data[Common::DATETIME], Common::RESULT => []];
        foreach ($_data[Common::RESULT] as $value) {
            if ($_filter !== null && isset($_filter::$_listenEvents) && !in_array($value[Common::NAME], $_filter::$_listenEvents)) {
                continue;
            }
            if (count($filters) != 0 && $value[Common::NAME] == 'cmd::update' && !in_array($value['option']['cmd_id'], $filters)) {
                continue;
            }
            $result[Common::RESULT][] = $value;
        }
        return $result;
    }

    /**
     * Get events whose state has changed since a datetime
     *
     * @param mixed $_datetime Limit datetime
     *
     * @return array Associative array with all events
     * @throws \Exception
     */
    private static function changesSince($_datetime)
    {
        $now = getmicrotime();
        if ($_datetime > $now) {
            $_datetime = $now;
        }
        $result = [Common::DATETIME => $_datetime, Common::RESULT => []];
        $cache = CacheManager::byKey(CacheKey::EVENT);
        $events = json_decode($cache->getValue('[]'), true);
        if (!is_array($events)) {
            $events = [];
        }
        $values = array_reverse($events);
        if (count($values) > 0) {
            $result[Common::DATETIME] = $values[0][Common::DATETIME];
            foreach ($values as $value) {
                if ($value[Common::DATETIME] <= $_datetime) {
                    break;
                }
                $result[Common::RESULT][] = $value;
            }
        }
        $result[Common::RESULT] = array_reverse($result[Common::RESULT]);
        return $result;
    }
}