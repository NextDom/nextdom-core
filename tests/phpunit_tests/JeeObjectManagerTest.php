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

use NextDom\Managers\JeeObjectManager;

require_once(__DIR__ . '/../../src/core.php');

class JeeObjectManagerTest extends PHPUnit_Framework_TestCase
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

    public function testById() {
        $jeeObject = JeeObjectManager::byId(1);
        $this->assertEquals(1, $jeeObject->getId());
        $this->assertEquals('My Room', $jeeObject->getName());
    }

    public function testByIdBadId() {
        $jeeObject = JeeObjectManager::byId(69);
        $this->assertFalse($jeeObject);
    }

    public function testByName() {
        $jeeObject = JeeObjectManager::byName('My Room');
        $this->assertEquals(1, $jeeObject->getId());
    }

    public function testByNameBadName() {
        $jeeObject = JeeObjectManager::byName('An impossible name');
        $this->assertFalse($jeeObject);
    }

    public function testAll() {
        $jeeObjects = JeeObjectManager::all();
    }
}