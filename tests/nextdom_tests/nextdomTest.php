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

class nextdomTest extends PHPUnit_Framework_TestCase
{
    private $timelineFile;

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . '/../../core/class/nextdom.class.php');
        DB::Prepare("CREATE TABLE `config` (
                      `plugin` varchar(127) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'core',
                      `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                      `value` text COLLATE utf8_unicode_ci,
                      PRIMARY KEY (`key`,`plugin`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;", array());
    }

    public function setUp()
    {
        require_once(__DIR__ . '/../../core/class/nextdom.class.php');

        $this->timelineFile = __DIR__ . '/../../data/timeline.json';
        if (file_exists($this->timelineFile)) {
            unlink($this->timelineFile);
        }
    }

    public function tearDown()
    {
        if (file_exists($this->timelineFile)) {
            unlink($this->timelineFile);
        }
    }

    public static function tearDownAfterClass() {
        require_once(__DIR__ . '/../../core/class/nextdom.class.php');
        DB::Prepare("DROP TABLE `config`", array());
    }

    public function testAddTimeLineEventWithoutDestFile()
    {
        nextdom::addTimelineEvent(array('test' => 'value'));
        $fileContent = file_get_contents($this->timelineFile);
        $this->assertEquals("{\"test\":\"value\"}\n", $fileContent);
    }

    public function testAddTimeLineEventWithContentInDestFile()
    {
        file_put_contents($this->timelineFile, "{\"data\":\"value\"}\n");
        nextdom::addTimelineEvent(array('another' => 'value'));
        $fileContent = file_get_contents($this->timelineFile);
        $this->assertEquals("{\"data\":\"value\"}\n{\"another\":\"value\"}\n", $fileContent);
    }
/*
    public function testGetTimeLineEventWithoutFile()
    {
        $result = nextdom::getTimelineEvent();
        $this->assertCount(0, $result);
    }

    public function testGetTimeLineEventWithEmptyFile()
    {
        config::save('timeline::maxevent', 5);
        file_put_contents($this->timelineFile, '');
        $result = nextdom::getTimelineEvent();
        $this->assertCount(0, $result);
    }
*/
}
