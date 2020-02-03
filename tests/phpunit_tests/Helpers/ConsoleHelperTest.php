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
use NextDom\Helpers\ConsoleHelper;

class ConsoleHelperTest extends PHPUnit\Framework\TestCase
{
    public function testTitleWithoutEnding()
    {
        ob_start();
        ConsoleHelper::title('Test title', false);
        $result = ob_get_clean();
        $this->assertEquals("[ Test title ]\n", $result);
    }

    public function testTitleWithEnding()
    {
        ob_start();
        ConsoleHelper::title('Test title', true);
        $result = ob_get_clean();
        $this->assertEquals("[ / Test title ]\n", $result);
    }

    public function testSubTitle()
    {
        ob_start();
        ConsoleHelper::subTitle('Test subtitle');
        $result = ob_get_clean();
        $this->assertContains('*** Test subtitle ***', $result);
    }

    public function testStep()
    {
        ob_start();
        ConsoleHelper::step('Test step');
        $result = ob_get_clean();
        $this->assertEquals('Test step... ', $result);
    }

    public function testStepLine()
    {
        ob_start();
        ConsoleHelper::stepLine('Test step');
        $result = ob_get_clean();
        $this->assertEquals("Test step...\n", $result);
    }

    public function testProcess()
    {
        ob_start();
        ConsoleHelper::process('Process');
        $result = ob_get_clean();
        $this->assertEquals("...Process\n", $result);
    }

    public function testEnter()
    {
        ob_start();
        ConsoleHelper::enter();
        $result = ob_get_clean();
        $this->assertEquals("\n", $result);
    }

    public function testOk()
    {
        ob_start();
        ConsoleHelper::ok();
        $result = ob_get_clean();
        $this->assertEquals(" OK\n", $result);
    }

    public function testNok()
    {
        ob_start();
        ConsoleHelper::nok();
        $result = ob_get_clean();
        $this->assertEquals(" Failure\n", $result);
    }

    public function testError()
    {
        $testException = new CoreException('This is an exception');
        ob_start();
        ConsoleHelper::error($testException);
        $result = ob_get_clean();
        $this->assertContains('*** ERROR *** This is an exception', $result);
        $this->assertContains('*** TRACE *** #', $result);
    }
}
