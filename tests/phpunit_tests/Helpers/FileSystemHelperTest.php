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

use NextDom\Helpers\FileSystemHelper;

require_once(__DIR__ . '/../../../src/core.php');

define('TEST_BASE_PATH', '/tmp/nextdom_tests');
define('SECOND_TEST_BASE_PATH', '/tmp/nextdom_tests_S');

class FileSystemHelperTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        system('rm -fr ' . TEST_BASE_PATH);
        mkdir(TEST_BASE_PATH);
        system('mkdir ' . TEST_BASE_PATH . '/TestFolder1');
        system('mkdir ' . TEST_BASE_PATH . '/TestFolder2');
        system('mkdir ' . TEST_BASE_PATH . '/TestFolder2/TestRecurs');
        system('touch ' . TEST_BASE_PATH . '/TestFolder1/File1.txt');
        system('touch ' . TEST_BASE_PATH . '/TestFolder2/File2.php');
        system('touch ' . TEST_BASE_PATH . '/TestFolder2/File3.csv');
        system('touch ' . TEST_BASE_PATH . '/TestFile1.txt');
        system('touch ' . TEST_BASE_PATH . '/TestFile2.php');
        system('sudo chown www-data:www-data -R ' . TEST_BASE_PATH);
    }

    public function tearDown(): void {
        system('rm -fr ' . TEST_BASE_PATH);
        system('rm -fr ' . SECOND_TEST_BASE_PATH);
    }

    public function testGetTemplateFileContentOnExistingTemplate() {
        $template = FileSystemHelper::getTemplateFileContent('core', 'dashboard', 'eqLogic','');
        $this->assertStringContainsString('data-eqLogic_id="#id#"', $template);
        $this->assertStringContainsString('cmd refresh', $template);
    }

    public function testGetTemplateFileContentOnPluginTemplate() {
        $template = FileSystemHelper::getTemplateFileContent('core', 'dashboard', 'eqLogic', 'plugin4tests');
        $this->assertStringContainsString('data-eqLogic_id="#id#"', $template);
        $this->assertStringContainsString('My Plugin', $template);
    }

    public function testGetTemplateFileContentOnBadTemplate() {
        $template = FileSystemHelper::getTemplateFileContent('core', 'dashboard', 'eqLogicXXXX','');
        $this->assertEquals('', $template);
    }

    public function testLsSimple() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH);
        $this->assertContains('TestFile1.txt', $result);
        $this->assertContains('TestFolder2/', $result);
        $this->assertCount(4, $result);
    }

    public function testLsFiltered() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*.php');
        $this->assertContains('TestFile2.php', $result);
        $this->assertCount(1, $result);
    }

    public function testLsRecursive() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*', true);
        $this->assertContains('TestFile1.txt', $result);
        $this->assertContains('TestFolder2/', $result);
        $this->assertContains('TestFolder2/TestRecurs/', $result);
        $this->assertContains('TestFolder2/File3.csv', $result);
        $this->assertCount(8, $result);
    }

    public function testLsFolders() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*', false, ['folders']);
        $this->assertContains('TestFolder1/', $result);
        $this->assertContains('TestFolder2/', $result);
        $this->assertCount(2, $result);
    }

    public function testLsFiles() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*', false, ['files']);
        $this->assertContains('TestFile1.txt', $result);
        $this->assertContains('TestFile2.php', $result);
        $this->assertCount(2, $result);
    }

    public function testLsFoldersRecursive() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*', true, ['folders']);
        $this->assertContains('TestFolder1/', $result);
        $this->assertContains('TestFolder2/', $result);
        $this->assertNotContains('TestFolder2/File3.csv', $result);
        $this->assertContains('TestFolder2/TestRecurs/', $result);
        $this->assertCount(3, $result);
    }

    public function testLsFilesRecursive() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*', true, ['files']);
        $this->assertContains('TestFile2.php', $result);
        $this->assertContains('TestFolder2/File3.csv', $result);
        $this->assertNotContains('TestFolder2/TestRecurs/', $result);
        $this->assertCount(5, $result);
    }

    public function testLsFilteredRecursive() {
        $result = FileSystemHelper::ls(TEST_BASE_PATH, '*.php', true);
        $this->assertContains('TestFile2.php', $result);
        $this->assertContains('TestFolder2/File2.php', $result);
        $this->assertCount(2, $result);
    }

    public function testRcopy() {
        $result = FileSystemHelper::rcopy(TEST_BASE_PATH, SECOND_TEST_BASE_PATH);
        $this->assertTrue($result);
        $this->assertFileExists(TEST_BASE_PATH . '/TestFolder2/File3.csv');
        $this->assertFileExists(SECOND_TEST_BASE_PATH . '/TestFolder2/File3.csv');
    }

    public function testRmove() {
        $result = FileSystemHelper::rmove(TEST_BASE_PATH, SECOND_TEST_BASE_PATH);
        $this->assertTrue($result);
        $this->assertFileNotExists(TEST_BASE_PATH . '/TestFolder2/File3.csv');
        $this->assertFileExists(SECOND_TEST_BASE_PATH . '/TestFolder2/File3.csv');
    }

    public function testRrmdir() {
        $result = FileSystemHelper::rrmdir(TEST_BASE_PATH);
        $this->assertTrue($result);
        $this->assertFileNotExists(TEST_BASE_PATH);
    }
}