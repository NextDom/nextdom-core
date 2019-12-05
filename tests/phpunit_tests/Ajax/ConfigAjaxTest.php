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
use NextDom\Ajax\ConfigAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Cmd;

require_once('BaseAjaxTest.php');

class ConfigAjaxTest extends BaseAjaxTest
{
    /** @var ConfigAjax */
    private $configAjax = null;

    public function setUp()
    {
        $this->configAjax = new ConfigAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
        DBHelper::exec('DELETE FROM config WHERE plugin = "phpunit"');
    }

    public function testGetKeyOneVar()
    {
        $this->connectAdAdmin();
        $_GET['key'] = 'api';
        ob_start();
        $this->configAjax->getKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('uMOVMeaRleSDCVp6aZ5aLcOaVeOBeAVv', $jsonResult['result']);
    }

    public function testGetKeyBadVar()
    {
        $this->connectAdAdmin();
        $_GET['key'] = 'unknown-data';
        ob_start();
        $this->configAjax->getKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('', $jsonResult['result']);
    }

    public function testGetKeyNoVar()
    {
        $this->connectAdAdmin();
        $this->expectException(CoreException::class);
        $this->configAjax->getKey();
    }

    public function testGetKeyPluginVar()
    {
        $this->connectAdAdmin();
        $_GET['key'] = 'unused_key';
        $_GET['plugin'] = 'plugin4tests';
        ob_start();
        $this->configAjax->getKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('sample', $jsonResult['result']);
    }

    public function testGetKeyMultipleVars()
    {
        $this->connectAdAdmin();
        $_GET['key'] = '{"api":"","nextdom::user-theme":"","unknown":""}';
        ob_start();
        $this->configAjax->getKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('uMOVMeaRleSDCVp6aZ5aLcOaVeOBeAVv', $jsonResult['result']['api']);
        $this->assertEquals('dark-nextdom', $jsonResult['result']['nextdom::user-theme']);
        $this->assertEquals('', $jsonResult['result']['unknown']);
    }

    public function testAddOneKey()
    {
        $this->connectAdAdmin();
        $_POST['value'] = '{"one_key":"one_value"}';
        $_POST['plugin'] = 'phpunit';
        ob_start();
        $this->configAjax->addKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $addKey = ConfigManager::byKey('one_key', 'phpunit');
        $this->assertEquals('one_value', $addKey);
    }

    public function testRemoveOneKey()
    {
        ConfigManager::save('first_test', 'First value', 'phpunit');
        $this->connectAdAdmin();
        $_POST['key'] = '{"first_test":""}';
        $_POST['plugin'] = 'phpunit';
        ob_start();
        $this->configAjax->removeKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $keyToTest = ConfigManager::byKey('first_test', 'phpunit');
        $this->assertEquals('', $keyToTest);
    }

    public function testRemoveMultipleKey()
    {
        ConfigManager::save('first_test', 'First value', 'phpunit');
        ConfigManager::save('second_test', 'Second value', 'phpunit');
        $this->connectAdAdmin();
        $_POST['key'] = '{"first_test":"", "second_test":""}';
        $_POST['plugin'] = 'phpunit';
        ob_start();
        $this->configAjax->removeKey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $keysList = ConfigManager::byKeys(['first_test', 'second_test'], 'phpunit');
        $this->assertEquals('', $keysList['first_test']);
        $this->assertEquals('', $keysList['second_test']);
    }
}