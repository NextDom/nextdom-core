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

class AjaxNextDomTest extends AjaxBase
{
    private $ajaxFile = 'nextdom';

    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'addWarnme']);
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

    public function testGetInfoApplicationWithoutAjax() {
        $this->connectAsUser();
        $this->resetAjaxToken();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getInfoApplication']);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetDocumentationUrlAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getDocumentationUrl']);
        $this->assertStringContainsString('jeedom.github.io', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAddWarnMeAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'addWarnme']);
        $this->assertStringContainsString('Commande non trouvée', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSshAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'ssh']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSshAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'ssh']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDbAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'db']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDbAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'db']);
        $this->assertStringContainsString('Aucune requête à exécuter', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDbCorrectTableAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'dbcorrectTable']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDbCorrectTableAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'dbcorrectTable']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHealthAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'health']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHealthAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'health']);
        $this->assertStringContainsString('{"state":"ok","result":[{"icon":"fa-cogs"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testUpdateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'update']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testUpdateAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'update']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testClearDateAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'clearDate']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testClearDateAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'clearDate']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testBackupAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'backup']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testBackupAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'backup']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRestoreAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'restoreLocal']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
/*
    public function testRestoreAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'restoreLocal']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
*/
    public function testRemoveBackupAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeBackup']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveBackupAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeBackup']);
        $this->assertStringContainsString('Impossible de trouver le fichier', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListBackupAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listBackup']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testListBackupAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'listBackup']);
        $this->assertStringContainsString('{"state":"ok","result":', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetConfigurationAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getConfiguration']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetConfigurationAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getConfiguration']);
        $this->assertStringContainsString('{"state":"ok","result":{"eqLogic":{"category"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testResetHwKeyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'resetHwKey']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testResetHwKeyAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'resetHwKey']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testResetHourAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'resetHour']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testResetHourAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'resetHour']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testBackupUploadAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'backupupload']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testBackupUploadAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'backupupload']);
        $this->assertStringContainsString('Aucun fichier trouvé', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
    public function testHaltSystemAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'haltSystem']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRebootSystemAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'rebootSystem']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testForceSyncHourAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'forceSyncHour']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testForceSyncHourAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'forceSyncHour']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetGraphDataAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getGraphData']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetGraphDataAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getGraphData']);
        $this->assertStringContainsString('Aucun filtre', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetTimelineEventsAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getTimelineEvents']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetTimelineEventsAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getTimelineEvents']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveTimelineEventsAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeTimelineEvents']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveTimelineEventsAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeTimelineEvents']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetFileFolderAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getFileFolder']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetFileFolderAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getFileFolder']);
        $this->assertStringContainsString('"result":[]', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetFileContentAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getFileContent']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetFileContentAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getFileContent']);
        $this->assertStringContainsString('Vous ne pouvez éditer ce type d\'extension', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetFileContentAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setFileContent']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testSetFileContentAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'setFileContent']);
        $this->assertStringContainsString('Vous ne pouvez éditer ce type d\'extension', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDeleteFileAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'deleteFile']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testDeleteFileAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'deleteFile']);
        $this->assertStringContainsString('Vous ne pouvez éditer ce type d\'extension', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCreateFileAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'createFile']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCreateFileAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'createFile']);
        $this->assertStringContainsString('Vous ne pouvez éditer ce type d\'extension', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testEmptyRemoveHistoryAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'emptyRemoveHistory']);
        $this->assertStringContainsString('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testEmptyRemoveHistoryAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'emptyRemoveHistory']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testConsistency() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'consistency']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCleanFileSystemRight() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'cleanFileSystemRight']);
        $this->assertStringContainsString('"result":""', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testVersionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'version']);
        $this->assertStringContainsString('"result":{"nextdom":', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testVersionAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'version']);
        $this->assertStringContainsString('"result":{"nextdom":', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }
}
