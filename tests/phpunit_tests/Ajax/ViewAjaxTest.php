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

use NextDom\Ajax\ViewAjax;
use NextDom\Helpers\AuthentificationHelper;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class ViewAjaxTest extends BaseAjaxTest
{
    /** @var ViewAjax */
    private $viewAjax = null;

    public function setUp(): void
    {
        $this->viewAjax = new ViewAjax();
    }

    public function tearDown(): void
    {
    }

    public function testAll()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['repo'] = 'apt';
        ob_start();
        $this->viewAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Test view', $jsonResult['result'][0]['name']);
    }

    public function testGetWithoutHtml()
    {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['id'] = '1';
        $_GET['html'] = false;
        ob_start();
        $this->viewAjax->get();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Test view', $jsonResult['result']['name']);
        $this->assertEquals('My Zone', $jsonResult['result']['viewZone'][0]['name']);
    }
}