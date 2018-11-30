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
     * Add event in cache
     *
     * @param string $eventName
     * @param array $options
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
            $value[] = array('datetime' => getmicrotime(), 'name' => $eventName, 'option' => $options);
            CacheManager::set('event', json_encode(self::cleanEvent($value)));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Add multiple events in cache
     *
     * @param string $eventName
     * @param array $values
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
                $value[] = array('datetime' => getmicrotime(), 'name' => $eventName, 'option' => $option);
            }
            CacheManager::set('event', json_encode(self::cleanEvent(array_merge($value_src, $value))));
            flock($fd, LOCK_UN);
        }
    }

    /**
     * Get the last MAX_EVENTS_BY_PROCESS events
     * @param $events
     * @return array
     */
    private static function cleanEvent($events)
    {
		$events = array_slice(array_values($events), -self::$MAX_EVENTS_BY_PROCESS, self::$MAX_EVENTS_BY_PROCESS);
		$find = array();
		foreach (array_values($events) as $key => $event) {
			if ($event['name'] == 'eqLogic::update') {
				$id = $event['name'] . '::' . $event['option']['eqLogic_id'];
			} elseif ($event['name'] == 'cmd::update') {
				$id = $event['name'] . '::' . $event['option']['cmd_id'];
			} elseif ($event['name'] == 'scenario::update') {
				$id = $event['name'] . '::' . $event['option']['scenario_id'];
			} elseif ($event['name'] == 'jeeObject::summary::update') {
				$id = $event['name'] . '::' . $event['option']['object_id'];
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
     * @param mixed $datetime Event time
     * @param null $longPolling Wait for new events
     * @param null $filter Event filter
     * @return array
     */
    public static function changes($datetime, $longPolling = null, $filter = null)
    {
        $result = self::filterEvent(self::changesSince($datetime), $filter);
        if ($longPolling === null || count($result['result']) > 0) {
            return $result;
        }
        $waitTime = \config::byKey('event::waitPollingTime');
        $cycleCount = 0;
        $maxCycle = $longPolling / $waitTime;
        while (count($result['result']) == 0 && $cycleCount < $maxCycle) {
            if ($waitTime < 1) {
                usleep(1000000 * $waitTime);
            } else {
                sleep(round($waitTime));
            }
            // Dans ce cas, 2 fois le temps
            //sleep(1);
            // TODO : Déplacer après la boucle ?
            // Pourquoi ne pas attendre tout simplement le temps du longPolling
            // vu que le résultat est écrasé à chaque fois
            $result = self::filterEvent(self::changesSince($datetime), $filter);
            $cycleCount++;
        }
        return $result;
    }

    /**
     * Get events filtered by name
     *
     * @param array $eventsToFilter Filter events
     * @param array $filterName Filter name
     *
     * @return array Filtered events
     */
    private static function filterEvent($eventsToFilter = [], $filterName = null)
    {
        $result = [];
        if ($filterName == null) {
            return $eventsToFilter;
        }
        $filters = CacheManager::byKey($filterName . '::event')->getValue([]);
        if (isset($eventsToFilter['datetime'])) {
            $result = array('datetime' => $eventsToFilter['datetime'], 'result' => []);
        }
        foreach ($eventsToFilter['result'] as $value) {
            /* TODO $_listEvents n'a jamais existé
            if (isset($filter::$_listenEvents) && !in_array($value['name'], $filter::$_listenEvents)) {
                continue;
            }
             */
            if (count($filters) != 0 && $value['name'] == 'cmd::update' && !in_array($value['option']['cmd_id'], $filters)) {
                continue;
            }
            $result['result'][] = $value;
        }
        return $result;
    }

    /**
     * Get events whose state has changed since a datetime
     *
     * @param mixed $datetime Limit datetime
     *
     * @return array Associative array with all events
     */
    private static function changesSince($datetime)
    {
        $return = array('datetime' => $datetime, 'result' => []);
        $cache = CacheManager::byKey('event');
        $events = json_decode($cache->getValue('[]'), true);
        if (!is_array($events)) {
            $events = [];
        }
        // Reverse order and break when datetime is reach
        $values = array_reverse($events);
        if (count($values) > 0) {
            $return['datetime'] = $values[0]['datetime'];
            foreach ($values as $value) {
                if ($value['datetime'] <= $datetime) {
                    break;
                }
                $return['result'][] = $value;
            }
        }
        $return['result'] = array_reverse($return['result']);
        return $return;
    }

    /**
     * Get event cache file object
     *
     * @return bool|null|resource
     */
    private static function getEventLockFile()
    {
        if (self::$eventLockFile === null) {
            self::$eventLockFile = fopen(NextDomHelper::getTmpFolder() . '/event_cache_lock', 'w');
            chmod(NextDomHelper::getTmpFolder() . '/event_cache_lock', 0666);
        }
        return self::$eventLockFile;
    }
}