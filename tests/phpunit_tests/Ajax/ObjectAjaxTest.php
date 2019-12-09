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

use NextDom\Ajax\ObjectAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\JeeObjectManager;
use NextDom\Model\Entity\JeeObject;

require_once('BaseAjaxTest.php');

class ObjectAjaxTest extends BaseAjaxTest
{
    /** @var ObjectAjax */
    private $objectAjax = null;

    public function setUp()
    {
        $parentObject = JeeObjectManager::byName('Parent test object');
        if (is_object($parentObject)) {
            $parentObject->remove();
        }
        $childObject = JeeObjectManager::byName('Child test object');
        if (is_object($childObject)) {
            $childObject->remove();
        }
        $this->objectAjax = new ObjectAjax();
        $parentObject = new JeeObject();
        $parentObject->setName('Parent test object');
        $parentObject->save();
        $childObject = new JeeObject();
        $childObject->setName('Child test object');
        $childObject->setFather_id($parentObject->getId());
        $childObject->save();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
        $parentObject = JeeObjectManager::byName('Parent test object');
        if (is_object($parentObject)) {
            $parentObject->remove();
        }
        $childObject = JeeObjectManager::byName('Child test object');
        if (is_object($childObject)) {
            $childObject->remove();
        }
        $moreTestObject = JeeObjectManager::byName('More test object');
        if (is_object($moreTestObject)) {
            $moreTestObject->remove();
        }
    }

    public function testRemove()
    {
        $parentObject = JeeObjectManager::byName('Parent test object');
        $this->connectAdAdmin();
        $_GET['id'] = $parentObject->getId();
        ob_start();
        $this->objectAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertFalse(is_object(JeeObjectManager::byName('Parent test object')));
    }

    public function testRemoveNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->objectAjax->remove();
    }

    public function testById() {
        $this->connectAdAdmin();
        $_GET['id'] = 1;
        ob_start();
        $this->objectAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('My Room', $jsonResult['result']['name']);
    }

    public function testByIdNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->objectAjax->byId();
    }

    public function testAll() {
        $this->connectAdAdmin();
        ob_start();
        $this->objectAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('My Room', $jsonResult['result'][0]['name']);
        $this->assertEquals('Child test object', $jsonResult['result'][4]['name']);
    }

    public function testSave() {
        $this->connectAdAdmin();
        $_GET['object'] = '{"name":"More test object"}';
        ob_start();
        $this->objectAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $testObject = JeeObjectManager::byName('More test object');
        $this->assertTrue(is_object($testObject));
        $this->assertEquals('More test object', $testObject->getName());
    }

    public function testGetChild() {
        $parentObject = JeeObjectManager::byName('Parent test object');
        $this->connectAdAdmin();
        $_GET['id'] = $parentObject->getId();
        ob_start();
        $this->objectAjax->getChild();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Child test object', $jsonResult['result'][0]['name']);
        $this->assertCount(1, $jsonResult['result']);
    }

    public function testByGetChildNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->objectAjax->getChild();
    }

    public function testToHtml() {
        $this->connectAdAdmin();
        $_GET['id'] = 1;
        ob_start();
        $this->objectAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertArrayHasKey('objectHtml', $jsonResult['result']);
        $this->assertArrayHasKey('scenarios', $jsonResult['result']);
        $this->assertContains('class="eqLogic', $jsonResult['result']['objectHtml']);
        $this->assertEquals('Scenario with expressions', $jsonResult['result']['scenarios'][0]['name']);
    }

    public function testRemoveImage() {
        $parentObject = JeeObjectManager::byName('Parent test object');
        $parentObject->setImage('data', 'Sample data');
        $parentObject->setImage('sha512', 'Sample hash');
        $parentObject->save();
        $this->connectAdAdmin();
        $_GET['id'] = $parentObject->getId();
        ob_start();
        $this->objectAjax->removeImage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $parentObject = JeeObjectManager::byName('Parent test object');
        $this->assertEquals('', $parentObject->getImage('data'));
        $this->assertEquals('', $parentObject->getImage('sha512'));
    }

    public function testRemoveImageNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->objectAjax->removeImage();
    }
}