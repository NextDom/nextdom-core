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

require_once(__DIR__ . '/../../src/core.php');

class UserManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testLoginAdmin() {
        $user = UserManager::connect('admin', 'nextdom-test');
        $this->assertEquals($user->getLogin(), 'admin');
        $this->assertEquals($user->getProfils(), 'admin');
    }

    public function testLoginUser() {
        $user = UserManager::connect('simple', 'simple-test');
        $this->assertEquals($user->getLogin(), 'simple');
        $this->assertEquals($user->getProfils(), 'user');
    }

    public function testUserById() {
        $user = UserManager::byId(1);
        $this->assertEquals($user->getLogin(), 'admin');
        $this->assertEquals($user->getProfils(), 'admin');
    }

    public function testUserByLogin() {
        $user = UserManager::byId(1);
        $this->assertEquals($user->getLogin(), 'admin');
        $this->assertEquals($user->getProfils(), 'admin');
    }

    public function testByLoginAndPassword() {
        $user = UserManager::byLoginAndPassword('admin', hash('sha512', 'nextdom-test'));
        $this->assertEquals($user->getLogin(), 'admin');
        $this->assertEquals($user->getProfils(), 'admin');
    }

    public function testAll() {
        $users = UserManager::all();
        $this->assertEquals(2, count($users));
        $this->assertEquals($users[0]->getLogin(), 'admin');
        $this->assertEquals($users[1]->getProfils(), 'user');
    }

    public function testByProfils() {
        $users = UserManager::byProfils('user');
        $this->assertEquals(1, count($users));
        $this->assertEquals($users[0]->getLogin(), 'simple');
        $this->assertEquals($users[0]->getProfils(), 'user');
    }
}