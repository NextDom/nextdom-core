<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class FirstUsePageTest extends BasePageTest
{
    public function setUp(): void
    {
        exec('bash tests/load_fixtures.sh --reset --firstuse');
    }

    public function tearDown(): void
    {
        exec('bash tests/load_fixtures.sh --nofirstuse');
    }

    public function testShortcuts()
    {
        $this->goTo('');
        $this->refreshCrawler();
        $this->crawler->filter('a[href="#step-1"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#toStep2')->getTagName());
        $this->crawler->filter('a[href="#step-2"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#toStep3')->getTagName());
        $this->crawler->filter('a[href="#step-3"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#toStep4')->getTagName());
        $this->crawler->filter('a[href="#step-4"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#toStep5')->getTagName());
        $this->crawler->filter('a[href="#step-5"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#toStep6')->getTagName());
        $this->crawler->filter('a[href="#step-6"]')->click();
        $this->assertEquals('button', $this->crawler->filter('#finishConf')->getTagName());
        $this->checkJs();
    }

    public function testFullProcess()
    {
        $this->goTo('');
        $this->refreshCrawler();
        $this->crawler->filter('#toStep2')->click();
        sleep(3);
        $this->crawler->filter('#in_change_password')->sendKeys('nextdom-test');
        $this->crawler->filter('#in_change_password_confirm')->sendKeys('nextdom-test');
        $this->crawler->filter('#toStep3')->click();
        sleep(4);
        $this->crawler->filter('#skipStep4')->click();
        sleep(4);
        $this->crawler->filter('#light-nextdom')->click();
        $this->crawler->filter('#toStep5')->click();
        sleep(4);
        $this->crawler->filter('#flat')->click();
        $this->crawler->filter('#toStep6')->click();
        sleep(4);
        $this->crawler->filter('#finishConf')->click();
        sleep(8);
        $this->refreshCrawler();
        $this->assertContains('Dashboard', $this->client->getTitle());
        $this->assertEquals('a', $this->crawler->filter('#search-toggle')->getTagName());
        $this->checkJs();
    }
}
