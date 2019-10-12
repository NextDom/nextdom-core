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

class ApiControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        ConfigManager::remove('api', 'core');
    }


    public function testSimple()
    {
        ConfigManager::save('api', 'GPaCCxccAwyZx8kNgLlDkGlZxp0W3vaM', 'core');
        $pageData = [];
        $result = \NextDom\Controller\Admin\ApiController::get($pageData);
        $this->assertArrayHasKey('adminConfigs', $pageData);
        $this->assertArrayHasKey('api', $pageData['adminConfigs']);
        $this->assertContains('Sauvegarder', $result);
        $this->assertContains('GPaCCxccAwyZx8kNgLlDkGlZxp0W3vaM', $result);
    }
}
