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

require_once(__DIR__ . '/libs/AjaxBase.php');

class AjaxUserTest extends AjaxBase
{
    private $ajaxFile = 'user';

    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'deleteSession']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertStringContainsString('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertStringContainsString('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testUseTwoFactorAuthentificationWithoutAjax() {
        $this->resetAjaxToken();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'useTwoFactorAuthentification']);
        $this->assertStringContainsString('"result":0', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testLoginAsWithoutAjax() {
        $this->resetAjaxToken();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'login']);
        $this->assertStringContainsString('Mot de passe ou nom d\'utilisateur incorrect', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetApikeyWithoutAjax() {
        $this->resetAjaxToken();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getApikey']);
        $this->assertStringContainsString('Mot de passe ou nom d\'utilisateur incorrect', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testValidateTwoFactorCodeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'validateTwoFactorCode']);
        $this->assertStringContainsString('Invalid characters in the base32 string.', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveTwoFactorCodeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeTwoFactorCode']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveTwoFactorCodeAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeTwoFactorCode']);
        $this->assertStringContainsString('User ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testIsConnectAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'isConnect']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRefreshAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'refresh']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testLogoutAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'logout']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAllAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'all']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAllAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'all']);
        $this->assertStringContainsString('{"state":"ok","result":[{"login":"admin"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'save']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'save']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertStringContainsString('User ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'get']);
        $this->assertStringContainsString('{"state":"ok","result":{"login":"user"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveProfilsAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'saveProfils']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveRegisterDeviceAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeRegisterDevice']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveRegisterDeviceAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeRegisterDevice']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDeleteSessionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'deleteSession']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testTestLdapConnectionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'testLdapConnection']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testTestLdapConnectionAsAdmin() {
        // ldap not installed
        $this->connectAsAdmin();
        try {
            $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'testLdapConnection']);
        }
        catch (\Exception $e) {
            $this->assertStringContainsString('500 Internal Server Error', $e->getMessage());
        }
    }

    public function testRemoveBanIpAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeBanIp']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveBanIpAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeBanIp']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSupportAccessAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'supportAccess']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSupportAccessAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'supportAccess']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

}