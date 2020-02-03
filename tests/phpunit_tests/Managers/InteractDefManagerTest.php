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

use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\InteractDefManager;

require_once(__DIR__ . '/../../../src/core.php');

class InteractDefManagerTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function testListGroup() {
        $groupsList = InteractDefManager::listGroup();
        $this->assertCount(2, $groupsList);
        $this->assertEquals('Intermediate', $groupsList[0]['group']);
        $this->assertEquals('Simple', $groupsList[1]['group']);
    }

    public function testListGroupFiltered() {
        $groupsList = InteractDefManager::listGroup('im');
        $this->assertCount(1, $groupsList);
        $this->assertEquals('Simple', $groupsList[0]['group']);
    }
}