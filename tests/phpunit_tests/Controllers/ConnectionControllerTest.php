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

require_once(__DIR__ . '/../../../src/core.php');
require_once(__DIR__ . '/BaseControllerTest.php');

class ConnectionControllerTest extends BaseControllerTest
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            unset($_SERVER['HTTP_USER_AGENT']);
        }
    }

    public function testSimple()
    {
        $pageData = [];
        $result = \NextDom\Controller\Pages\ConnectionController::get($pageData);
        $this->assertFalse($pageData['IS_MOBILE']);
        $this->assertArrayHasKey('TITLE', $pageData);
        $this->assertEquals(4, substr_count($result, 'input'));
    }

    public function testMobile()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        $pageData = [];
        $result = \NextDom\Controller\Pages\ConnectionController::get($pageData);
        $this->assertTrue($pageData['IS_MOBILE']);
        $this->assertContains('mobile', $result);
    }

    public function testPageDataVars()
    {
        $pageData = [];
        \NextDom\Controller\Pages\ConnectionController::get($pageData);
        $this->pageDataVars('desktop/pages/connection.html.twig', $pageData);
    }
}
