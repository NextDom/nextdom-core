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
use NextDom\Ajax\EventAjax;
use NextDom\Ajax\LogAjax;
use NextDom\Ajax\NoteAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;

require_once('BaseAjaxTest.php');

class EventAjaxTest extends BaseAjaxTest
{
    /** @var EventAjaxTest */
    private $eventAjax = null;

    public function setUp(): void
    {
        $this->eventAjax = new EventAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testChanges()
    {
        EventManager::add('scenario::update', ['scenario_id' => 1, 'state' => 'fake_state', 'last_launch' => '2019-12-22 18:41:18']);
        $_GET['datetime'] = strtotime('now') - 2;
        ob_start();
        $this->eventAjax->changes();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('scenario::update', $jsonResult['result']['result'][0]['name']);
        $this->assertEquals('fake_state', $jsonResult['result']['result'][0]['option']['state']);
    }
}