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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Helpers\PrepareView;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Note;

require_once(__DIR__ . '/../../../src/core.php');

class PrepareViewTest extends PHPUnit\Framework\TestCase
{
    /** @var PrepareView */
    private $prepareView = null;

    public function setUp(): void
    {
        $this->prepareView = new PrepareView();
    }

    public function tearDown(): void
    {
        DBHelper::exec("UPDATE user SET password = '" . Utils::sha512('nextdom-test') . "' WHERE login = 'admin'");
        DBHelper::exec("DELETE FROM config WHERE `key` = 'nextdom::firstUse'");
    }

    public function testFirstUseAlreadyShowedToShowWithDefaultPassword() {
        DBHelper::exec("UPDATE user SET password = '" . Utils::sha512('admin') . "' WHERE login = 'admin'");
        ConfigManager::save('nextdom::firstUse', 1);
        $this->prepareView->initConfig();
        $this->assertFalse($this->prepareView->firstUseAlreadyShowed());
        $this->assertEquals(1, ConfigManager::byKey('nextdom::firstUse'));
    }

    public function testFirstUseAlreadyShowedToShowWithOtherPassword() {
        DBHelper::exec("UPDATE user SET password = '" . Utils::sha512('nextdom-test') . "' WHERE login = 'admin'");
        ConfigManager::save('nextdom::firstUse', 1);
        $this->prepareView->initConfig();
        $this->assertTrue($this->prepareView->firstUseAlreadyShowed());
        $this->assertEquals(0, ConfigManager::byKey('nextdom::firstUse'));
    }

    public function testFirstUseAlreadyShowedSkipWithDefaultPassword() {
        DBHelper::exec("UPDATE user SET password = '" . Utils::sha512('admin') . "' WHERE login = 'admin'");
        ConfigManager::save('nextdom::firstUse', 0);
        $this->prepareView->initConfig();
        $this->assertTrue($this->prepareView->firstUseAlreadyShowed());
        $this->assertEquals(0, ConfigManager::byKey('nextdom::firstUse'));
    }

    public function testFirstUseAlreadyShowedSkipWithOtherPassword() {
        DBHelper::exec("UPDATE user SET password = '" . Utils::sha512('nextdom-test') . "' WHERE login = 'admin'");
        ConfigManager::save('nextdom::firstUse', 0);
        $this->prepareView->initConfig();
        $this->assertTrue($this->prepareView->firstUseAlreadyShowed());
        $this->assertEquals(0, ConfigManager::byKey('nextdom::firstUse'));
    }


}