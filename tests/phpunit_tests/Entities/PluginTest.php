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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\PluginManager;
use NextDom\Model\Entity\Plugin;

require_once(__DIR__ . '/../../../src/core.php');

class PluginTest extends PHPUnit\Framework\TestCase
{
    private $testPluginData = [
        'id' => 'ThePlugin',
        'name' => 'The plugin to test',
        'description' => "A small description\nWith carriage return",
        'licence' => 'GPL',
        'author' => 'Someone',
        'hasDependency' => false,
        'hasOwnDeamon' => false,
        'require' => '3.3.30',
        'maxDependancyInstallTime' => 5,
    ];

    public static function tearDownAfterClass(): void
    {
        DBHelper::exec('DELETE FROM plugin WHERE id > 1');
    }

    public function tearDown(): void
    {
        DBHelper::exec('DELETE FROM plugin WHERE id > 1');
        system('rm -fr /var/log/nextdom/plugin4tests');
    }

    public function testInitPluginFromData()
    {
        $pluginToTest = new Plugin();
        $pluginToTest->initPluginFromData($this->testPluginData);
        $this->assertEquals($this->testPluginData['id'], $pluginToTest->getId());
        $this->assertEquals($this->testPluginData['name'], $pluginToTest->getName());
        $this->assertEquals("A small description<br />\nWith carriage return", $pluginToTest->getDescription());
        $this->assertEquals($this->testPluginData['licence'], $pluginToTest->getLicense());
        $this->assertEquals($this->testPluginData['author'], $pluginToTest->getAuthor());
        $this->assertEquals($this->testPluginData['hasDependency'], $pluginToTest->getHasDependency());
        $this->assertEquals($this->testPluginData['hasOwnDeamon'], $pluginToTest->getHasOwnDeamon());
        $this->assertEquals($this->testPluginData['maxDependancyInstallTime'], $pluginToTest->getMaxDependancyInstallTime());
        $this->assertEquals($this->testPluginData['require'], $pluginToTest->getRequire());
    }

    public function testGetPathToConfiguration() {
        $plugin4tests = PluginManager::byId('plugin4tests');
        $this->assertEquals('plugins/plugin4tests/plugin_info/configuration.php', $plugin4tests->getPathToConfiguration());
    }

    public function testGetLogList() {
        $plugin4tests = PluginManager::byId('plugin4tests');
        $result = $plugin4tests->getLogList();
        $this->assertCount(0, $result);
        LogHelper::addError('plugin4tests', 'Just a test');
        $result = $plugin4tests->getLogList();
        $this->assertCount(1, $result);
        $this->assertEquals('plugin4tests', $result[0]);
    }

    public function testRequire() {
        $pluginToTest = new Plugin();
        $customize = $this->testPluginData;
        $customize['require'] = '99.99';
        $pluginToTest->initPluginFromData($customize);
        $this->expectException(CoreException::class);
        $pluginToTest->setIsEnable(1);
    }
}