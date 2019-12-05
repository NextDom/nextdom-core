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

use NextDom\Enums\ViewType;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Router;
use NextDom\Managers\ConfigManager;

require_once(__DIR__ . '/../../src/core.php');

class RouterTest extends PHPUnit_Framework_TestCase
{
    private static $firstUseState = null;

    public static function setUpBeforeClass()
    {
        self::$firstUseState = ConfigManager::byKey('nextdom::firstUse');
    }

    public static function tearDownAfterClass()
    {
        ConfigManager::save('nextdom::firstUse', self::$firstUseState);
    }

    public function tearDown()
    {
        foreach (array_keys($_GET) as $getKey) {
            unset($_GET[$getKey]);
        }
        ConfigManager::save('nextdom::firstUse', 0);
        DBHelper::exec('UPDATE user SET password = SHA2("nextdom-test", 512) WHERE login = "admin"');
    }

    public function getContent($connected = false)
    {
        if ($connected) {
            AuthentificationHelper::login('admin', 'nextdom-test');
        }
        $router = new Router(ViewType::DESKTOP_VIEW);
        ob_start();
        $router->show();
        return ob_get_clean();
    }

    public function testDesktopViewDisconnected()
    {
        $_GET['p'] = 'dashboard';
        $result = $this->getContent();
        $this->assertContains('<input type="password"', $result);
        $this->assertContains('<input type="checkbox" id="storeConnection">', $result);
    }

    public function testDesktopViewConnected()
    {
        $_GET['p'] = 'dashboard';
        $result = $this->getContent(true);
        $this->assertContains('id="div_mainContainer"', $result);
    }

    public function testModalDisconnected()
    {
        $_GET['p'] = 'dashboard';
        $_GET['modal'] = 'welcome';
        $this->expectException(CoreException::class);
        $router = new Router(ViewType::DESKTOP_VIEW);
        $router->show();
    }

    public function testModalConnected()
    {
        $_GET['p'] = 'dashboard';
        $_GET['modal'] = 'welcome';
        $result = $this->getContent(true);
        $this->assertContains('Bienvenue dans NextDom', $result);
    }

    public function testPluginConfPageDisconnected()
    {
        $_GET['p'] = 'dashboard';
        $_GET['configure'] = 1;
        $_GET['plugin'] = 'plugin4tests';
        $this->expectException(CoreException::class);
        $router = new Router(ViewType::DESKTOP_VIEW);
        $router->show();
    }

    public function testPluginConfPageConnected()
    {
        $_GET['p'] = 'dashboard';
        $_GET['configure'] = 1;
        $_GET['plugin'] = 'plugin4tests';
        $result = $this->getContent(true);
        $this->assertContains('<input class="configKey form-control" data-l1key="text_option" />', $result);
    }

    public function testPageByAjaxDisconnected()
    {
        $_GET['p'] = 'administration';
        $_GET['ajax'] = 1;
        $result = $this->getContent();
        $this->assertContains('<span id="span_errorMessage">401', $result);
    }

    public function testPageByAjaxConnected()
    {
        $_GET['p'] = 'administration';
        $_GET['ajax'] = 1;
        $result = $this->getContent(true);
        $this->assertContains('<link href="/public/css/pages/administration.css" rel="stylesheet" />', $result);
        $this->assertNotContains('<body', $result);
    }

    public function testFirstUseWithDefaultPassword()
    {
        ConfigManager::save('nextdom::firstUse', 1);
        DBHelper::exec('UPDATE user SET password = SHA2("admin", 512) WHERE login = "admin"');
        $_GET['p'] = 'administration';
        $result = $this->getContent();
        $this->assertContains('stepwizard-btn-circle', $result);
    }

    public function testFirstUseWithPasswordSetted()
    {
        ConfigManager::save('nextdom::firstUse', 1);
        $_GET['p'] = 'administration';
        $result = $this->getContent();
        $this->assertContains('<input type="password"', $result);
        $this->assertContains('<input type="checkbox" id="storeConnection">', $result);
    }
}