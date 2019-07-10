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

use NextDom\Model\Entity\EqLogic;
use NextDom\Managers\EqLogicManager;

require_once(__DIR__ . '/../../src/core.php');

class EqLogicManagerTest extends PHPUnit_Framework_TestCase
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

    public function testCastToPlugin4TestsEqLogic() {
        $srcEqLogic = new EqLogic();
        $srcEqLogic->setId(69);
        $srcEqLogic->setName('Test eqLogic');
        $srcEqLogic->setEqType_name('plugin4tests');
        $targetEqLogic = EqLogicManager::cast($srcEqLogic);
        $this->assertEquals(69, $targetEqLogic->getId());
        $this->assertEquals('Test eqLogic', $targetEqLogic->getName());
        $this->assertEquals('plugin4tests', get_class($targetEqLogic));
    }

    public function testCastToUnknownPlugin() {
        $srcEqLogic = new EqLogic();
        $srcEqLogic->setId(69);
        $srcEqLogic->setName('Test eqLogic');
        $srcEqLogic->setEqType_name('ThisPluginDoesntExists');
        $targetEqLogic = EqLogicManager::cast($srcEqLogic);
        $this->assertEquals(69, $targetEqLogic->getId());
        $this->assertEquals('Test eqLogic', $targetEqLogic->getName());
        $this->assertEquals(EqLogic::class, get_class($targetEqLogic));
    }

    public function testByIdSimple()
    {
        $eqLogic = EqLogicManager::byId(1);
        $this->assertEquals(1, $eqLogic->getId());
        $this->assertEquals('Test eqLogic', $eqLogic->getName());
        $this->assertEquals('plugin4tests', get_class($eqLogic));
    }

    public function testByIdWhatNotExists()
    {
        $eqLogic = EqLogicManager::byId(49);
        $this->assertFalse($eqLogic);
    }

    public function testAll() {
        $eqLogics = EqLogicManager::all();
        $this->assertEquals(4, count($eqLogics));
        $this->assertEquals('plugin4tests', get_class($eqLogics[0]));
    }

    public function testAllOnlyEnabled() {
        $eqLogics = EqLogicManager::all(true);
        $this->assertEquals(3, count($eqLogics));
        $this->assertEquals('plugin4tests', get_class($eqLogics[0]));
    }
}