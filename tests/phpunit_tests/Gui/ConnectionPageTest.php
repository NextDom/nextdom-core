<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class ConnectionPageTest extends BasePageTest
{
    public function testConnectionPage()
    {
        $this->goTo('index.php?v=d&logout=1');
        $this->assertEquals('input', $this->crawler->filter('#login')->getTagName());
        $this->assertEquals('input', $this->crawler->filter('#password')->getTagName());
        $this->assertEquals('button', $this->crawler->filter('#submit')->getTagName());
        $this->assertEquals('input', $this->crawler->filter('#storeConnection')->getTagName());
        $this->checkJs();
    }

    public function testGoodConnectionAndButton()
    {
        $this->goTo('index.php?v=d&logout=1');
        $this->crawler->filter('#login')->sendKeys('admin');
        $this->crawler->filter('#password')->sendKeys('nextdom-test');
        $this->crawler->filter('#submit')->click();
        sleep(8);
        $this->refreshCrawler();
        $this->assertContains('Dashboard', $this->client->getTitle());
        $this->assertEquals('a', $this->crawler->filter('#search-toggle')->getTagName());
        $this->checkJs();
    }

    public function testBadConnectionAndHitEnter()
    {
        $this->goTo('index.php?v=d&logout=1');
        $this->crawler->filter('#login')->sendKeys('admin');
        $this->crawler->filter('#password')->sendKeys('If this password work, you\'re crazy!');
        $this->crawler->filter('#password')->sendKeys(\Facebook\WebDriver\WebDriverKeys::ENTER);
        sleep(5);
        $this->refreshCrawler();
        $this->assertContains('Connexion', $this->client->getTitle());
        $this->checkJs();
    }
}
