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
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class DataStoreAjaxTest extends BaseAjaxTest
{
    /** @var DataStoreAjax */
    private $dataStoreAjax = null;

    public function setUp(): void
    {
        $this->dataStoreAjax = new DataStoreAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        DBHelper::exec('DELETE FROM dataStore WHERE id > 2');
    }

    public function testRemove()
    {
        $dataStore = new DataStore();
        $dataStore->setKey('test key');
        $dataStore->setValue('test value');
        $dataStore->setLink_id(-1);
        $dataStore->setType('scenario');
        $dataStore->save();

        $_GET['id'] = $dataStore->getId();
        ob_start();
        $this->dataStoreAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertFalse(DataStoreManager::byId($dataStore->getId()));
    }

    public function testRemoveBadId()
    {
        $_GET['id'] = 9999;
        $this->expectException(CoreException::class);
        $this->dataStoreAjax->remove();
    }

    public function testSaveDoesntExists()
    {
        $_GET['key'] = 'test key';
        $_GET['value'] = 'test value';
        $_GET['link_id'] = -547;
        $_GET['type'] = 'scenario';
        ob_start();
        $this->dataStoreAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $dataStore = DataStoreManager::byTypeLinkId('scenario', -547);
        $this->assertEquals('test key', $dataStore[0]->getKey());
        $this->assertEquals('test value', $dataStore[0]->getValue());
    }

    public function testSaveAlreadyExists()
    {
        $dataStore = new DataStore();
        $dataStore->setKey('test key');
        $dataStore->setValue('test value');
        $dataStore->setLink_id(-832);
        $dataStore->setType('scenario');
        $dataStore->save();

        $_GET['id'] = $dataStore->getId();
        $_GET['value'] = 'modified value';
        ob_start();
        $this->dataStoreAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $modifiedDataStore = DataStoreManager::byTypeLinkId('scenario', -832);
        $this->assertEquals('test key', $modifiedDataStore[0]->getKey());
        $this->assertEquals('modified value', $modifiedDataStore[0]->getValue());
    }

    public function testAll()
    {
        $_GET['type'] = 'scenario';
        ob_start();
        $this->dataStoreAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals(42, $jsonResult['result'][0]['value']);
        $this->assertEquals(2, $jsonResult['result'][1]['id']);
    }
}