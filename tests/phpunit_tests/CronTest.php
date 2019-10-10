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

use NextDom\Helpers\DBHelper;
use NextDom\Managers\CronManager;
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Cron;

require_once(__DIR__ . '/../../src/core.php');

class CronTest extends PHPUnit_Framework_TestCase
{
    /** @var Cron cron for test */
    private $preparedCron = null;
    private static $maxExecTimeCrontask = 0;

    public static function setUpBeforeClass()
    {
        self::$maxExecTimeCrontask = ConfigManager::byKey('maxExecTimeCrontask');
    }

    public static function tearDownAfterClass()
    {
        DBHelper::exec('DELETE FROM ' . CronManager::DB_CLASS_NAME. ' WHERE id > 3');
    }

    public function setUp()
    {
        $this->preparedCron = new Cron();
        $this->preparedCron->setClass('MyTest');
        $this->preparedCron->setFunction('MyFunc');
        $this->preparedCron->setSchedule('* * * * *');
    }

    public function tearDown()
    {
        DBHelper::exec('DELETE FROM ' . CronManager::DB_CLASS_NAME. ' WHERE id > 3');
    }

    public function testGettersAndSetters()
    {
        $cron = new Cron();
        $cron->setClass('MyClass');
        $cron->setFunction('myFunction');
        $cron->setSchedule('* * * * *');
        $cron->setEnable(1);
        $cron->setTimeout(30);
        $cron->setOnce(1);
        $cron->save();
        $savedCron = CronManager::byId($cron->getId());
        $this->assertEquals('MyClass::myFunction()', $savedCron->getName());
        $this->assertEquals('* * * * *', $savedCron->getSchedule());
        $this->assertEquals(1, $savedCron->getEnable());
        $this->assertTrue($this->preparedCron->isEnabled());
        $this->assertEquals(30, $savedCron->getTimeout());
        $this->assertEquals(1, $savedCron->getOnce());
    }

    public function testGetEnableWithoutValue() {
        $this->preparedCron->setEnable(null);
        $this->preparedCron->save();
        $this->assertEquals(0, $this->preparedCron->getEnable());
        $this->assertFalse($this->preparedCron->isEnabled());
    }

    public function testGetEnableWithDefaultValue() {
        $this->preparedCron->setEnable(null);
        $this->preparedCron->save();
        $this->assertEquals(1, $this->preparedCron->getEnable(1));
        $this->assertFalse($this->preparedCron->isEnabled());
    }

    public function testGetTimeoutDefaultValue() {
        $this->preparedCron->save();
        $this->assertTrue(self::$maxExecTimeCrontask > 0);
        $this->assertEquals(self::$maxExecTimeCrontask, $this->preparedCron->getTimeout());
    }

    public function testGetOnceWithoutValue() {
        $this->preparedCron->setOnce(null);
        $this->preparedCron->save();
        $this->assertEquals(0, $this->preparedCron->getOnce());
    }

    public function testGetOnceWithDefaultValue() {
        $this->preparedCron->setEnable(null);
        $this->preparedCron->save();
        $this->assertEquals(1, $this->preparedCron->getOnce(1));
    }
}