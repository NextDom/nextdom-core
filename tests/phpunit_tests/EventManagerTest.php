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

use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\EventManager;

require_once(__DIR__ . '/../../src/core.php');

class EventManagerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        CacheManager::delete('event');
    }

    public function tearDown()
    {
        CacheManager::delete('event');
    }

    public function testAdd()
    {
        EventManager::add('test_event', ['some_data' => 'some_value']);
        $cachedEvents = CacheManager::byKey('event');
        $events = json_decode($cachedEvents->getValue(), true);
        $this->assertEquals(1, count($events));
        $this->assertEquals('test_event', $events[0]['name']);
    }

    public function testAdds()
    {
        EventManager::adds('test_event', [['some_data' => 'some_value1'], ['some_data' => 'some_value2'], ['some_data' => 'some_value3']]);
        $cachedEvents = CacheManager::byKey('event');
        $events = json_decode($cachedEvents->getValue(), true);
        $this->assertEquals(3, count($events));
        $this->assertEquals('test_event', $events[0]['name']);
        $this->assertEquals('some_value2', $events[1]['option']['some_data']);
        $this->assertEquals('some_value3', $events[2]['option']['some_data']);
    }

    public function testCleanEventsUnderLimit()
    {
        $eventsForTest = [];
        for ($i = 0; $i < 100; ++$i) {
            $eventsForTest[] = ['Test data' => 'DATA'];
        }
        EventManager::adds('samples', $eventsForTest);
        EventManager::adds('test_event', [['some_data' => 'some_value1'], ['some_data' => 'some_value2'], ['some_data' => 'some_value3']]);
        $cachedEvents = CacheManager::byKey('event');
        $events = json_decode($cachedEvents->getValue(), true);
        $this->assertEquals(103, count($events));
        $this->assertEquals('test_event', $events[100]['name']);
        $this->assertEquals('some_value2', $events[101]['option']['some_data']);
        $this->assertEquals('some_value3', $events[102]['option']['some_data']);
    }

    public function testCleanEventsUOverLimit()
    {
        $eventsForTest = [];
        for ($i = 0; $i < 300; ++$i) {
            $eventsForTest[] = ['Test data' => 'DATA'];
        }
        EventManager::adds('samples', $eventsForTest);
        EventManager::adds('test_event', [['some_data' => 'some_value1'], ['some_data' => 'some_value2'], ['some_data' => 'some_value3']]);
        $cachedEvents = CacheManager::byKey('event');
        $events = json_decode($cachedEvents->getValue(), true);
        $this->assertEquals(250, count($events));
        $this->assertEquals('test_event', $events[247]['name']);
        $this->assertEquals('some_value2', $events[248]['option']['some_data']);
        $this->assertEquals('some_value3', $events[249]['option']['some_data']);
    }

    public function testOrderEvent()
    {
        $eventA = [];
        $eventB = [];
        $eventA['datetime'] = Utils::getMicrotime();
        $eventB['datetime'] = Utils::getMicrotime() + 30;
        $eventA['name'] = 'Event A';
        $eventB['name'] = 'Event B';
        $eventA['option'] = [];
        $eventB['option'] = [];
        $result = EventManager::orderEvent($eventA, $eventB);
        $this->assertEquals(-30.0, round($result));
    }

    public function testChanges() {
        EventManager::add('first_event', ['some_data' => 'some_value']);
        sleep(2);
        $stepDatetime = Utils::getMicrotime();
        sleep(1);
        EventManager::add('second_event', ['some_data' => 'some_value']);
        $changes = EventManager::changes($stepDatetime);
        $this->assertCount(1, $changes['result']);
        $this->assertEquals('second_event', $changes['result'][0]['name']);
    }
}