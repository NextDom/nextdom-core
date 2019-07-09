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

use NextDom\Rest\EqLogicRest;

require_once(__DIR__ . '/../../../src/core.php');

class EqLogicRestTest extends PHPUnit_Framework_TestCase
{
    public function testGetAll()
    {
        $result = EqLogicRest::getAll();
        $this->assertCount(4, $result);

        $this->assertEquals('A lamp', $result[0]['name']);
        $this->assertEquals('plugin4tests', $result[0]['type']);
        $this->assertEquals('Second eqLogic', $result[1]['name']);
        $this->assertEquals('1', $result[1]['objectId']);
        $this->assertEquals('Test eqLogic', $result[2]['name']);
        $this->assertEquals('Third eqLogic', $result[3]['name']);
    }

    public function testGetByRoomWhatExists()
    {
        $result = EqLogicRest::getByRoom(1);
        $this->assertCount(2, $result);
        $this->assertEquals('Test eqLogic', $result[0]['name']);
        $this->assertEquals('Third eqLogic', $result[1]['name']);
    }

    public function testGetByRoomWhatNotExists()
    {
        $result = EqLogicRest::getByRoom(32);
        $this->assertCount(0, $result);
    }

    public function testGetVisibleByRoomWhatExists()
    {
        $result = EqLogicRest::getVisibleByRoom(1);
        $this->assertCount(1, $result);
        $this->assertEquals('Test eqLogic', $result[0]['name']);
    }

    public function testGetVisibleByRoomWhatNotExists()
    {
        $result = EqLogicRest::getVisibleByRoom(32);
        $this->assertCount(0, $result);
    }
}
