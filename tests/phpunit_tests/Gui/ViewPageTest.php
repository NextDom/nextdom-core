<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class ViewPageTest extends BasePageTest
{
    public function testViewPage()
    {
        $this->goTo('index.php?v=d&p=view');
        $this->assertCount(2, $this->crawler->filter('div[data-eqlogic_id="4"]'));
        $this->assertEquals('i', $this->crawler->filter('#bt_editViewWidgetOrder')->getTagName());

        $this->crawler->filter('a[href="index.php?v=d&p=view_edit&view_id=1"]')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_addView')->getTagName());

        $this->checkJs();
    }

    public function testViewEditPage()
    {
        $this->goTo('index.php?v=d&p=view_edit&view_id=1');

        $this->crawler->filter('#bt_editView')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_saveConfigureView')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#bt_addviewZone')->click();
        sleep(3);
        $this->assertEquals('select', $this->crawler->filter('#sel_addEditviewZoneType')->getTagName());
        $this->crawler->filter('#md_addEditviewZone .close')->click();
        sleep(3);

        $this->crawler->filter('.bt_addViewWidget')->click();
        sleep(3);
        $this->assertEquals('td', $this->crawler->filter('.mod_insertEqLogicValue_object')->getTagName());
        $this->crawler->filter('div[aria-describedby="mod_insertEqLogicValue"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->checkJs();
    }
}
