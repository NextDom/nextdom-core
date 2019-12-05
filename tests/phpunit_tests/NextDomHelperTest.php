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

use NextDom\Helpers\NextDomHelper;
use NextDom\Managers\ConfigManager;

require_once(__DIR__ . '/../../src/core.php');

define('REMOVE_HISTORY_PATH_FILE', NEXTDOM_DATA . '/data/remove_history.json');

class NextDomHelperTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        system('rm -fr ' . NEXTDOM_DATA . '/data');
        mkdir(NEXTDOM_DATA . '/data', 0777, true);
    }

    public function setUp()
    {
        if (file_exists(REMOVE_HISTORY_PATH_FILE)) {
            unlink(REMOVE_HISTORY_PATH_FILE);
        }
        touch(REMOVE_HISTORY_PATH_FILE);
    }

    public function testAddRemoveHistoryAddSimpleLine()
    {
        NextDomHelper::addRemoveHistory('Test');
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('["Test"]', $fileContent);
    }

    public function testAddRemoveHistoryAddArrayLine()
    {
        NextDomHelper::addRemoveHistory(['a' => 'b', 'c' => 1]);
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('[{"a":"b","c":1}]', $fileContent);
    }

    public function testAddRemoveHistoryAddMultipleLines()
    {
        NextDomHelper::addRemoveHistory('Line 1');
        NextDomHelper::addRemoveHistory('Line 2');
        NextDomHelper::addRemoveHistory('Line 3');
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals('["Line 1","Line 2","Line 3"]', $fileContent);
    }

    public function testAddRemoveHistoryAddOutOfLimits()
    {
        for ($i = 0; $i < 220; ++$i) {
            NextDomHelper::addRemoveHistory("Line $i");
        }
        $fileContent = file_get_contents(REMOVE_HISTORY_PATH_FILE);
        $this->assertEquals(0, strpos($fileContent, '["Line 20","Line 21"'));
    }

    public function testGetConfigurationAllData()
    {
        $configuration = NextDomHelper::getConfiguration();
        $this->assertArrayHasKey('eqLogic', $configuration);
        $this->assertArrayHasKey('cmd', $configuration);
    }

    public function testGetConfigurationWithKey()
    {
        $alertsConf = NextDomHelper::getConfiguration('alerts');
        $this->assertArrayHasKey('batterywarning', $alertsConf);
    }

    public function testGetTmpFolder()
    {
        $tmpFolder = NextDomHelper::getTmpFolder();
        $this->assertEquals('/tmp/nextdom', $tmpFolder);
    }

    public function testGetTmpFolderWithNewFolder()
    {
        $testPath = '/tmp/nextdom/just_a_test';
        if (is_dir($testPath)) {
            rmdir($testPath);
        }
        $tmpFolder = NextDomHelper::getTmpFolder('just_a_test');
        $this->assertEquals($testPath, $tmpFolder);
        $this->assertDirectoryIsWritable($testPath);
        rmdir($testPath);
    }

    public function testIsCapableSudo()
    {
        $result = NextDomHelper::isCapable('sudo');
        $this->assertTrue($result);
    }

    public function testGetHardwareName()
    {
        $result = NextDomHelper::getHardwareName();
        $this->assertEquals('docker', $result);
    }

    public function testGetHardwareNameWithRemovedConfig()
    {
        ConfigManager::remove('hardware_name');
        $result = NextDomHelper::getHardwareName();
        $this->assertEquals('docker', $result);
    }

    public function testGetTypeUseWithCmd()
    {
        $result = NextDomHelper::getTypeUse('#1#');
        $this->assertCount(1, $result['cmd']);
        $this->assertEquals('Cmd 1', $result['cmd'][1]->getName());
        $result = NextDomHelper::getTypeUse('#1##2#');
        $this->assertCount(2, $result['cmd']);
        $this->assertEquals('Cmd 2', $result['cmd'][2]->getName());
    }

    public function testGetTypeUseWithScenario()
    {
        $result = NextDomHelper::getTypeUse('#scenario1#');
        $this->assertCount(1, $result['scenario']);
        $this->assertEquals('Test scenario', $result['scenario'][1]->getName());
        $result = NextDomHelper::getTypeUse('#scenario1##scenario2#');
        $this->assertCount(2, $result['scenario']);
        $this->assertEquals('Empty scenario', $result['scenario'][2]->getName());
        $result = NextDomHelper::getTypeUse('"scenario_id":"3"');
        $this->assertCount(1, $result['scenario']);
        $this->assertEquals('Scenario with expressions', $result['scenario'][3]->getName());
        $result = NextDomHelper::getTypeUse('"scenario_id":"3""scenario_id":"4"');
        $this->assertCount(2, $result['scenario']);
        $this->assertEquals('Disabled scenario', $result['scenario'][4]->getName());
    }

    public function testGetTypeUseWithEqLogic()
    {
        $result = NextDomHelper::getTypeUse('#eqLogic1#');
        $this->assertCount(1, $result['eqLogic']);
        $this->assertEquals('Test eqLogic', $result['eqLogic'][1]->getName());
        $result = NextDomHelper::getTypeUse('#eqLogic1##eqLogic2#');
        $this->assertCount(2, $result['eqLogic']);
        $this->assertEquals('Second eqLogic', $result['eqLogic'][2]->getName());
        $result = NextDomHelper::getTypeUse('#"eqLogic":"3"#');
        $this->assertCount(1, $result['eqLogic']);
        $this->assertEquals('Third eqLogic', $result['eqLogic'][3]->getName());
        $result = NextDomHelper::getTypeUse('#"eqLogic":"3"##"eqLogic":"4"#');
        $this->assertCount(2, $result['eqLogic']);
        $this->assertEquals('A lamp', $result['eqLogic'][4]->getName());
    }

    public function testGetTypeUseWithView()
    {
        $result = NextDomHelper::getTypeUse('"view_id":"1"');
        $this->assertCount(1, $result['view']);
        $this->assertEquals('Test view', $result['view'][1]->getName());
    }

    public function testGetTypeUseWithPlan()
    {
        $result = NextDomHelper::getTypeUse('"plan_id":"1"');
        $this->assertCount(1, $result['plan']);
        $this->assertEquals('Plan test', $result['plan'][1]->getName());
    }

    public function testGetTypeUseWithVariable()
    {
        $result = NextDomHelper::getTypeUse('variable(numeric_data)');
        $this->assertCount(1, $result['dataStore']);
        $this->assertEquals('42', $result['dataStore']['numeric_data']->getValue());
        $result = NextDomHelper::getTypeUse('variable(numeric_data)variable(text_data)');
        $this->assertCount(2, $result['dataStore']);
        $this->assertEquals('H2G2', $result['dataStore']['text_data']->getValue());
    }

    public function testGetTypeUseWithAll()
    {
        $result = NextDomHelper::getTypeUse('#1##scenario1##eqLogic1#"view_id":"1""plan_id":"1"variable(numeric_data)');
        $this->assertCount(1, $result['cmd']);
        $this->assertEquals('Cmd 1', $result['cmd'][1]->getName());
        $this->assertCount(1, $result['scenario']);
        $this->assertEquals('Test scenario', $result['scenario'][1]->getName());
        $this->assertCount(1, $result['eqLogic']);
        $this->assertEquals('Test eqLogic', $result['eqLogic'][1]->getName());
        $this->assertCount(1, $result['view']);
        $this->assertEquals('Test view', $result['view'][1]->getName());
        $this->assertCount(1, $result['plan']);
        $this->assertEquals('Plan test', $result['plan'][1]->getName());
        $this->assertCount(1, $result['dataStore']);
        $this->assertEquals('42', $result['dataStore']['numeric_data']->getValue());
    }
}
