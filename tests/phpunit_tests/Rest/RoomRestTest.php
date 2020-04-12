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
use NextDom\Rest\RoomRest;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../../../src/core.php');

class RoomRestTest extends PHPUnit\Framework\TestCase
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

        $result = RoomRest::getDefaultTree();
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Second Room', $result['name']);
        $this->assertCount(1, $result['children']);
        $this->assertEquals('Chamber', $result['children'][0]['name']);
    }

    public function testGetDefaultTreeWithoutDefaultDefined()
    {
        $result = RoomRest::getDefaultTree();
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('My Room', $result['name']);
        $this->assertArrayNotHasKey('children', $result);
    }

    public function testGetTreeWithoutChild()
    {
        $result = RoomRest::getTree(1);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('My Room', $result['name']);
        $this->assertArrayNotHasKey('children', $result);
    }

    public function testGetTreeWithChildren()
    {
        $result = RoomRest::getTree(2);
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Second Room', $result['name']);
        $this->assertCount(1, $result['children']);
        $this->assertEquals('Chamber', $result['children'][0]['name']);
    }

    public function testGet()
    {
        $result = RoomRest::get(1);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('My Room', $result['name']);
        $this->assertArrayNotHasKey('children', $result);
    }

    public function testGetRoots()
    {
        $result = RoomRest::getRoots();
        $this->assertArrayHasKey('children', $result);
        $this->assertCount(3, $result['children']);
        $this->assertEquals('My Room', $result['children'][0]['name']);
        $this->assertEquals('Second Room', $result['children'][1]['name']);
    }

    public function testGetRoomSummary()
    {
        $result = RoomRest::getRoomSummary(2);
        // TODO: Need items
        $this->assertEquals('<span class="objectSummary2" data-version="desktop"></span>', $result);
    }

    public function testGetRoomsSummary()
    {
        $result = RoomRest::getRoomsSummary('1;2');
        // TODO: Need items
        $this->assertEquals('<span class="objectSummary1" data-version="desktop"></span>', $result[1]);
        $this->assertEquals('<span class="objectSummary2" data-version="desktop"></span>', $result[2]);
    }
}
