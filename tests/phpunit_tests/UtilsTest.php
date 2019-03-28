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

class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testSendVarToJsInt()
    {
        ob_start();
        Utils::sendVarToJS('varName', 1);
        $result = ob_get_clean();
        $this->assertEquals("<script>var varName = \"1\";</script>\n", $result);
    }

    public function testSendVarToJsString()
    {
        ob_start();
        Utils::sendVarToJS('varName', 'a_string');
        $result = ob_get_clean();
        $this->assertEquals("<script>var varName = \"a_string\";</script>\n", $result);
    }

    public function testSendVarToJsArray()
    {
        ob_start();
        Utils::sendVarToJS('varName', [1, 2, 3]);
        $result = ob_get_clean();
        $this->assertEquals("<script>var varName = jQuery.parseJSON(\"[1,2,3]\");</script>\n", $result);
    }

    public function testSendVarToJsDict()
    {
        ob_start();
        Utils::sendVarToJS('varName', ["a" => "b", "c" => "d"]);
        $result = ob_get_clean();
        $this->assertEquals("<script>var varName = jQuery.parseJSON(\"{\\\"a\\\":\\\"b\\\",\\\"c\\\":\\\"d\\\"}\");</script>\n", $result);
    }

    public function testSendVarsToJsSimple()
    {
        ob_start();
        Utils::sendVarsToJS(['var1' => 1, 'var2' => "a string"]);
        $result = ob_get_clean();
        $this->assertEquals("<script>\nvar var1 = \"1\";\nvar var2 = \"a string\";\n</script>\n", $result);
    }

    public function testSendVarsToJsComplex()
    {
        ob_start();
        Utils::sendVarsToJS(['var1' => [0, 1], 'var2' => ["a" => 1, "b" => "ping"]]);
        $result = ob_get_clean();
        $this->assertEquals("<script>\nvar var1 = jQuery.parseJSON(\"[0,1]\");\nvar var2 = jQuery.parseJSON(\"{\\\"a\\\":1,\\\"b\\\":\\\"ping\\\"}\");\n</script>\n", $result);
    }

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

    public function testEvaluateSimpleWithComplexFunc() {
        $result = Utils::transformExpressionForEvaluation('variable(commut_switch_bathroom, second_param) == 0');
        $this->assertEquals('variable(commut_switch_bathroom,second_param)==0', $result);
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

    public function testEvaluateNegativeTest() {
        $result = Utils::transformExpressionForEvaluation('!(1 == 2.0 OR 2 == .28)');
        $this->assertEquals('!(1==2.0||2==.28)', $result);
    }

    public function testEvaluateNegativeFuncTest() {
        $result = Utils::transformExpressionForEvaluation('(1 == 2.0 OR !myFunc(23, 23))');
        $this->assertEquals('(1==2.0||!myFunc(23,23))', $result);
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

    public function testEvaluateNegativeNumbers() {
        $result = Utils::transformExpressionForEvaluation('-133.5 > 50.2 || .1 == -2');
        $this->assertEquals('-133.5>50.2||.1==-2', $result);
    }
}
