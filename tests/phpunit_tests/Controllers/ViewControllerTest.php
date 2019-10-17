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

use NextDom\Managers\ConfigManager;

require_once(__DIR__ . '/../../../src/core.php');
require_once(__DIR__ . '/BaseControllerTest.php');

class ViewControllerTest extends BaseControllerTest
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
    }


    public function testSimple()
    {
        $_SESSION['user'] = \NextDom\Managers\UserManager::byId(1);
        $pageData = [];
        $result = \NextDom\Controller\Pages\ViewController::get($pageData);
        $this->assertEquals(1, $pageData['viewsList'][0]->getId());
        $this->assertContains('Test view', $result);
    }

    public function testPageDataVars()
    {
        $_SESSION['user'] = \NextDom\Managers\UserManager::byId(1);
        $pageData = [];
        \NextDom\Controller\Pages\ViewController::get($pageData);
        $this->pageDataVars('desktop/pages/view.html.twig', $pageData);
    }
}
