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

use NextDom\Helpers\Utils;

// TODO: Utiliser la vraie classe
class UserMock {
    public $isConnectedResult = true;
    public $getProfilsResult = '';

    public function is_connected() {
        return $this->isConnectedResult;
    }

    public function getProfils() {
        return $this->getProfilsResult;
    }
}

class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testInitWithoutDataWithoutDefault()
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $toTest = Utils::init('Name');
        $this->assertEquals('', $toTest);
    }

    public function testInitWithoutDataWithDefault()
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('', $toTest);
    }

    public function testInitWithDataGetOnly()
    {
        $_GET = ['Name' => 'Result'];
        $_POST = [];
        $_REQUEST = [];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataPostOnly()
    {
        $_GET = [];
        $_POST = ['Name' => 'Result'];
        $_REQUEST = [];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataRequestOnly()
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = ['Name' => 'Result'];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataGetPost()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = [];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('GET', $toTest);
    }

    public function testInitWithDataGetRequest()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = [];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('GET', $toTest);
    }

    public function testInitWithDataPostRequest()
    {
        $_GET = [];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('POST', $toTest);
    }

    public function testInitWithDataGetPostRequest()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', '');
        $this->assertEquals('GET', $toTest);
    }

    public function testSendVarToJsSimpleVar()
    {
        ob_start();
        Utils::sendVarToJs('test', 'value');
        $result = ob_get_clean();
        $this->assertEquals("<script>var test = \"value\";</script>\n", $result);
    }

    public function testSendVarToJsAssociativeArray()
    {
        ob_start();
        Utils::sendVarToJs('test', array('foo' => 1, 'bar' => 'final'));
        $result = ob_get_clean();
        $this->assertEquals("<script>var test = jQuery.parseJSON(\"{\\\"foo\\\":1,\\\"bar\\\":\\\"final\\\"}\");</script>\n", $result);
    }

    public function testSendVarsToJS()
    {
        $tests = array(
            'test' => 'value',
            'anArray' => array('foo' => 1, 'bar' => 'final')
        );
        ob_start();
        Utils::sendVarsToJS($tests);
        $result = ob_get_clean();
        $this->assertEquals(
            "<script>\n".
            "var test = \"value\";\n".
            "var anArray = jQuery.parseJSON(\"{\\\"foo\\\":1,\\\"bar\\\":\\\"final\\\"}\");\n".
            "</script>\n", $result);
    }

    public function testRedirectJS()
    {
        ob_start();
        Utils::redirect('http://www.nextdom.org', 'JS');
        $result = ob_get_clean();
        $this->assertEquals('<script type="text/javascript">window.location.href="http://www.nextdom.org"</script>', $result);
    }

    public function testIsConnectWithoutRightsWithoutData()
    {
        $result = Utils::isConnect();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::']);
    }

    public function testIsConnectWithoutRightsWithEmptySession()
    {
        $_SESSION = [];
        $result = Utils::isConnect();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::']);
    }

    public function testIsConnectWithoutRightsWithSessionUserEmpty()
    {
        $_SESSION = [];
        $_SESSION['user'] = null;
        $result = Utils::isConnect();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::']);
    }

    public function testIsConnectWithoutRightsWithSessionUserConnected()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->isConnectedResult = true;
        $result = Utils::isConnect();
        $this->assertTrue($result);
        $this->assertTrue($GLOBALS['isConnect::']);
    }

    public function testIsConnectWithoutRightsWithSessionUserDisconnected()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->isConnectedResult = false;
        $result = Utils::isConnect();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::']);
    }

    public function testIsConnectWithRightsWithoutData()
    {
        $result = Utils::isConnect('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::admin']);
    }

    public function testIsConnectWithRightsWithEmptySession()
    {
        $_SESSION = [];
        $result = Utils::isConnect('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::admin']);
    }

    public function testIsConnectWithRightsWithSessionUserEmpty()
    {
        $_SESSION = [];
        $_SESSION['user'] = null;
        $result = Utils::isConnect('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::admin']);
    }

    public function testIsConnectWithRightsWithSessionUserConnectedNoRights()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->isConnectedResult = true;
        $result = Utils::isConnect('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::admin']);
    }

    public function testIsConnectWithRightsWithSessionUserConnectedWithRights()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->isConnectedResult = true;
        $_SESSION['user']->getProfilsResult = 'admin';
        $result = Utils::isConnect('admin');
        $this->assertTrue($result);
        $this->assertTrue($GLOBALS['isConnect::admin']);
    }

    public function testIsConnectWithRightsWithSessionUserDisconnected()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->isConnectedResult = false;
        $result = Utils::isConnect('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['isConnect::admin']);
    }

    public function testEvaluateWithObject() {
        $result = Utils::transformExpressionForEvaluation('#[Buanderie][Lumiere buanderie][Etat]# == 0');
        $this->assertEquals('#[Buanderie][Lumiere buanderie][Etat]#==0', $result);

    }

    public function testEvaluateSimpleWithoutSpace() {
        $result = Utils::transformExpressionForEvaluation('#[Buanderie][Lumiere buanderie][Etat]#==0');
        $this->assertEquals('#[Buanderie][Lumiere buanderie][Etat]#==0', $result);
    }

    public function testEvaluateSimpleWithOneSpace() {
        $result = Utils::transformExpressionForEvaluation('#[Buanderie][Lumiere buanderie][Etat]# ==0');
        $this->assertEquals('#[Buanderie][Lumiere buanderie][Etat]#==0', $result);
        $result = Utils::transformExpressionForEvaluation('#[Buanderie][Lumiere buanderie][Etat]#== 0');
        $this->assertEquals('#[Buanderie][Lumiere buanderie][Etat]#==0', $result);
    }

    public function testEvaluateSimpleWithNextDomVar() {
        $result = Utils::transformExpressionForEvaluation('#time# >= 2300 OR #time# < 0800');
        $this->assertEquals('#time#>=2300||#time#<0800', $result);
    }

    public function testEvaluateSimpleWithOneEqual() {
        $result = Utils::transformExpressionForEvaluation('#[Buanderie][Lumiere buanderie][Etat]# = 0');
        $this->assertEquals('#[Buanderie][Lumiere buanderie][Etat]#==0', $result);
    }

    public function testEvaluateSimpleWithFunc() {
        $result = Utils::transformExpressionForEvaluation('variable(commut_switch_bathroom) == 0');
        $this->assertEquals('variable(commut_switch_bathroom)==0', $result);
    }

    public function testEvaluateComplex() {
        $result = Utils::transformExpressionForEvaluation('#[Escaliers RDC][Detecteur RDC][Présence]# == 1 OR #[Escaliers 1er][Détecteur de mouvement 1er][Présence]# == 1');
        $this->assertEquals('#[Escaliers RDC][Detecteur RDC][Présence]#==1||#[Escaliers 1er][Détecteur de mouvement 1er][Présence]#==1', $result);

    }

    public function testEvaluateComplexOr() {
        $result = Utils::transformExpressionForEvaluation('"Test ou piege" OU "TEST ET PIEGE"');
        $this->assertEquals('"Test ou piege"||"TEST ET PIEGE"', $result);

    }

    public function testEvaluateComplexAnd() {
        $result = Utils::transformExpressionForEvaluation('"Test ou piege" ET "TEST ET PIEGE"');
        $this->assertEquals('"Test ou piege"&&"TEST ET PIEGE"', $result);

    }

    public function testEvaluateNegation() {
        $result = Utils::transformExpressionForEvaluation('variable(commut_switch_bathroom) !== 0');
        $this->assertEquals('variable(commut_switch_bathroom)!=0', $result);
    }

    public function testEvaluateParenthesis() {
        $result = Utils::transformExpressionForEvaluation('variable(PhraseQuestion) == "NON" OU (#ObjetTest# != "Push" ET #[Organisation][Mode Notifications][Mode]# != "Tous")');
        $this->assertEquals('variable(PhraseQuestion)=="NON"||(#ObjetTest#!="Push"&&#[Organisation][Mode Notifications][Mode]#!="Tous")', $result);
    }

    public function testEvaluateInParenthesis() {
        $result = Utils::transformExpressionForEvaluation('variable(scenario(plouf + PhraseQuestion)) == "NON"');
        $this->assertEquals('variable(scenario(plouf+PhraseQuestion))=="NON"', $result);
    }

    public function testEvaluateDot() {
        $result = Utils::transformExpressionForEvaluation('1.23 == 12.3');
        $this->assertEquals('1.23==12.3', $result);
    }

    public function testEvaluateOperator() {
        $result = Utils::transformExpressionForEvaluation('1*2 + 3-#Test# == 12/3');
        $this->assertEquals('1*2+3-#Test#==12/3', $result);
    }

    public function testEvaluateAndSymbols() {
        $result = Utils::transformExpressionForEvaluation('133.5 > 50 && 1 == 0');
        $this->assertEquals('133.5>50&&1==0', $result);
    }

    public function testEvaluateOrSymbols() {
        $result = Utils::transformExpressionForEvaluation('133.5 > 50 || 1 == 0');
        $this->assertEquals('133.5>50||1==0', $result);
    }
}
