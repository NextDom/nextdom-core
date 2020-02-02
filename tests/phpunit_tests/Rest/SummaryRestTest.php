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

use NextDom\Managers\UserManager;
use NextDom\Rest\Authenticator;
use NextDom\Rest\SummaryRest;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../../../src/core.php');

class SummaryRestTest extends PHPUnit\Framework\TestCase
{
    /** @var Authenticator */
    private $authenticator;

    public function setUp(): void
    {
        $admin = UserManager::byLogin('admin');
        $admin->setOptions('defaultDashboardObject', null);
        $admin->save();

        $tempAuthenticator = Authenticator::init(new Request());
        $tempAuthenticator->createTokenForUser($admin);
        $testRequest = new Request();
        $testRequest->headers->set('X-AUTH-TOKEN', $admin->getOptions('token'));
        $this->authenticator = Authenticator::init($testRequest);
        $this->authenticator->checkSendedToken();
    }

    public function testGetDefaultTreeWithDefaultDefined()
    {
        $admin = UserManager::byLogin('admin');
        $admin->setOptions('defaultDashboardObject', 2);
        $admin->save();
        // Reload user
        $this->authenticator->checkSendedToken();

        $result = SummaryRest::getDefaultRoomTree();
        $this->assertEquals('Second Room', $result['name']);
        $this->assertArrayHasKey('children', $result);
        $this->assertCount(1, $result['children']);
    }

    public function testGetDefaultTreeWithoutDefaultDefined()
    {
        $result = SummaryRest::getDefaultRoomTree();
        $this->assertEquals('My Room', $result['name']);
        $this->assertArrayHasKey('eqLogics', $result);
        $this->assertCount(1, $result['eqLogics']);
        $this->assertCount(2, $result['eqLogics'][0]['cmds']);
    }

    public function testGetRoomTree()
    {
        $result = SummaryRest::getRoomTree(1);
        $this->assertEquals('My Room', $result['name']);
        $this->assertArrayHasKey('eqLogics', $result);
        $this->assertCount(1, $result['eqLogics']);
        $this->assertCount(2, $result['eqLogics'][0]['cmds']);
    }
}
