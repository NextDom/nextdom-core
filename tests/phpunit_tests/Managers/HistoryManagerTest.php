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
use NextDom\Managers\HistoryManager;
use NextDom\Model\Entity\History;

require_once(__DIR__ . '/../../../src/core.php');

class HistoryManagerTest extends PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        DBHelper::exec('TRUNCATE history');
        DBHelper::exec('TRUNCATE historyArch');
    }

    public function testArchive()
    {
        $start = date('Y-m-d H:i:s');
        // Binary data
        for ($i = 0; $i < 50; ++$i) {
            $historyDate = date('Y-m-d H:i:s', strtotime('-' . ($i * 30) . ' minutes', strtotime($start)));
            $h = new History();
            $h->setDatetime($historyDate);
            $h->setCmd_id(1);
            $h->setValue(random_int(0, 1));
            $h->save();
        }
        // Numeric data
        for ($i = 0; $i < 50; ++$i) {
            $historyDate = date('Y-m-d H:i:s', strtotime('-' . ($i * 30) . ' minutes', strtotime($start)));
            $h = new History();
            $h->setDatetime($historyDate);
            $h->setCmd_id(4);
            $h->setValue(mt_rand(20, 30));
            $h->save();
        }
        HistoryManager::archive();
        $this->assertFalse(DBHelper::getOne('SELECT * FROM history WHERE cmd_id = 1'));
        $this->assertEquals(4, DBHelper::getOne('SELECT * FROM history WHERE cmd_id = 4')['cmd_id']);
        $this->assertEquals(1, DBHelper::getOne('SELECT * FROM historyArch WHERE cmd_id = 1')['cmd_id']);
        $this->assertEquals(4, DBHelper::getOne('SELECT * FROM historyArch WHERE cmd_id = 4')['cmd_id']);
    }
}