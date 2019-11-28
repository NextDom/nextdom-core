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

use NextDom\Helpers\DBHelper;
use NextDom\Managers\UpdateManager;

require_once(__DIR__ . '/../../src/core.php');

class UpdateManagerTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
        // Keep only core
        DBHelper::exec('DELETE FROM `update` WHERE id > 1');
    }

    public function setUp()
    {

    }
/*
    public function testFindNewUpdateObject()
    {
        UpdateManager::findNewUpdateObject();
        $allUpdates = UpdateManager::all();
        $this->assertCount(2, $allUpdates);
        $this->assertEquals('plugin4tests', $allUpdates[0]->getName());
    }

    public function testGetRepoDataFromNameGithHub()
    {
        $repoData = UpdateManager::getRepoDataFromName('github');
        $this->assertEquals('RepoGitHub', $repoData['className']);
        $this->assertEquals('\NextDom\Repo\RepoGitHub', $repoData['phpClass']);
    }

    public function testGetRepoDataFromNameApt()
    {
        $repoData = UpdateManager::getRepoDataFromName('apt');
        $this->assertEquals('RepoApt', $repoData['className']);
        $this->assertEquals('\NextDom\Repo\RepoApt', $repoData['phpClass']);
    }

    public function testGetRepoDataFromNameUnknown()
    {
        $repoData = UpdateManager::getRepoDataFromName('unknown');
        $this->assertCount(0, array_keys($repoData));
    }

    public function testRepoByIdGitHub()
    {
        $repoData = UpdateManager::repoById('github');
        $this->assertArrayHasKey('configuration', $repoData);
        $this->assertArrayHasKey('scope', $repoData);
        $this->assertEquals('Github', $repoData['name']);
    }

    public function testByTypeCore()
    {
        UpdateManager::findNewUpdateObject();
        $updatesByType = UpdateManager::byType('core');
        $this->assertCount(1, $updatesByType);
        $this->assertEquals('nextdom', $updatesByType[0]->getName());
    }

    public function testByTypePlugin()
    {
        UpdateManager::findNewUpdateObject();
        $updatesByType = UpdateManager::byType('plugin');
        $this->assertCount(1, $updatesByType);
        $this->assertEquals('plugin4tests', $updatesByType[0]->getName());
    }

    public function testAll()
    {
        UpdateManager::findNewUpdateObject();
        $allUpdates = UpdateManager::all();
        $this->assertCount(2, $allUpdates);
    }

    public function testAllWithFilterCore()
    {
        UpdateManager::findNewUpdateObject();
        $updatesByType = UpdateManager::all('core');
        $this->assertCount(1, $updatesByType);
        $this->assertEquals('nextdom', $updatesByType[0]->getName());
    }

    public function testAllWithFilterPlugin()
    {
        UpdateManager::findNewUpdateObject();
        $updatesByType = UpdateManager::all('plugin');
        $this->assertCount(1, $updatesByType);
        $this->assertEquals('plugin4tests', $updatesByType[0]->getName());
    }
*/
    public function testListRepo()
    {
        $repoList = UpdateManager::listRepo();
        $this->assertCount(7, $repoList);
        $this->assertEquals('Apt', $repoList['apt']['name']);
        $this->assertEquals('\NextDom\Repo\RepoNextDom', $repoList['nextdom']['class']);
        $this->assertEquals('input', $repoList['samba']['configuration']['configuration']['backup::ip']['type']);
    }
}