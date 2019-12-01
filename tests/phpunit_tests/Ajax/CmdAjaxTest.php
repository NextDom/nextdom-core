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

use NextDom\Ajax\CmdAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Model\Entity\Cmd;

require_once('BaseAjaxTest.php');

class CmdAjaxTest extends BaseAjaxTest
{
    /** @var CmdAjax */
    private $cmdAjax = null;

    public function setUp()
    {
        $this->cmdAjax = new CmdAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
        DBHelper::exec('DELETE FROM cmd WHERE id > 4');
    }

    public function testToHtmlOnlyOne()
    {
        $_GET['id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        $this->cmdAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('data-cmd_id=\"1\"', $result);
    }

    public function testToHtmlMultiple()
    {
        $_GET['ids'] = '{"1": {"version": "dashboard"}, "2": {"version": "dashboard"}, "badid": "baddata", "3" : {"version": "dashboard"}}';
        ob_start();
        $this->cmdAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('data-cmd_id=\"2\"', $result);
        $this->assertCount(3, $jsonResult['result']);
        $this->assertEquals(1, $jsonResult['result']['1']['id']);
    }

    public function testToHtmlBadId()
    {
        $_GET['id'] = 'Bad id';
        $_GET['version'] = 'dashboard';
        $this->expectException(CoreException::class);
        $this->cmdAjax->toHtml();
    }

    public function testExecCmd()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = '2';
        ob_start();
        $this->cmdAjax->execCmd();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('', $jsonResult['result']);
    }

    public function testExecDisconnected()
    {
        $_GET['id'] = '2';
        $this->expectException(CoreException::class);
        $this->cmdAjax->execCmd();
    }

    public function testGetByObjectNameEqNameCmdName()
    {
        $_GET['object_name'] = 'My Room';
        $_GET['eqLogic_name'] = 'Test eqLogic';
        $_GET['cmd_name'] = 'Cmd 1';
        ob_start();
        $this->cmdAjax->getByObjectNameEqNameCmdName();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('1', $jsonResult['result']);
    }

    public function testGetByObjectNameEqNameCmdNameBadRoom()
    {
        $_GET['object_name'] = 'My Bad Room';
        $_GET['eqLogic_name'] = 'Test eqLogic';
        $_GET['cmd_name'] = 'Cmd 1';
        $this->expectException(CoreException::class);
        $this->cmdAjax->getByObjectNameEqNameCmdName();
    }

    public function testGetByObjectNameCmdName()
    {
        $_GET['object_name'] = 'My Room';
        $_GET['cmd_name'] = 'Cmd 1';
        ob_start();
        $this->cmdAjax->getByObjectNameCmdName();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result']['eqType']);
    }

    public function testGetByObjectNameCmdNameBadRoom()
    {
        $_GET['object_name'] = 'My Bad Room';
        $_GET['cmd_name'] = 'Cmd 1';
        $this->expectException(CoreException::class);
        $this->cmdAjax->getByObjectNameEqNameCmdName();
    }

    public function testById()
    {
        $_GET['id'] = '3';
        ob_start();
        $this->cmdAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result']['eqType']);
    }

    public function testByIdBadId()
    {
        $_GET['id'] = '20';
        $this->expectException(CoreException::class);
        $this->cmdAjax->byId();
    }

    public function testByHumanName()
    {
        $_GET['humanName'] = '#[My Room][Test eqLogic][Cmd 1]#';
        ob_start();
        $this->cmdAjax->byHumanName();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result']['eqType']);
    }

    public function testByHumanNameUnknown()
    {
        $_GET['humanName'] = '#[Bad Room][Bad eqLogic][Bad Cmd]#';
        $this->expectException(CoreException::class);
        $this->cmdAjax->byHumanName();
    }

    public function testGetHumanCmdName()
    {
        $_GET['id'] = '2';
        ob_start();
        $this->cmdAjax->getHumanCmdName();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('#[My Room][Test eqLogic][Cmd 2]#', $jsonResult['result']);
    }

    public function testUsedBy()
    {
        $this->connectAdAdmin();
        $_GET['id'] = '3';
        ob_start();
        $this->cmdAjax->usedBy();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('[My Room][Scenario for tests][Scenario with expressions]', $jsonResult['result']['scenario'][0]['humanName']);
    }

    public function testByEqLogic()
    {
        $_GET['eqLogic_id'] = '1';
        ob_start();
        $this->cmdAjax->byEqLogic();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Cmd 1', $jsonResult['result'][0]['name']);
        $this->assertEquals('action', $jsonResult['result'][1]['type']);
    }

    public function testGetCmd()
    {
        $_GET['id'] = '1';
        ob_start();
        $this->cmdAjax->getCmd();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Cmd 1', $jsonResult['result']['name']);
        $this->assertEquals('Test eqLogic', $jsonResult['result']['eqLogic_name']);
        $this->assertEquals('My Room', $jsonResult['result']['object_name']);
    }

    public function testSave()
    {
        $this->connectAdAdmin();

        $cmd4 = new Cmd();
        $cmd4->setName('Save test');
        $cmd4->setType('action');
        $cmd4->setSubType('other');
        $cmd4->setEqLogic_id(1);
        $cmd4->save();

        $_GET['cmd'] = json_encode(['id' => $cmd4->getId(), 'name' => 'Save test changed', 'type' => 'action', 'subType' => 'other', 'eqLogic_id' => 1]);
        ob_start();
        $this->cmdAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Save test changed', $jsonResult['result']['name']);
        $this->assertEquals('Save test changed', CmdManager::byId($cmd4->getId())->getName());
    }

    public function testMultiSave()
    {
        $this->connectAdAdmin();

        $cmd4 = new Cmd();
        $cmd4->setName('Save test');
        $cmd4->setType('action');
        $cmd4->setSubType('other');
        $cmd4->setEqLogic_id(1);
        $cmd4->save();

        $cmd5 = new Cmd();
        $cmd5->setName('Second save test');
        $cmd5->setType('info');
        $cmd5->setSubType('numeric');
        $cmd5->setEqLogic_id(1);
        $cmd5->save();

        $_GET['cmd'] = json_encode(
            [
                ['id' => $cmd4->getId(), 'name' => 'New name test', 'type' => 'action', 'subType' => 'other', 'eqLogic_id' => 1],
                ['id' => $cmd5->getId(), 'name' => 'Second save test', 'type' => 'info', 'subType' => 'binary', 'eqLogic_id' => 2]
            ]);
        ob_start();
        $this->cmdAjax->multiSave();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('New name test', CmdManager::byId($cmd4->getId())->getName());
        $this->assertEquals('binary', CmdManager::byId($cmd5->getId())->getSubType());
    }
}