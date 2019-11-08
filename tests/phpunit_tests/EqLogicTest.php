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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\EqLogicManager;
use NextDom\Model\Entity\EqLogic;

require_once(__DIR__ . '/../../src/core.php');

class EqLogicTest extends PHPUnit_Framework_TestCase
{
    public $testEqLogicId = null;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
        DBHelper::Prepare('DELETE FROM ' . EqLogicManager::DB_CLASS_NAME. ' WHERE id > 4', []);
    }

    public function setUp()
    {
        $eqLogic = new EqLogic();
        $eqLogic->setName('Run test eqLogic');
        $eqLogic->setGenericType('');
        $eqLogic->setLogicalId('run-test-eqlogic');
        $eqLogic->setEqType_name('plugin4tests');
        $eqLogic->setConfiguration('createtime', new \DateTime());
        $eqLogic->setIsVisible(1);
        $eqLogic->setIsEnable(1);
        $eqLogic->setTimeout(300);
        $eqLogic->setCategory('light', 1);
        $eqLogic->setDisplay('showObjectNameOnview', 1);
        $eqLogic->setOrder(72);
        $eqLogic->setComment('Just an eqLogic for test');
        $eqLogic->setTags('TAG');
        $eqLogic->setObject_id(1);
        $eqLogic->save();
        $this->testEqLogicId = $eqLogic->getId();
        $cmd = new Cmd();
        $cmd->setId(69);
        $cmd->setName('Test cmd');
        $cmd->setEqType('plugin4tests');
        $cmd->setEqLogic($eqLogic);
        $cmd->setType('info');
        $cmd->setSubType('binary');
    }

    public function tearDown()
    {
        DBHelper::Prepare('DELETE FROM ' . EqLogicManager::DB_CLASS_NAME. ' WHERE id > 4', []);
    }

    public function testGettersAndSetters()
    {
        // Add in db
        $createDate = new \DateTime();
        $eqLogic = new EqLogic();
        $eqLogic->setName('GetSet eqLogic');
        $eqLogic->setGenericType('');
        $eqLogic->setLogicalId('getset-test-eqlogic');
        $eqLogic->setEqType_name('plugin4tests');
        $eqLogic->setConfiguration('createtime', $createDate);
        $eqLogic->setIsVisible(1);
        $eqLogic->setIsEnable(1);
        $eqLogic->setTimeout(300);
        $eqLogic->setCategory('light', 1);
        $eqLogic->setDisplay('showObjectNameOnview', 1);
        $eqLogic->setOrder(72);
        $eqLogic->setComment('Just an eqLogic for test');
        $eqLogic->setTags('TAG');
        $eqLogic->setObject_id(1);
        $this->assertTrue($eqLogic->getChanged());
        $eqLogic->save();
        $eqLogicId = $eqLogic->getId();
        $this->assertFalse($eqLogic->getChanged());
        // Test added eqLogic
        $savedEqLogic = EqLogicManager::byId($eqLogicId);
        $this->assertEquals($eqLogicId, $savedEqLogic->getId());
        $this->assertEquals('plugin4tests', get_class($savedEqLogic));
        $this->assertEquals('GetSet eqLogic', $savedEqLogic->getName());
        $this->assertEquals('', $savedEqLogic->getGenericType());
        $this->assertEquals('getset-test-eqlogic', $savedEqLogic->getLogicalId());
        $this->assertEquals('plugin4tests', $savedEqLogic->getEqType_name());
        $this->assertContains($createDate->format('Y-m-d'), $savedEqLogic->getConfiguration('createtime'));
        $this->assertEquals(1, $savedEqLogic->getIsVisible());
        $this->assertEquals(1, $savedEqLogic->getIsEnable());
        $this->assertEquals(300, $savedEqLogic->getTimeout());
        $this->assertEquals(1, $savedEqLogic->getCategory('light'));
        $this->assertEquals(1, $savedEqLogic->getDisplay('showObjectNameOnview'));
        $this->assertEquals(72, $savedEqLogic->getOrder());
        $this->assertEquals('Just an eqLogic for test', $savedEqLogic->getComment());
        $this->assertEquals('TAG', $savedEqLogic->getTags());
        $this->assertEquals(1, $savedEqLogic->getObject_id());
        $this->assertFalse($eqLogic->getChanged());
    }

    public function testGetPrimaryCategoryWithOne() {
        $eqLogic = EqLogicManager::byId(1);
        $this->assertEquals('security', $eqLogic->getPrimaryCategory());
    }

    public function testGetPrimaryCategoryWithPriority() {
        $eqLogic = EqLogicManager::byId(3);
        $this->assertEquals('energy', $eqLogic->getPrimaryCategory());
    }

    public function testGetCategoryWithoutArgs() {
        $eqLogic = EqLogicManager::byId(1);
        $result = $eqLogic->getCategory();
        $this->assertCount(7, $result);
        $this->assertEquals(1, $result['security']);
    }

    public function testGetCategoryWithDefaultUseless() {
        $eqLogic = EqLogicManager::byId(2);
        $this->assertEquals(1, $eqLogic->getCategory('light', 0));
    }

    public function testGetCategoryWithDefaultUsefull() {
        $eqLogic = EqLogicManager::byId(4);
        $this->assertEquals(1, $eqLogic->getCategory('energy', 1));
    }
}