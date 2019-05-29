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

require_once(__DIR__ . '/../../src/core.php');

class ConfigManagerTest extends PHPUnit_Framework_TestCase
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

    public function testGetDefaultConfiguration()
    {
        $defaultConfig = ConfigManager::getDefaultConfiguration();
        $this->assertArrayHasKey('core', $defaultConfig);
        $this->assertEquals('3', $defaultConfig['core']['security::maxFailedLogin']);
        $this->assertArrayHasKey('developer::mode', $defaultConfig['core']);
    }

    public function testGetDefaultConfigurationFromPlugin()
    {
        $pluginConfig = ConfigManager::getDefaultConfiguration('plugin4tests');
        $this->assertEquals(2, count($pluginConfig));
        $this->assertEquals('a text value', $pluginConfig['config_key']);
        $this->assertArrayHasKey('another_key', $pluginConfig);
    }

    public function testGetDefaultConfigurationFromPluginWithoutConfig()
    {
        $pluginConfig = ConfigManager::getDefaultConfiguration('impossiblePlugin');
        $this->assertEmpty($pluginConfig);
    }

    public function testByKeySaveAndRemoveConfigKey()
    {
        // From default value
        $defaultValue = ConfigManager::byKey('log::level');
        $this->assertEquals('400', $defaultValue);
        // Save new value
        ConfigManager::save('log::level', '500');
        $defaultValue = ConfigManager::byKey('log::level');
        $this->assertEquals('500', $defaultValue);
        // Remove saved value
        ConfigManager::remove('log::level');
        $defaultValue = ConfigManager::byKey('log::level');
        $this->assertEquals('400', $defaultValue);
    }

    public function testByKeysFromDefault()
    {
        $keys = ConfigManager::byKeys(['internalPort', 'enableScenario']);
        $this->assertEquals('80', $keys['internalPort']);
        $this->assertEquals('1', $keys['enableScenario']);
    }

    public function testByKeysFromDefaultAndDatabase()
    {
        ConfigManager::save('session_lifetime', '25');
        $keys = ConfigManager::byKeys(['internalPort', 'session_lifetime']);
        $this->assertEquals('80', $keys['internalPort']);
        $this->assertEquals('25', $keys['session_lifetime']);
        ConfigManager::remove('session_lifetime');
    }

    public function testSearchKey()
    {
        ConfigManager::remove('update::lastCheck');
        ConfigManager::save('update::allowCore', '0');
        ConfigManager::save('update::backupBefore', '0');
        $updateKeys = ConfigManager::searchKey('update');
        $this->assertEquals(2, count($updateKeys));
        ConfigManager::remove('update::allowCore');
        ConfigManager::remove('update::backupBefore');
        $updateKeys = ConfigManager::searchKey('update');
        $this->assertEquals(0, count($updateKeys));
    }

    public function testGenKey()
    {
        $key = ConfigManager::genKey(58);
        $this->assertEquals(58, strlen($key));
        $this->assertRegExp('/^[0-9a-zA-Z]{58}$/', $key);
    }

    public function testGetEnabledPlugins()
    {
        $plugins = ConfigManager::getEnabledPlugins();
        $this->assertArrayHasKey('plugin4tests', $plugins);
    }
}