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

use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;

require_once(__DIR__ . '/../../src/core.php');

class CacheManagerTest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
    }

    public function setUp(): void
    {

    }

    public function testStorageShortTime()
    {
        CacheManager::set('test short key', 'test value', 2);
        $this->assertEquals('test value', CacheManager::byKey('test short key')->getValue());
        sleep(3);
        $this->assertEquals('', CacheManager::byKey('test short key')->getValue());
    }

    public function testStorageLongTime()
    {
        CacheManager::set('test long key', 'test value', 20);
        $this->assertEquals('test value', CacheManager::byKey('test long key')->getValue());
        sleep(3);
        $this->assertEquals('test value', CacheManager::byKey('test long key')->getValue());
    }

    public function testExists()
    {
        CacheManager::set('test exists key', 'test value', 2);
        $this->assertTrue(CacheManager::exists('test exists key'));
    }

    public function testFlush()
    {
        CacheManager::set('test persisted key', 'test value', 30);
        $this->assertEquals('test value', CacheManager::byKey('test persisted key')->getValue());
        CacheManager::flush();
        $this->assertEquals('', CacheManager::byKey('test persisted key')->getValue());
    }

    public function testDelete()
    {
        CacheManager::set('test delete key', 'test value', 30);
        $this->assertEquals('test value', CacheManager::byKey('test delete key')->getValue());
        CacheManager::delete('test delete key');
        $this->assertEquals('', CacheManager::byKey('test delete key')->getValue());
    }

    public function testClean()
    {
        CacheManager::set('cmd1::lastCommunication', 'a value');
        CacheManager::set('cmd1::state', 'a state');
        CacheManager::set('cmd1::numberTryWithoutSuccess', 'number of try');
        CacheManager::set('cmd666', 'a cmd');
        CacheManager::set('widgetHtml666Something', 'widget html');
        CacheManager::set('camera666something', 'camera');
        CacheManager::set('scenarioHtmlSomething666', 'scenario');
        CacheManager::set('widgetHtmlSomething666', 'widget html');
        CacheManager::set('widgetHtmldashboard', 'widgetHtml dashboard');
        CacheManager::set('widgetHtmldashboard666', 'widgetHtml dashboard');
        CacheManager::set('widgetHtmldplan666', 'widgetHtml dplan');
        CacheManager::set('widgetHtml666', 'widgetHtml');
        CacheManager::set('dependancyBadPluginName', 'dependancy');
        CacheManager::clean();
        $this->assertEquals('', CacheManager::byKey('cmd1::lastCommunication')->getValue());
        $this->assertEquals('', CacheManager::byKey('cmd1::state')->getValue());
        $this->assertEquals('', CacheManager::byKey('cmd1::numberTryWithoutSuccess')->getValue());
        $this->assertEquals('', CacheManager::byKey('cmd666')->getValue());
        $this->assertEquals('', CacheManager::byKey('widgetHtml666Something')->getValue());
        $this->assertEquals('', CacheManager::byKey('camera666something')->getValue());
        $this->assertEquals('', CacheManager::byKey('scenario666something')->getValue());
        $this->assertEquals('widgetHtml dashboard', CacheManager::byKey('widgetHtmldashboard')->getValue());
        $this->assertEquals('', CacheManager::byKey('widgetHtmldashboard666')->getValue());
        $this->assertEquals('', CacheManager::byKey('widgetHtmldplan666')->getValue());
        $this->assertEquals('', CacheManager::byKey('widgetHtml666')->getValue());
        $this->assertEquals('', CacheManager::byKey('widgetHtml666Something')->getValue());
        $this->assertEquals('', CacheManager::byKey('dependancyBadPluginName')->getValue());
    }
}