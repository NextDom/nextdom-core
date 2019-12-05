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

use NextDom\Ajax\EqLogicAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;

require_once('BaseAjaxTest.php');

class EqLogicAjaxTest extends BaseAjaxTest
{
    /** @var EqLogicAjax */
    private $eqLogicAjax = null;

    public function setUp()
    {
        $this->eqLogicAjax = new EqLogicAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
    }

    public function testGetEqLogicObject()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['object_id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        $this->eqLogicAjax->getEqLogicObject();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('My Room', $jsonResult['result']['name']);
        $this->assertCount(1, $jsonResult['result']['eqLogic']);
        $this->assertContains('class="eqLogic', $jsonResult['result']['eqLogic'][0]['html']);
    }

    public function testGetEqLogicObjectNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->eqLogicAjax->getEqLogicObject();
    }

    public function testGetEqLogicObjectNoVersion()
    {
        $this->connectAdAdmin();
        $_GET['object_id'] = 1;
        $this->expectException(CoreException::class);
        $this->eqLogicAjax->getEqLogicObject();
    }

    public function testById()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = 1;
        ob_start();
        $this->eqLogicAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Test eqLogic', $jsonResult['result']['name']);
    }

    public function testByIdNoId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->eqLogicAjax->getEqLogicObject();
    }

    public function testToHtmlOne()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        $this->eqLogicAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(4, $jsonResult['result']);
        $this->assertContains('nextdom.cmd.update[\'1\']', $jsonResult['result']['html']);
    }

    public function testToHtmlOneWithoutVersion()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $this->expectException(CoreException::class);
        $this->eqLogicAjax->toHtml();
    }

    public function testToHtmlMultiple()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['ids'] = '{"1":{"version":"dashboard"},"2":{"version":"dashboard"}}';
        ob_start();
        $this->eqLogicAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result'][1]['type']);
        $this->assertContains('$(\'.cmd[data-cmd_id=1]', $jsonResult['result'][1]['html']);
    }

    public function testToHtmlMultipleWithoutVersion()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $this->expectException(CoreException::class);
        $_GET['ids'] = '{"1":"","2":""}';
        $this->eqLogicAjax->toHtml();
    }

    public function testToHtmlWithoutId()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->eqLogicAjax->toHtml();
    }

    public function testListByType()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['type'] = 'plugin4tests';
        ob_start();
        $this->eqLogicAjax->listByType();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(4, $jsonResult['result']);
        $this->assertEquals('A lamp', $jsonResult['result'][0]['name']);
    }

    public function testListByTypeBadType()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['type'] = 'impossible type';
        ob_start();
        $this->eqLogicAjax->listByType();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(0, $jsonResult['result']);
    }

    public function testListByTypeAndCmdType()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['type'] = 'plugin4tests';
        $_GET['typeCmd'] = 'info';
        ob_start();
        $this->eqLogicAjax->listByTypeAndCmdType();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(3, $jsonResult['result']);
        $this->assertCount(2, $jsonResult['result'][1]);
        $this->assertEquals('Test eqLogic', $jsonResult['result'][2]['eqLogic']['name']);
        $this->assertEquals('My Room', $jsonResult['result'][2]['object']['name']);
    }

    public function testListByTypeAndCmdTypeWithSubType()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['type'] = 'plugin4tests';
        $_GET['typeCmd'] = 'info';
        $_GET['subTypeCmd'] = 'numeric';
        ob_start();
        $this->eqLogicAjax->listByTypeAndCmdType();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(1, $jsonResult['result']);
        $this->assertCount(2, $jsonResult['result'][0]);
        $this->assertEquals('A lamp', $jsonResult['result'][0]['eqLogic']['name']);
        $this->assertEquals('Bathroom', $jsonResult['result'][0]['object']['name']);
    }
}