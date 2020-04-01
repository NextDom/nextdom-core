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
use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class LogAjaxTest extends BaseAjaxTest
{
    /** @var LogAjax */
    private $logAjax = null;

    public function setUp(): void
    {
        $this->logAjax = new LogAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testList()
    {
        ob_start();
        $this->logAjax->list();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('http.error', $jsonResult['result']);
        $this->assertContains('cron_execution', $jsonResult['result']);
    }

    public function testGetExists()
    {
        $_GET['log'] = 'cron_execution';
        file_put_contents('/var/log/nextdom/cron_execution', 'Test this');
        ob_start();
        $this->logAjax->get();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Test this', $jsonResult['result'][count($jsonResult['result']) - 1]);
    }

    public function testGetDoesntExists()
    {
        $_GET['log'] = 'thats_impossible';
        ob_start();
        $this->logAjax->get();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertFalse($jsonResult['result']);
    }

    public function testClear()
    {
        LogHelper::addError(LogTarget::SCENARIO, 'An error message');
        $_GET['log'] = 'scenario';
        ob_start();
        $this->logAjax->clear();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(0, LogHelper::get(LogTarget::SCENARIO));
    }

    public function testRemove()
    {
        LogHelper::addError(LogTarget::SCENARIO, 'An error message');
        $_GET['log'] = 'scenario';
        ob_start();
        $this->logAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertFalse(LogHelper::get(LogTarget::SCENARIO));
    }

    public function testRemoveAll()
    {
        LogHelper::addError(LogTarget::SCENARIO, 'An error message');
        LogHelper::addError(LogTarget::PLUGIN, 'An error message');
        LogHelper::addError(LogTarget::MARKET, 'An error message');
        ob_start();
        $this->logAjax->removeAll();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertFalse(LogHelper::get(LogTarget::SCENARIO));
    }
}