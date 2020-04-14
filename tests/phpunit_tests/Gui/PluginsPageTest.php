<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class PluginsPageTest extends BasePageTest
{
    public function testPluginsPageAccess()
    {
        $this->goTo('index.php?v=d&p=dashboard');
        $this->crawler->filter('.treeview>a>i.fa-puzzle-piece')->click();
        sleep(1);
        $this->crawler->filter('.treeview-menu>.treeview>a>i.fa-code')->click();
        sleep(1);
        $this->crawler->filter('a[href="index.php?v=d&m=plugin4tests&p=plugin4tests"]')->click();
        sleep(6);
        $this->assertEquals('div', $this->crawler->filter('#add-eqlogic-btn')->getTagName());
        $this->checkJs();
    }

    public function testPluginPageRender()
    {
        $this->goTo('index.php?v=d&m=plugin4tests&p=plugin4tests');
        $this->assertCount(5, $this->crawler->filter('.eqLogicDisplayCard'));
        $this->assertEquals('div', $this->crawler->filter('#add-eqlogic-btn')->getTagName());
        $this->checkJs();
    }

    public function testPluginConfigPage()
    {
        $this->goTo('index.php?v=d&m=plugin4tests&p=plugin4tests');
        $this->crawler->filter('#config-btn')->click();
        sleep(8);
        $this->crawler->filter('.configKey[data-l1key="text_option"]')->sendKeys('Just a test');
        sleep(1);
        $this->crawler->filter('#bt_savePluginConfig')->click();
        sleep(2);
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->crawler->filter('#config-btn')->click();
        sleep(8);
        $this->assertEquals('Just a test', $this->crawler->filter('.configKey[data-l1key="text_option"]')->getAttribute('value'));
        $this->checkJs();
    }

    public function testDashboardWidget()
    {
        $this->goTo('index.php?v=d&p=dashboard');
        $this->assertEquals('div', $this->crawler->filter('#div_ob1')->getTagName());
        $this->assertEquals('TEST EQLOGIC', $this->crawler->filter('#div_ob1 .widget-name')->text());
        $this->assertContains('Cmd 1', $this->crawler->filter('[data-eqlogic_id="1"]')->text());
    }
}
