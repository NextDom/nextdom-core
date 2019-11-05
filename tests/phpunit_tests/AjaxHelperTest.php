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

use NextDom\Helpers\AjaxHelper;

class AjaxHelperTest extends PHPUnit_Framework_TestCase
{
    /** @var AjaxHelper */
    public $ajax = null;

    public function setUp() {
        $this->ajax = new AjaxHelper();
    }

    public function testGetTokenWithSameSession() {
        $token1 = AjaxHelper::getToken();
        $token2 = AjaxHelper::getToken();
        $token3 = AjaxHelper::getToken();
        $this->assertEquals($token1, $token2);
        $this->assertEquals($token2, $token3);
        $this->assertEquals($token1, $token3);
    }

    public function testGetTokenWithDifferentSessions() {
        $token1 = AjaxHelper::getToken();
        unset($_SESSION);
        $token2 = AjaxHelper::getToken();
        unset($_SESSION);
        $token3 = AjaxHelper::getToken();
        $this->assertEquals($token1, $token2);
        $this->assertEquals($token2, $token3);
        $this->assertEquals($token1, $token3);
    }

    public function testGetResponseWithStringAndErrorCode() {
        $result = $this->ajax->getResponse('A message', 12);
        $this->assertEquals('{"state":"error","result":"A message","code":12}', $result);
    }

    public function testGetResponseWithStringWithoutErrorCode() {
        $result = $this->ajax->getResponse('A message');
        $this->assertEquals('{"state":"ok","result":"A message"}', $result);
    }

    public function testGetResponseWithArrayAndErrorCode() {
        $result = $this->ajax->getResponse(['ab' => 3, 'c' => 'd'], 12);
        $this->assertEquals('{"state":"error","result":{"ab":3,"c":"d"},"code":12}', $result);
    }

    public function testGetResponseWithArrayWithoutErrorCode() {
        $result = $this->ajax->getResponse(['ab' => 3, 'c' => 'd']);
        $this->assertEquals('{"state":"ok","result":{"ab":3,"c":"d"}}', $result);
    }

}
