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
use NextDom\Managers\ThemeManager;
use NextDom\Managers\UpdateManager;

require_once(__DIR__ . '/../../../src/core.php');

class ThemeManagerTest extends PHPUnit\Framework\TestCase
{
    public function testGetWidgetThemes() {
        $result = ThemeManager::getWidgetThemes();
        $this->assertCount(3, $result);
        $this->assertEquals('/views/templates/dashboard/themes/adminlte/adminlte.png', $result[0]->getIcon());
        $this->assertEquals('flat', $result[1]->getCode());
    }
}