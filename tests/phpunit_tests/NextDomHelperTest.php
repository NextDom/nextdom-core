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

define('NEXTDOM_ROOT', '/tmp/tests');
define('NEXTDOM_DATA', NEXTDOM_ROOT . '/data');

use NextDom\Helpers\NextDomHelper;

define('REMOVE_HISTORY_PATH_FILE', NEXTDOM_DATA . '/data/remove_history.json');

class NextDomHelperTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        system('rm -fr ' . NEXTDOM_DATA . '/data');
        mkdir(NEXTDOM_DATA . '/data', 0777, true);
    }

    public function setUp()
    {
        if (file_exists(REMOVE_HISTORY_PATH_FILE)) {
            unlink(REMOVE_HISTORY_PATH_FILE);
        }
        touch(REMOVE_HISTORY_PATH_FILE);
    }

    public function testAddRemoveHistoryAddSimpleLine()
    {
        NextDomHelper::addRemoveHistory('Test');
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('["Test"]', $fileContent);
    }

    public function testAddRemoveHistoryAddArrayLine()
    {
        NextDomHelper::addRemoveHistory(['a' => 'b', 'c' => 1]);
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('[{"a":"b","c":1}]', $fileContent);
    }

    public function testAddRemoveHistoryAddMultipleLines()
    {
        NextDomHelper::addRemoveHistory('Line 1');
        NextDomHelper::addRemoveHistory('Line 2');
        NextDomHelper::addRemoveHistory('Line 3');
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('["Line 1","Line 2","Line 3"]', $fileContent);
    }

    public function testAddRemoveHistoryAddOutOfLimits()
    {
        for ($i = 0; $i < 220; ++$i) {
            NextDomHelper::addRemoveHistory("Line $i");
        }
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals(0, strpos($fileContent, '["Line 20","Line 21"'));
    }
}
