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

require_once('BaseAjaxTest.php');

class MessageAjaxTest extends BaseAjaxTest
{
    /** @var MessageAjax */
    private $messageAjax = null;

    public function setUp(): void
    {
        $this->messageAjax = new MessageAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        DBHELPER::exec(
            "DELETE FROM message;
                    INSERT INTO `message` VALUES (1,'2019-05-03 22:00:03','newUpdate','update','De nouvelles mises à jour sont disponibles : nextdom,openzwave','');
                    INSERT INTO `message` VALUES (2,'2019-05-04 00:00:02','scenario::sKEbQHaqQGiOwgvu4sknYQUQqFurIUks','scenario','Une commande du scénario : [Couloir][Couloir][Lumière couloir] est introuvable','');
                    INSERT INTO `message` VALUES (3,'2019-05-05 22:00:02','plugin4tests::Zx1FKbfyTf3jW6ux8TLpSmQAbJSvCIt1','plugin4tests','Message from a plugin','');");

    }

    public function testNbMessage()
    {
        ob_start();
        $this->messageAjax->nbMessage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertStringContainsString('3', $result);
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

    public function testRemoveMessage()
    {
        $_GET['id'] = 1;
        ob_start();
        $this->messageAjax->removeMessage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(2, MessageManager::all());
    }

    public function testClearMessage()
    {
        ob_start();
        $this->messageAjax->clearMessage();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(0, MessageManager::all());
    }
}