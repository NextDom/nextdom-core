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

class AjaxCacheTest extends AjaxBase
{
    private $ajaxFile = 'cache';
    
    public function testWithout() {
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'flush']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
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

    public function testFlushAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'flush']);
        $this->assertContains('"state":"ok"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testFlushAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'flush']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        // TODO : Change
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCleanAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'clean']);
        $this->assertContains('"state":"ok"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCleanAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'clean']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        // TODO : Change
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testStatsAsAdmin() {
        $this->connectAsAdmin();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'stats']);
        $this->assertContains('"state":"ok"', (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testStatsAsUser() {
        $this->connectAsUser();
        $result = $this->getAjaxQueryWithTokenResult($this->ajaxFile, ['action' => 'stats']);
        $this->assertContains('"state":"error"', (string) $result->getBody());
        // TODO : Change
        $this->assertEquals(200, $result->getStatusCode());
    }
}