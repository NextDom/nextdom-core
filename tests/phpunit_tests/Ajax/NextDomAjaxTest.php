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
use NextDom\Ajax\NextDomAjax;
use NextDom\Ajax\NoteAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class NextDomAjaxTest extends BaseAjaxTest
{
    /** @var NextDomAjax */
    private $nextdomAjax = null;

    public function setUp(): void
    {
        $this->nextdomAjax = new NextDomAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testGetDocumentationUrlCore()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        $_GET['page'] = 'eqlogic';
        ob_start();
        $this->nextdomAjax->getDocumentationUrl();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('https://jeedom.github.io/core/fr_FR/administration', $jsonResult['result']);
    }

    public function testGetDocumentationUrlPlugin()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        $_GET['plugin'] = 'plugin4tests';
        ob_start();
        $this->nextdomAjax->getDocumentationUrl();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('https://nextdom.github.io/plugin-plugin4tests/fr_FR/', $jsonResult['result']);
    }

    public function testResetHour()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        CacheManager::set('hour', 'symbolic value');
        ob_start();
        $this->nextdomAjax->resetHour();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('', CacheManager::byKey('hour')->getValue());
    }

    public function testClearDate()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        CacheManager::set('NextDomHelper::lastDate', 'symbolic value');
        ob_start();
        $this->nextdomAjax->clearDate();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('', CacheManager::byKey('NextDomHelper::lastDate')->getValue());
    }

    public function testGetConfiguration()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        ob_start();
        $this->nextdomAjax->getConfiguration();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('fa fa-fire', $jsonResult['result']['eqLogic']['category']['heating']['icon']);
    }

    public function testGetConfigurationOnImpossibleKey()
    {
        $this->connectAsAdmin();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        $_GET['key'] = 'eqLogic:displayType:dashboard';
        $_GET['default'] = true;
        ob_start();
        $this->nextdomAjax->getConfiguration();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Dashboard', $jsonResult['result']['name']);
    }
}