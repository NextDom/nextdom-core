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

use NextDom\Model\Entity\Cmd;
use NextDom\Managers\CmdManager;

require_once(__DIR__ . '/../../src/core.php');

class CmdManagerTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
    }

    public function testCastToPlugin4TestsCmd() {
        $srcCmd = new Cmd();
        $srcCmd->setId(69);
        $srcCmd->setName('Test cmd');
        $srcCmd->setEqType('plugin4tests');
        $targetCmd = CmdManager::cast($srcCmd);
        $this->assertEquals(69, $targetCmd->getId());
        $this->assertEquals('Test cmd', $targetCmd->getName());
        $this->assertEquals('plugin4testsCmd', get_class($targetCmd));
    }

    public function testCastToUnknownPlugin() {
        $srcCmd = new Cmd();
        $srcCmd->setId(69);
        $srcCmd->setName('Test cmd');
        $srcCmd->setEqType('ThisPluginDoesntExists');
        $targetCmd = CmdManager::cast($srcCmd);
        $this->assertEquals(69, $targetCmd->getId());
        $this->assertEquals('Test cmd', $targetCmd->getName());
        $this->assertEquals(Cmd::class, get_class($targetCmd));
    }

    public function testByIdSimple()
    {
        $cmd = CmdManager::byId(1);
        $this->assertEquals(1, $cmd->getId());
        $this->assertEquals('Cmd 1', $cmd->getName());
        $this->assertEquals('plugin4testsCmd', get_class($cmd));
    }

    public function testByIdWhatNotExists()
    {
        $cmd = CmdManager::byId(42);
        $this->assertFalse($cmd);
    }

    public function testByIdsWithOne()
    {
        $cmds = CmdManager::byIds([1]);
        $this->assertEquals(1, count($cmds));
        $this->assertEquals(1, $cmds[0]->getId());
    }

    public function testByIdsWithMultiple()
    {
        $cmds = CmdManager::byIds([1, 2]);
        $this->assertEquals(2, count($cmds));
        $this->assertEquals(1, $cmds[0]->getId());
        $this->assertEquals(2, $cmds[1]->getId());
    }

    public function testByIdsWithOneBad()
    {
        $cmds = CmdManager::byIds([42, 2]);
        $this->assertEquals(1, count($cmds));
        $this->assertEquals(2, $cmds[0]->getId());
    }

    public function testAll() {
        $cmds = CmdManager::all();
        $this->assertEquals(4, count($cmds));
        $this->assertEquals(1, $cmds[0]->getId());
        $this->assertEquals('Cmd 2', $cmds[1]->getName());
        $this->assertEquals('plugin4testsCmd', get_class($cmds[0]));
    }

    public function testbyEqLogicIdSimple() {
        $cmds = CmdManager::byEqLogicId(1);
        $this->assertEquals(2, count($cmds));
        $this->assertEquals(1, $cmds[0]->getId());
        $this->assertEquals('Cmd 2', $cmds[1]->getName());
        $this->assertEquals('plugin4testsCmd', get_class($cmds[0]));
    }

    public function testbyEqLogicIdByType() {
        $cmds = CmdManager::byEqLogicId(1, 'action');
        $this->assertEquals(1, count($cmds));
        $this->assertEquals(2, $cmds[0]->getId());
        $this->assertEquals('Cmd 2', $cmds[0]->getName());
        $this->assertEquals('plugin4testsCmd', get_class($cmds[0]));
    }

    public function testbyEqLogicIdOnlyVisible() {
        $cmds = CmdManager::byEqLogicId(1, null, 1);
        $this->assertEquals(1, count($cmds));
        $this->assertEquals(1, $cmds[0]->getId());
        $this->assertEquals('Cmd 1', $cmds[0]->getName());
        $this->assertEquals('plugin4testsCmd', get_class($cmds[0]));
    }

    public function testCmdToHumanReadable() {
        $humanReadable = CmdManager::cmdToHumanReadable('#1#');
        $this->assertEquals('#[My Room][Test eqLogic][Cmd 1]#', $humanReadable);
    }

    public function testHumanReadableToCmd() {
        $cmd = CmdManager::humanReadableToCmd('#[My Room][Test eqLogic][Cmd 1]#');
        $this->assertEquals('#1#', $cmd);
    }
}