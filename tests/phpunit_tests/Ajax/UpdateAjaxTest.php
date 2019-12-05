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
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Cmd;

require_once('BaseAjaxTest.php');

class UpdateAjaxTest extends BaseAjaxTest
{
    /** @var UpdateAjax */
    private $configAjax = null;

    public function setUp()
    {
        $this->configAjax = new UpdateAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
    }

    public function testAll()
    {
        $this->connectAdAdmin();
        ob_start();
        $this->configAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(2, $jsonResult['result']);
    }
}