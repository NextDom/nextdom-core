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

class AjaxScenarioTest extends AjaxBase
{
    private $ajaxFile = 'scenario';

    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'changeState']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testChangeStateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'changeState']);
        $this->assertContains('Scénario ID inconnu :', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListScenarioHtmlAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listScenarioHtml']);
        $this->assertContains('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetOrderAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setOrder']);
        $this->assertContains('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testTestExpressionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'testExpression']);
        $this->assertContains('"result":{"evaluate":"","result":"","correct":"nok"}}', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetTemplateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getTemplate']);
        $this->assertContains('"result":[]}', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testConvertToTemplateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'convertToTemplate']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveTemplateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeTemplate']);
        $this->assertContains('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testLoadTemplateDiffAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'loadTemplateDiff']);
        $this->assertContains('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testApplyTemplateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'applyTemplate']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAllAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'all']);
        $this->assertContains('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveAllAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'saveAll']);
        $this->assertContains('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAutoCompleteGroupAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'autoCompleteGroup']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAutoCompleteGroupAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'autoCompleteGroup']);
        $this->assertContains('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testToHtmlAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'toHtml']);
        $this->assertContains('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'remove']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testEmptyLogAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'emptyLog']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testEmptyLogAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'emptyLog']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCopyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'copy']);
        $this->assertContains('401 -', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCopyAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'copy']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'get']);
        $this->assertContains('Scénario ID inconnu', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'save']);
        $this->assertContains('401 -', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSaveAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'save']);
        $this->assertContains('Champs json invalide', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testActionToHtmlAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'actionToHtml']);
        $this->assertContains('"result":{"html":"","template":""}}', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testTemplateUploadAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'templateupload']);
        $this->assertContains('Aucun fichier trouvé. Vérifiez le paramètre PHP', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
}