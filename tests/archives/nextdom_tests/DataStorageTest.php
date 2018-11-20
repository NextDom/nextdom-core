<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use NextDom\Helpers\DataStorage;

require_once(__DIR__ . '/../../core/class/DB.class.php');

class DataStorageTest extends TestCase
{
    public $dataStorage;

    public $dbConnection;

    private $testTableName = 'test_DB';

    private $realTableName;

    protected function setUp()
    {
        global $CONFIG;
        $CONFIG = [];
        $CONFIG['db'] = [];
        $CONFIG['db']['host'] = 'localhost';
        $CONFIG['db']['port'] = '3306';
        $CONFIG['db']['dbname'] = 'nextdom_test';
        $CONFIG['db']['username'] = 'nextdom_test';
        $CONFIG['db']['password'] = 'nextdom_test';
        $this->dbConnection = DB::getConnection();
        $this->dataStorage = new DataStorage($this->testTableName);
        $this->realTableName = 'data_' . $this->testTableName;
    }

    protected function tearDown()
    {
        $statement = $this->dbConnection->prepare('DROP TABLE ' . $this->realTableName);
        $statement->execute();
    }

    public function testIsDataTableExistsWithEmptyDatabase()
    {
        $this->assertFalse($this->dataStorage->isDataTableExists());
    }

    public function testIsDataTableExistsWithCreatedTable()
    {
        $statement = $this->dbConnection->prepare("CREATE TABLE `" . $this->realTableName . "` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `code` VARCHAR(256) NOT NULL, `data` TEXT NULL)");
        $statement->execute();
        $this->assertTrue($this->dataStorage->isDataTableExists());
    }

    public function testCreateDataTableWithEmptyDatabase()
    {
        $this->dataStorage->createDataTable();
        $this->assertTrue($this->dataStorage->isDataTableExists());
    }

    public function testDropDataTable()
    {
        $statement = $this->dbConnection->prepare("CREATE TABLE `" . $this->realTableName . "` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `code` VARCHAR(256) NOT NULL, `data` TEXT NULL)");
        $statement->execute();
        $this->dataStorage->dropDataTable();
        $this->assertFalse($this->dataStorage->isDataTableExists());
    }
}
