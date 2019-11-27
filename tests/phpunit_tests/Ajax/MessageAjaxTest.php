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

use NextDom\Ajax\MessageAjax;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\MessageManager;
use NextDom\Model\Entity\Message;

require_once('BaseAjaxTest.php');

class MessageAjaxTest extends BaseAjaxTest
{
    /** @var MessageAjax */
    private $messageAjax = null;

    public function setUp()
    {
        $this->messageAjax = new MessageAjax();
    }

    public function tearDown()
    {
        $this->cleanGetParams();
        DBHelper::exec('DELETE FROM message WHERE id > 3');
    }

    public function testNbMessage()
    {
        ob_start();
        $this->messageAjax->nbMessage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('3', $result);
    }

    public function testAll()
    {
        ob_start();
        $this->messageAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(3, $jsonResult['result']);
        $this->assertEquals('plugin4tests', $jsonResult['result'][0]['plugin']);
    }

    public function testAllFromPlugin()
    {
        $_GET['plugin'] = 'plugin4tests';
        ob_start();
        $this->messageAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(1, $jsonResult['result']);
        $this->assertEquals('Message from a plugin', $jsonResult['result'][0]['message']);
    }

    public function testRemoveMessage() {
        $message = new Message();
        $message->setMessage('test message');
        $message->setDate(date('Y-m-d H:i:s'));
        $message->setPlugin('plugin4tests');
        $message->save();

        $_GET['id'] = $message->getId();
        ob_start();
        $this->messageAjax->removeMessage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(3, MessageManager::all());
    }
}