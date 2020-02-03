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

use NextDom\Ajax\InteractAjax;
use NextDom\Managers\DataStoreManager;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class InteractAjaxTest extends BaseAjaxTest
{
    /** @var InteractAjax */
    private $interactAjax = null;

    public function setUp(): void
    {
        $this->interactAjax = new InteractAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        $testInteractVar = DataStoreManager::byTypeLinkIdKey('scenario', '-1', 'test_interact');
        if (is_object($testInteractVar)) {
            $testInteractVar->remove();
        }
    }

    public function testAll()
    {
        $this->connectAsAdmin();
        ob_start();
        $this->interactAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(2, $jsonResult['result']);
        $this->assertEquals('Test interact', $jsonResult['result'][0]['name']);
        $this->assertEquals('1', $jsonResult['result'][0]['nbInteractQuery']);
    }

    public function testExecute()
    {
        $this->connectAsAdmin();
        $_GET['query'] = 'Test interact';
        ob_start();
        $this->interactAjax->execute();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('42', $jsonResult['result']['reply']);
        $dataStore = DataStoreManager::byTypeLinkIdKey('scenario', '-1', 'test_interact');
        $this->assertEquals(42, $dataStore->getValue());
    }

    public function testExecuteWithSynonymous()
    {
        $this->connectAsAdmin();
        $_GET['query'] = 'light chamber';
        ob_start();
        $this->interactAjax->execute();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Done', $jsonResult['result']['reply']);
    }

    public function testExecuteWithBadQuery()
    {
        $this->connectAsAdmin();
        $_GET['query'] = 'An impossible sentence';
        ob_start();
        $this->interactAjax->execute();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('compr', $jsonResult['result']['reply']);
    }
}