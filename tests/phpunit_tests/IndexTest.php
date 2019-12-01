<?php
/** @noinspection PhpIncludeInspection */

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

class IndexTest extends PHPUnit_Framework_TestCase
{
    // For avoid NEXTDOM_ROOT override
    const ROOT_PATH = __DIR__ . '/../../';

    public static function setUpBeforeClass()
    {
        system('rm -fr ' . self::ROOT_PATH . 'mobile');
    }

    public function tearDown()
    {
        system('rm -fr ' . self::ROOT_PATH . 'mobile');
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
        if (isset($_SESSION['force_desktop'])) {
            unset($_SESSION['force_desktop']);
        }
        if (isset($_GET['force_desktop'])) {
            unset($_GET['force_desktop']);
        }
        if (isset($_SESSION['desktop_view'])) {
            unset($_SESSION['desktop_view']);
        }
    }

    private function getIndexContent()
    {
        ob_start();
        require_once(realpath(self::ROOT_PATH . 'index.php'));
        return ob_get_clean();
    }

    public function testSimple()
    {
        $result = $this->getIndexContent();
        $this->assertContains('connection.js', $result);
        $this->assertContains('<input type="password"', $result);
        $this->assertNotContains('id="mobile-box"', $result);
    }

    public function testOnPhoneWithoutMobileInstalled()
    {
        system('rm -fr ' . self::ROOT_PATH . 'mobile');
        $_SERVER["HTTP_USER_AGENT"] = 'Firefox Android';
        $result = $this->getIndexContent();
        $this->assertContains('connection.js', $result);
        $this->assertContains('<input type="password"', $result);
        $this->assertContains('id="mobile-box"', $result);
    }

    public function testOnPhoneWithForceDesktop()
    {
        system('rm -fr ' . self::ROOT_PATH . 'mobile');
        $_SERVER["HTTP_USER_AGENT"] = 'Firefox Android';
        $_GET['force_desktop'] = true;
        $result = $this->getIndexContent();
        $this->assertContains('connection.js', $result);
        $this->assertContains('<input type="password"', $result);
        $this->assertContains('id="mobile-box"', $result);
    }

    public function testOnPhoneWithDesktopViewSetted()
    {
        system('rm -fr ' . self::ROOT_PATH . 'mobile');
        $_SERVER["HTTP_USER_AGENT"] = 'Firefox Android';
        $_SESSION['desktop_view'] = true;
        $result = $this->getIndexContent();
        $this->assertContains('connection.js', $result);
        $this->assertContains('<input type="password"', $result);
        $this->assertContains('id="mobile-box"', $result);
    }
}