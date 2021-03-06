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

require_once('libs/AjaxBase.php');

class AjaxEqLogicTest extends AjaxBase
{
    private $ajaxFile = 'eqLogic';

    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
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

    public function testGetEqLogicObjectAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getEqLogicObject']);
        $this->assertStringContainsString('Objet inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testByIdAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'byId']);
        $this->assertStringContainsString('EqLogic inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testToHtmlAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'toHtml']);
        $this->assertStringContainsString('Eqlogic inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHtmlAlertAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'htmlAlert']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHtmlBatteryAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'htmlBattery']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListByTypeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listByType']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListByObjectAndCmdTypeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listByObjectAndCmdType']);
        $this->assertStringContainsString('"result":null', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListByObjectAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listByObject']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListByTypeAndCmdTypeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listByTypeAndCmdType']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetIsEnableAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setIsEnable']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetIsEnableAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setIsEnable']);
        $this->assertStringContainsString('EqLogic inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetOrderAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setOrder']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemovesAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removes']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
        // Todo : Bizarre
    }

    public function testSetIsVisiblesAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setIsVisibles']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetIsEnablesAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setIsEnables']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSimpleSaveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'simpleSave']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSimpleSaveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'simpleSave']);
        $this->assertStringContainsString('EqLogic inconnu.', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCopyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'copy']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCopyAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'copy']);
        $this->assertStringContainsString('EqLogic inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveCmdAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertStringContainsString('EqLogic inconnu. Vérifiez l\'ID', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'get']);
        $this->assertStringContainsString('Type incorrect (classe équipement inexistante)', (string) $result->getBody());
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
        $this->assertStringContainsString('"result":null', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetAlertAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getAlert']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetUseBeforeRemoveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getUseBeforeRemove']);
        $this->assertStringContainsString('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetUseBeforeRemoveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getUseBeforeRemove']);
        $this->assertStringContainsString('ID de l\'objet manquant', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
}