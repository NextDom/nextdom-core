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
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Config;

require_once(__DIR__ . '/../../src/core.php');

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public $testEqLogicId = null;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
        DBHelper::exec('DELETE FROM ' . ConfigManager::DB_CLASS_NAME. ' WHERE `key` = "Just a test"');
    }

    public function testGettersAndSetters()
    {
        $config = new Config();
        $config->setKey('Just a test');
        $config->setPlugin('plugin4tests');
        $config->setValue('A value');
        DBHelper::save($config);
        $configResult = ConfigManager::byKey('Just a test', 'plugin4tests');
        $this->assertEquals('A value', $configResult);
    }
}