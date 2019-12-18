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

use NextDom\Ajax\CmdAjax;
use NextDom\Ajax\DataStoreAjax;
use NextDom\Ajax\LogAjax;
use NextDom\Ajax\NoteAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;

require_once('BaseAjaxTest.php');

class NoteAjaxTest extends BaseAjaxTest
{
    /** @var NoteAjax */
    private $noteAjax = null;

    public function setUp(): void
    {
        $this->noteAjax = new NoteAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        DBHelper::exec('DELETE FROM note WHERE id > 2');
    }

    public function testAll()
    {
        ob_start();
        $this->noteAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Note de test', $jsonResult['result'][0]['name']);
        $this->assertEquals('Peu d\'idée', $jsonResult['result'][1]['text']);
    }

    public function testByIdExists()
    {
        $_GET['id'] = 1;
        ob_start();
        $this->noteAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Note de test', $jsonResult['result']['name']);
        $this->assertEquals('Un peu de contenu', $jsonResult['result']['text']);
    }

    public function testByIdDoesntExists()
    {
        $_GET['id'] = 999;
        ob_start();
        $this->noteAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(0, $jsonResult['result']);
    }

    public function testNewSaveEditSaveRemove()
    {
        // New
        $_GET['note'] = json_encode(['name' =>'Dynamic test note', 'text' => 'A sample text']);
        ob_start();
        $this->noteAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $returnedId = intval($jsonResult['result']['id']);
        $savedNote = NoteManager::byId($returnedId);
        $this->assertEquals('Dynamic test note', $savedNote->getName());
        $this->assertEquals('A sample text', $savedNote->getText());

        // Edit (reset ajax)
        $this->noteAjax = new NoteAjax();
        $_GET['note'] = json_encode(['id' => $savedNote->getId(), 'name' =>'Modified test note', 'text' => 'Another text']);
        ob_start();
        $this->noteAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals($returnedId, $jsonResult['result']['id']);
        $savedNote = NoteManager::byId($returnedId);
        $this->assertEquals('Modified test note', $savedNote->getName());
        $this->assertEquals('Another text', $savedNote->getText());

        // Remove (reset ajax)
        $this->noteAjax = new NoteAjax();
        $_GET['id'] = $returnedId;
        ob_start();
        $this->noteAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertFalse(NoteManager::byId($returnedId));
    }
}