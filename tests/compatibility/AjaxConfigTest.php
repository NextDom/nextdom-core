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

require_once('libs/AjaxBase.php');

class AjaxConfigTest extends AjaxBase
{
    private $ajaxFile = 'config';

    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'genApiKey']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testImpossibleActionAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'with-is-not-possible']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGenApiKeyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'genApiKey']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGenApiKeyAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'genApiKey']);
        $this->assertEquals(32, strlen(json_decode((string) $result->getBody(), true)['result']));
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetKeyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'getKey']);
        $this->assertContains('Aucune clef demand', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAddKeyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'addKey']);
        $this->assertContains('401 - ', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testAddKeyAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'addKey']);
        $this->assertContains('"state":"ok"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testRemoveKeyAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'removeKey']);
        $this->assertContains('Aucune clef demand', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
        // TODO : Pas normal ça
    }


}