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

use NextDom\Rest\ScenarioRest;

require_once(__DIR__ . '/../../../src/core.php');

class ScenarioRestTest extends PHPUnit_Framework_TestCase
{
    public function testGetAll()
    {
        $result = ScenarioRest::getAll();
        $this->assertCount(3, $result);
        $this->assertEquals('Scenario with expressions', $result[0]['name']);
        $this->assertEquals('Test scenario', $result[1]['name']);
    }

    public function testGetAllByGroup() {
        $result = ScenarioRest::getAllByGroup();
        $this->assertArrayHasKey('no-group', $result);
        $this->assertArrayHasKey('Small group', $result);
        $this->assertEquals('Test scenario', $result['no-group'][0]['name']);
        $this->assertEquals('Empty scenario', $result['Small group'][0]['name']);
    }

    public function testLaunchOnExisting() {
        $result = ScenarioRest::launch(1);
        $this->assertTrue($result);
    }

    public function testLaunchOnBadId() {
        $result = ScenarioRest::launch(39);
        $this->assertFalse($result);
    }
}
