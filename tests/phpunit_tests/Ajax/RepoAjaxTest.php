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

use NextDom\Ajax\RepoAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class RepoAjaxTest extends BaseAjaxTest
{
    /** @var RepoAjax */
    private $repoAjax = null;

    public function setUp(): void
    {
        $this->repoAjax = new RepoAjax();
    }

    public function tearDown(): void
    {
    }

    public function testTestWithMarketNotConfigured()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'market';
        $this->expectException(CoreException::class);
        $this->repoAjax->test();
    }

    public function testTestRepoWithoutTest()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'apt';
        ob_start();
        $this->repoAjax->test();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('error', $jsonResult['state']);
    }

    public function testTestWithInvalidRepo()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'not-valid';
        ob_start();
        $this->repoAjax->test();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('error', $jsonResult['state']);
    }

    public function testGetInfoWithValidRepo()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'market';
        ob_start();
        $this->repoAjax->getInfo();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
    }

    public function testGetInfoWithInvalidRepo()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'not-valid';
        ob_start();
        $this->repoAjax->getInfo();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('error', $jsonResult['state']);
    }
    
    public function testGetBackupListWithInvalidRepo()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'not-valid';
        ob_start();
        $this->repoAjax->backupList();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('error', $jsonResult['state']);
    }
}