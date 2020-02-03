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

use NextDom\Managers\InteractQueryManager;

require_once(__DIR__ . '/../../../src/core.php');

class InteractQueryManagerTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function testTryToReply()
    {
        $result = InteractQueryManager::tryToReply('on room');
        $this->assertEquals('Done', $result['reply']);
        $result = InteractQueryManager::tryToReply('light chamber');
        $this->assertEquals('Done', $result['reply']);
        $result = InteractQueryManager::tryToReply('on hall');
        $this->assertEquals('Done', $result['reply']);
        $result = InteractQueryManager::tryToReply('light hall');
        $this->assertEquals('Done', $result['reply']);
    }

    public function testTryToReplyBadInteract()
    {
        $result = InteractQueryManager::tryToReply('Impossible to reply');
        $this->assertContains('compr', $result['reply']);
    }
}