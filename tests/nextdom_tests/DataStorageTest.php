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
    /*
            public function testDeleteData()
            {
                $this->dataStorage->deleteData('test');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('DELETE FROM', $actions[0]['content']['query']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
                $this->assertEquals(array('test'), $actions[0]['content']['data']);
            }

            public function testGetRawDataWithoutData()
            {
                $result = $this->dataStorage->getRawData('a_code');
                $this->assertNull($result);
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testGetRawDataWithData()
            {
                DB::setAnswer(array('data' => 'something'));
                $result = $this->dataStorage->getRawData('a_code');
                $this->assertEquals('something', $result);
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testIsDataExistsWithoutData()
            {
                $result = $this->dataStorage->isDataExists('a_code');
                $this->assertFalse($result);
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testIsDataExistsWithData()
            {
                DB::setAnswer(array('data' => 'something'));
                $result = $this->dataStorage->isDataExists('a_code');
                $this->assertTrue($result);
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testAddRawData()
            {
                $this->dataStorage->addRawData('a_code', 'something');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('INSERT INTO', $actions[0]['content']['query']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testUpdateRawData()
            {
                $this->dataStorage->updateRawData('a_code', 'something');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('UPDATE', $actions[0]['content']['query']);
                $this->assertContains($this->realTableName, $actions[0]['content']['query']);
            }

            public function testStoreRawDataWithoutData()
            {
                $this->dataStorage->storeRawData('a_code', 'something');
                $actions = MockedActions::get();
                $this->assertCount(2, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertEquals('query_execute', $actions[1]['action']);
                $this->assertContains('INSERT INTO', $actions[1]['content']['query']);
                $this->assertContains($this->realTableName, $actions[1]['content']['query']);
            }

            public function testStoreRawDataWithData()
            {
                DB::setAnswer(array('data' => 'something'));
                $this->dataStorage->storeRawData('a_code', 'something');
                $actions = MockedActions::get();
                $this->assertCount(2, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertEquals('query_execute', $actions[1]['action']);
                $this->assertContains('UPDATE', $actions[1]['content']['query']);
                $this->assertContains($this->realTableName, $actions[1]['content']['query']);
            }

            public function testStoreJsonData()
            {
                $this->dataStorage->storeJsonData('a_code', array('something' => 'is_that'));
                $actions = MockedActions::get();
                $this->assertCount(2, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertEquals('query_execute', $actions[1]['action']);
                $this->assertContains('INSERT INTO', $actions[1]['content']['query']);
                $this->assertEquals(array('a_code', '{"something":"is_that"}'), $actions[1]['content']['data']);
            }

            public function testGetJsonData()
            {
                DB::setAnswer(array('data' => '{"something":"is_that"}'));
                $result = $this->dataStorage->getJsonData('a_code');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('SELECT', $actions[0]['content']['query']);
                $this->assertEquals(array('something' => 'is_that'), $result);
            }

            public function testRemove()
            {
                $result = $this->dataStorage->remove('a_code');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('DELETE FROM ', $actions[0]['content']['query']);
            }

            public function testGetAllByPrefix()
            {
                $result = $this->dataStorage->getAllByPrefix('Precode');
                $actions = MockedActions::get();
                $this->assertCount(1, $actions);
                $this->assertEquals('query_execute', $actions[0]['action']);
                $this->assertContains('SELECT `data` ', $actions[0]['content']['query']);
                $this->assertContains('LIKE ?', $actions[0]['content']['query']);
            }
        */
}
