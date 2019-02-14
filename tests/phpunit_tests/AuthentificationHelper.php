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

use NextDom\Helpers\AuthentificationHelper;

// TODO: Utiliser la vraie classe
class UserMock {
    public $IsConnectededResult = true;
    public $getProfilsResult = '';

    public function is_connected() {
        return $this->IsConnectededResult;
    }

    public function getProfils() {
        return $this->getProfilsResult;
    }
}

class AuthentificationHelperTest extends PHPUnit_Framework_TestCase
{
    public function testIsConnectedWithoutRightsWithoutData()
    {
        $result = AuthentificationHelper::IsConnected();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::']);
    }

    public function testIsConnectedWithoutRightsWithEmptySession()
    {
        $_SESSION = [];
        $result = AuthentificationHelper::IsConnected();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::']);
    }

    public function testIsConnectedWithoutRightsWithSessionUserEmpty()
    {
        $_SESSION = [];
        $_SESSION['user'] = null;
        $result = AuthentificationHelper::IsConnected();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::']);
    }

    public function testIsConnectedWithoutRightsWithSessionUserConnected()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->IsConnectededResult = true;
        $result = AuthentificationHelper::IsConnected();
        $this->assertTrue($result);
        $this->assertTrue($GLOBALS['IsConnected::']);
    }

    public function testIsConnectedWithoutRightsWithSessionUserDIsConnecteded()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->IsConnectededResult = false;
        $result = AuthentificationHelper::IsConnected();
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::']);
    }

    public function testIsConnectedWithRightsWithoutData()
    {
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::admin']);
    }

    public function testIsConnectedWithRightsWithEmptySession()
    {
        $_SESSION = [];
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::admin']);
    }

    public function testIsConnectedWithRightsWithSessionUserEmpty()
    {
        $_SESSION = [];
        $_SESSION['user'] = null;
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::admin']);
    }

    public function testIsConnectedWithRightsWithSessionUserConnectedNoRights()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->IsConnectededResult = true;
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::admin']);
    }

    public function testIsConnectedWithRightsWithSessionUserConnectedWithRights()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->IsConnectededResult = true;
        $_SESSION['user']->getProfilsResult = 'admin';
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertTrue($result);
        $this->assertTrue($GLOBALS['IsConnected::admin']);
    }

    public function testIsConnectedWithRightsWithSessionUserDIsConnecteded()
    {
        $_SESSION = [];
        $_SESSION['user'] = new UserMock();
        $_SESSION['user']->IsConnectededResult = false;
        $result = AuthentificationHelper::IsConnected('admin');
        $this->assertFalse($result);
        $this->assertFalse($GLOBALS['IsConnected::admin']);
    }
}
