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

use NextDom\Rest\CmdRest;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../../../src/core.php');

class CmdRestTest extends PHPUnit_Framework_TestCase
{
    public function testGetByEqLogicGoodId()
    {
        $result = CmdRest::getByEqLogic(1);
        $this->assertCount(2, $result);
        $this->assertEquals('Cmd 1', $result[0]['name']);
        $this->assertEquals('binary', $result[0]['subType']);
        $this->assertEquals('action', $result[1]['type']);
        $this->assertEquals('2', $result[1]['id']);
    }

    public function testGetByEqLogicBadId()
    {
        $result = CmdRest::getByEqLogic(82);
        $this->assertCount(0, $result);
    }

    public function testGetVisibleByEqLogicGoodId()
    {
        $result = CmdRest::getVisibleByEqLogic(1);
        $this->assertCount(1, $result);
        $this->assertEquals('Cmd 1', $result[0]['name']);
        $this->assertEquals('binary', $result[0]['subType']);
    }

    public function testGetVisibleByEqLogicBadId()
    {
        $result = CmdRest::getVisibleByEqLogic(92);
        $this->assertCount(0, $result);
    }

    public function testExecWhenCmdExists()
    {
        $result = CmdRest::exec(new Request(), 2);
        $this->assertTrue($result);
    }

    public function testExecWhenCmdNotExists()
    {
        $result = CmdRest::exec(new Request(), 92);
        $this->assertFalse($result);
    }
}
