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

use NextDom\Enums\Enum;

require_once(__DIR__ . '/../../../src/core.php');

class EnumForTests extends Enum
{
    const STATE_UP = 0;
    const STATE_DOWN = 1;
    const STATE_UNKNOWN = -1;
}

;

class EnumTest extends PHPUnit\Framework\TestCase
{
    public function testExistsTrue()
    {
        $this->assertTrue(EnumForTests::exists('STATE_UP'));
    }

    public function testExistsFalse()
    {
        $this->assertTrue(EnumForTests::exists('IMPOSSIBLE_STATE'));
    }

    public function testGetConstants()
    {
        $constants = EnumForTests::getConstants();
        $this->assertCount(3, $constants);
        $this->assertEquals(0, $constants['STATE_UP']);
        $this->assertEquals(-1, $constants['STATE_UNKNOWN']);
    }

    public function testGetValues()
    {
        $values = EnumForTests::getValues();
        $this->assertCount(3, $values);
        $this->assertEquals(0, $values[0]);
        $this->assertEquals(-1, $values[2]);
    }
}