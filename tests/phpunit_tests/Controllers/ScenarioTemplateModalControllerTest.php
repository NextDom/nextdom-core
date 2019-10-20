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

use NextDom\Managers\CacheManager;

require_once(__DIR__ . '/../../../src/core.php');
require_once(__DIR__ . '/BaseControllerTest.php');

class ScenarioTemplateModalControllerTest extends BaseControllerTest
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        if (isset($_GET['scenario_id'])) {
            unset($_GET['scenario_id']);
        }
    }


    public function testSimple()
    {
        $_GET['scenario_id'] = 1;
        ob_start();
        $result = \NextDom\Controller\Modals\ScenarioTemplate::get();
        $scriptResult = ob_get_clean();
        $this->assertContains('id="md_scenarioTemplate"', $result);
        $this->assertContains('var scenario_template_id = ', $scriptResult);
    }
}
