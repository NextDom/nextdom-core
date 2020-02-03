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

use NextDom\Ajax\PluginAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Managers\PluginManager;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class PluginAjaxTest extends BaseAjaxTest
{
    /** @var PluginAjax */
    private $pluginAjax = null;

    public function setUp(): void
    {
        $this->pluginAjax = new PluginAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        PluginManager::byId('plugin4tests')->setIsEnable(1);
    }

    public function testAll()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        ob_start();
        $this->pluginAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result'][0]['id']);
        $this->assertTrue($jsonResult['result'][0]['functionality']['cron']['exists']);
    }

    public function testAllDisconnected()
    {
        $this->expectException(CoreException::class);
        $this->pluginAjax->all();
    }

    public function testGetConf()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = 'plugin4tests';
        ob_start();
        $this->pluginAjax->getConf();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Plugin4Tests', $jsonResult['result']['name']);
        $this->assertFalse($jsonResult['result']['hasDependency']);
    }

    public function testGetConfBadPluginId()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $this->expectException(CoreException::class);
        $this->pluginAjax->getConf();
    }

    public function testToggleDisable()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = 'plugin4tests';
        $_GET['state'] = '0';
        ob_start();
        $this->pluginAjax->toggle();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals(0, PluginManager::byId('plugin4tests')->isActive());
    }

    public function testToggleEnable()
    {
        PluginManager::byId('plugin4tests')->setIsEnable(0);
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = 'plugin4tests';
        $_GET['state'] = '1';
        ob_start();
        $this->pluginAjax->toggle();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals(1, PluginManager::byId('plugin4tests')->isActive());
    }

    public function testToggleBadPluginId()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $this->expectException(CoreException::class);
        $this->pluginAjax->toggle();
    }
}