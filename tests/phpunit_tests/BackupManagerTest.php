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

use NextDom\Managers\BackupManager;

class BackupManagerTest extends PHPUnit_Framework_TestCase
{
    private $tmpDir;

    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . '/' . mt_rand();
        mkdir($this->tmpDir);
        touch($this->tmpDir . "/first.gz");
        sleep(1);
        touch($this->tmpDir . "/second.gz");
        sleep(1);
        touch($this->tmpDir . "/thrid.gz");
    }

    protected function tearDown()
    {
        system("rm -rf " . $this->tmpDir);
    }

    public function testGetBackupFileInfo()
    {
        $result = BackupManager::getBackupFileInfo($this->tmpDir, "oldest");
        $this->assertCount(3, $result);
        $this->assertEquals($this->tmpDir . "/first.gz", $result[0]["file"]);
        $this->assertEquals(0, $result[0]["size"]);
        $this->assertEquals($this->tmpDir . "/second.gz", $result[1]["file"]);
        $this->assertEquals(0, $result[0]["size"]);
        $this->assertEquals($this->tmpDir . "/thrid.gz", $result[2]["file"]);
        $this->assertEquals(0, $result[0]["size"]);

        $result = BackupManager::getBackupFileInfo($this->tmpDir, "newest");
        $this->assertCount(3, $result);
        $this->assertEquals($this->tmpDir . "/thrid.gz", $result[0]["file"]);
        $this->assertEquals($this->tmpDir . "/second.gz", $result[1]["file"]);
        $this->assertEquals($this->tmpDir . "/first.gz", $result[2]["file"]);
    }

    public function testGetLastBackupFilePath()
    {
        $this->assertEquals($this->tmpDir . "/first.gz", BackupManager::getLastBackupFilePath($this->tmpDir, "oldest"));
        $this->assertEquals($this->tmpDir . "/thrid.gz", BackupManager::getLastBackupFilePath($this->tmpDir, "newest"));
    }


    public function testGetBackupFilename()
    {
        $name = BackupManager::getBackupFilename("test");
        $this->assertStringStartsWith("backup-test-", $name);

        $name = BackupManager::getBackupFilename("test&check - test#fail - 'test' - \"test\"");
        $this->assertStringStartsWith("backup-testcheck__testfail__test__test-", $name);
    }

}
