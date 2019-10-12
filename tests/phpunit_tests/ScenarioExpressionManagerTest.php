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

use NextDom\Managers\ScenarioExpressionManager;

require_once(__DIR__ . '/../../src/core.php');

class ScenarioExpressionManagerTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {

    }

    public function testByIdWithExisting() {
        $scenarioExpression = ScenarioExpressionManager::byId(1);
        $this->assertInstanceOf(\NextDom\Model\Entity\ScenarioExpression::class, $scenarioExpression);
        $this->assertEquals(1, $scenarioExpression->getid());
    }

    public function testByIdWithoutExisting() {
        $scenarioExpression = ScenarioExpressionManager::byId(392);
        $this->assertFalse($scenarioExpression);
    }

    public function testAll() {
        $scenarioExpressions = ScenarioExpressionManager::all();
        $this->assertCount(6, $scenarioExpressions);
        $this->assertEquals(3, $scenarioExpressions[2]->getId());
    }

    public function testByScenarioSubElementIdOnIf() {
        $scenarioExpressions = ScenarioExpressionManager::byScenarioSubElementId(1);
        $this->assertCount(2, $scenarioExpressions);
        $this->assertEquals(1, $scenarioExpressions[0]->getId());
        $this->assertEquals(2, $scenarioExpressions[1]->getId());
    }

    public function testByScenarioSubElementIdWithoutExpression() {
        $scenarioExpressions = ScenarioExpressionManager::byScenarioSubElementId(3);
        $this->assertCount(0, $scenarioExpressions);
    }

    public function testSearchExpressionSimple() {
        $scenarioExpressions = ScenarioExpressionManager::searchExpression('log');
        $this->assertCount(3, $scenarioExpressions);
        $this->assertEquals('log', $scenarioExpressions[0]->getExpression());
    }

    public function testSearchExpressionAndOption() {
        $scenarioExpressions = ScenarioExpressionManager::searchExpression('log', 'LAUNCHED');
        $this->assertCount(1, $scenarioExpressions);
        $this->assertEquals('log', $scenarioExpressions[0]->getExpression());
        $this->assertEquals('LAUNCHED', $scenarioExpressions[0]->getOptions('message'));
    }

    public function testSearchExpressionOrOption() {
        $scenarioExpressions = ScenarioExpressionManager::searchExpression('log', 'success', false);
        $this->assertCount(4, $scenarioExpressions);
        $this->assertEquals('log', $scenarioExpressions[0]->getExpression());
        $this->assertEquals('alert', $scenarioExpressions[3]->getExpression());
        $this->assertEquals('That\'s works', $scenarioExpressions[3]->getOptions('message'));
    }

    public function testRand() {
        for ($i = 0; $i < 20; ++$i) {
            $randomNumber = ScenarioExpressionManager::rand(2, 5);
            $this->assertTrue($randomNumber > 1);
            $this->assertTrue($randomNumber < 6);
        }
    }
}