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
use NextDom\Ajax\PluginAjax;
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

class PluginAjaxTest extends BaseAjaxTest
{
    /** @var PluginAjax */
    private $pluginAjax = null;

    public function setUp(): void
    {
        $this->pluginAjax = new PluginAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testAll()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        ob_start();
        $this->pluginAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('plugin4tests', $jsonResult['result'][0]['id']);
        $this->assertTrue($jsonResult['result'][0]['functionality']['cron']['exists']);
    }

    public function testAllDisconnected()
    {
        $this->expectException(CoreException::class);
        $this->pluginAjax->all();
    }
}