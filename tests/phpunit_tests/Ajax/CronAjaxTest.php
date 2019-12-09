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

use NextDom\Ajax\CronAjax;
use NextDom\Helpers\LogHelper;

require_once('BaseAjaxTest.php');

class CronAjaxTest extends BaseAjaxTest
{
    /** @var CronAjax */
    private $cronAjax = null;

    public function setUp()
    {
        $this->cronAjax = new CronAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
    }

    public function testAll()
    {
        $this->connectAdAdmin();
        ob_start();
        $this->cronAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(8, $jsonResult['result']);
        $this->assertEquals(1, $jsonResult['result'][0]['id']);
        $this->assertEquals('cron10', $jsonResult['result'][1]['function']);
    }

    public function testStart()
    {
        LogHelper::clear('plugin4tests');
        $_GET['id'] = 5;
        $this->connectAdAdmin();
        ob_start();
        $this->cronAjax->start();
        $result = ob_get_clean();
        sleep(10);
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('CRON ERROR', LogHelper::get('plugin4tests')[0]);
    }
}