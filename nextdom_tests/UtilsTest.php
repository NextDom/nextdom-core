<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 09/06/2018
 * Time: 17:07
 */

use NextDom\Helper\Utils;

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
        $toTest = Utils::init('Name', false);
        $this->assertFalse($toTest);
    }

    public function testInitWithDataGetOnly()
    {
        $_GET = ['Name' => 'Result'];
        $_POST = [];
        $_REQUEST = [];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataPostOnly()
    {
        $_GET = [];
        $_POST = ['Name' => 'Result'];
        $_REQUEST = [];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataRequestOnly()
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = ['Name' => 'Result'];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('Result', $toTest);
    }

    public function testInitWithDataGetPost()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = [];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('GET', $toTest);
    }

    public function testInitWithDataGetRequest()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = [];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('GET', $toTest);
    }

    public function testInitWithDataPostRequest()
    {
        $_GET = [];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', false);
        $this->assertEquals('POST', $toTest);
    }

    public function testInitWithDataGetPostRequest()
    {
        $_GET = ['Name' => 'GET'];
        $_POST = ['Name' => 'POST'];
        $_REQUEST = ['Name' => 'REQUEST'];
        $toTest = Utils::init('Name', false);
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

    public function testIsConnect()
    {

    }

}
