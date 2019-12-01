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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;

require_once(__DIR__ . '/../../src/core.php');

class AjaxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @throws Exception
     */
    public function testAjaxSimple() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        $_GET['target'] = 'EqLogic';
        $_GET['action'] = 'toHtml';
        $_GET['id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        require_once(__DIR__ . '/../../src/ajax.php');
        $result = ob_get_clean();
        $this->assertContains('"state":"ok"', $result);
    }

    /**
     * @throws Exception
     */
    public function testAjaxNoToken() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['target'] = 'EqLogic';
        $_GET['action'] = 'toHtml';
        $_GET['id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        require_once(__DIR__ . '/../../src/ajax.php');
        $result = ob_get_clean();
        $this->assertContains('"state":"error"', $result);
        $this->assertContains('invalide', $result);
    }

    /*
    public function testAjaxBadTarget() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['target'] = 'Impossible';
        $this->expectException(CoreException::class);
        require_once(__DIR__ . '/../../src/ajax.php');
    }
    */

    /**
     * @throws Exception
     */
    public function testAjaxBadAction() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
        $_GET['nextdom_token'] = AjaxHelper::getToken();
        $_GET['target'] = 'EqLogic';
        $_GET['action'] = 'sdoqkpdokqspdok';
        $_GET['id'] = 1;
        $_GET['version'] = 'dashboard';
        ob_start();
        require_once(__DIR__ . '/../../src/ajax.php');
        $result = ob_get_clean();
        $this->assertContains('"state":"error"', $result);
        $this->assertContains('inconnue', $result);
    }
}