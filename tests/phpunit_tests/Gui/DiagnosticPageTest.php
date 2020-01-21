<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class DiagnosticPageTest extends BasePageTest
{
    public function testHealthPage()
    {
        $this->goTo('index.php?v=d&p=health');
        sleep(4);
        $this->assertCount(22, $this->crawler->filter('.info-box-content'));
        $this->crawler->filter('a[href="#div_Plugins"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#div_Plugins')->getTagName());
        $this->crawler->filter('#bt_benchmarkNextDom')->click();
        sleep(15);
        $this->assertContains('cache_write_5000', $this->crawler->filter('#md_modal .content')->text());
        $this->checkJs();
    }

    public function testCronPage()
    {
        $this->goTo('index.php?v=d&p=cron');
        $this->assertEquals('a', $this->crawler->filter('#bt_addCron')->getTagName());
        $this->assertEquals('a', $this->crawler->filter('#bt_save')->getTagName());
        $this->checkJs();
    }

    public function testEqAnalysePage()
    {
        $this->goTo('index.php?v=d&p=eqAnalyse');
        sleep(7);
        // Don't know why on this page
        $this->invisibleClick('a[href="#battery"]');
        $this->assertEquals('div', $this->crawler->filter('#battery')->getTagName());
        $this->invisibleClick('a[href="#alertEqlogic"]');
        $this->assertEquals('div', $this->crawler->filter('#alertEqlogic')->getTagName());
        $this->invisibleClick('a[href="#actionCmd"]');
        $this->assertEquals('div', $this->crawler->filter('#actionCmd')->getTagName());
        $this->invisibleClick('a[href="#alertCmd"]');
        $this->assertEquals('div', $this->crawler->filter('#alertCmd')->getTagName());
        $this->invisibleClick('a[href="#deadCmd"]');
        $this->assertEquals('div', $this->crawler->filter('#deadCmd')->getTagName());
        $this->checkJs();
    }

    public function testHistoryPage()
    {
        $this->goTo('index.php?v=d&p=history');
        $this->assertEquals('input', $this->crawler->filter('#in_calculHistory')->getTagName());
        $this->crawler->filter('#bt_openCmdHistoryConfigure')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_cmdConfigureCmdHistoryApply')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->checkJs();
    }

    public function testTimelinePage()
    {
        $this->goTo('index.php?v=d&p=timeline');
        $this->crawler->filter('#bt_configureTimelineScenario')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_saveSummaryScenario')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->crawler->filter('#bt_configureTimelineCommand')->click();
        sleep(3);
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->checkJs();
    }

    public function testReportPage()
    {
        $this->goTo('index.php?v=d&p=report');
        $this->assertEquals('ul', $this->crawler->filter('#ul_report')->getTagName());
        $this->assertEquals('div', $this->crawler->filter('#div_imgreport')->getTagName());
        $this->checkJs();
    }

    public function testLogPage()
    {
        $this->goTo('index.php?v=d&p=log');
        $this->assertEquals('a', $this->crawler->filter('#bt_removeAllLog')->getTagName());
        $this->assertContains('Vider', $this->crawler->filter('#bt_clearLog')->text());
        $this->checkJs();
    }
}
