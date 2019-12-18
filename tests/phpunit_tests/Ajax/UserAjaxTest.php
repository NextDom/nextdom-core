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
use NextDom\Ajax\UpdateAjax;
use NextDom\Ajax\UserAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Cmd;

require_once('BaseAjaxTest.php');

class UserAjaxTest extends BaseAjaxTest
{
    /** @var UserAjax */
    private $userAjax = null;

    public function setUp(): void
    {
        $this->userAjax = new UserAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testGetApiKey()
    {
        $_GET['username'] = 'admin';
        $_GET['password'] = 'nextdom-test';
        $_GET['twoFactorCode'] = false;
        ob_start();
        $this->userAjax->getApikey();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('VVZtg2HUxbE4XWStXTVWc2ONs0b0fXtt', $jsonResult['result']);
    }

    public function testGet()
    {
        $this->connectAsAdmin();
        ob_start();
        $this->userAjax->get();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('fea6ef74405b298a3fc654cfac01a3ed2e4cf010f394c0c555df15f14eed4833424545561c68461fc099843b06140ad4ba5c5694e209d30d10a5eb5b034d6a83', $jsonResult['result']['password']);
    }

    public function testRemoveBanIp()
    {
        CacheManager::set('security::banip', '10.0.0.0');
        $this->connectAsAdmin();
        ob_start();
        $this->userAjax->removeBanIp();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('', CacheManager::byKey('security::banip')->getValue());
    }
}