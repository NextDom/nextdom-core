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

use NextDom\Ajax\WidgetAjax;
use NextDom\Managers\WidgetManager;
use NextDom\Model\Entity\Widget;
use NextDom\Helpers\DBHelper;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class WidgetAjaxTest extends BaseAjaxTest
{
    /** @var WidgetAjax */
    private $widgetAjax = null;

    public function setUp(): void
    {
        $this->widgetAjax = new WidgetAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
        DBHelper::exec("DELETE FROM `widget` WHERE id > 2");
    }

    public function testAll()
    {
        $this->connectAsAdmin();
        ob_start();
        $this->widgetAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertCount(2, $jsonResult['result']);
        $this->assertEquals('ActionTest', $jsonResult['result'][0]['name']);
        $this->assertEquals('<i class=\'icon jeedom2-tirelire2 icon_green\'></i>', $jsonResult['result'][1]['replace']['#_icon_on_#']);
    }

    public function testById()
    {
        $this->connectAsAdmin();
        $_GET['id'] = 1;
        ob_start();
        $this->widgetAjax->byId();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertCount(2, $jsonResult);
        $this->assertEquals(1, $jsonResult['result']['id']);
        $this->assertEquals('InfoTest', $jsonResult['result']['name']);
        $this->assertEquals('info', $jsonResult['result']['type']);
    }

    public function testGetPreview()
    {
        $_GET['id'] = 1;
        $this->connectAsAdmin();
        ob_start();
        $this->widgetAjax->getPreview();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertContains('"<i class=\'icon jeedom2-tirelire2 icon_green\'></i>"', $jsonResult['result']['html']);
    }

    public function testLoadConfigWithTests()
    {
        $this->connectAsAdmin();
        $_GET['template'] = 'cmd.info.numeric.tmplmultistate';
        ob_start();
        $this->widgetAjax->loadConfig();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertTrue($jsonResult['result']['test']);
        $this->assertCount(3, $jsonResult['result']['replace']);
        $this->assertEquals('time_widget', $jsonResult['result']['replace'][0]);
    }

    public function testLoadConfigWithoutTest()
    {
        $this->connectAsAdmin();
        $_GET['template'] = 'cmd.info.binary.door';
        ob_start();
        $this->widgetAjax->loadConfig();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertFalse($jsonResult['result']['test']);
    }

    public function testSave()
    {
        $_GET['widget'] = '{"name": "Test", "test":[]}';
        $this->connectAsAdmin();
        ob_start();
        $this->widgetAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('Test', $jsonResult['result']['name']);
        $this->assertEquals('action', $jsonResult['result']['type']);
        $savedId = $jsonResult['result']['id'];
        $createdWidget = WidgetManager::byId($savedId);
        $this->assertEquals('Test', $createdWidget->getName());
    }

    public function testRemove()
    {
        $widget = new Widget();
        $widget->setName('Test remove');
        $widget->save();
        $_GET['id'] = $widget->getId();
        $this->connectAsAdmin();
        ob_start();
        $this->widgetAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(2, WidgetManager::all());
    }

    /**
     * TODO
    public function testReplacement()
    {
        $this->connectAsAdmin();
        ob_start();
        $this->widgetAjax->replacement();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        var_dump($jsonResult);
    }
     */
}