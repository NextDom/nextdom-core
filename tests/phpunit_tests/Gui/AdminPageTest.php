<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class AdminPageTest extends BasePageTest
{
    public function testUsersPage()
    {
        $this->goTo('index.php?v=d&p=users');
        $this->assertEquals('div', $this->crawler->filter('#md_newUser')->getTagName());
        $this->assertEquals('Ajouter un utilisateur', $this->crawler->filter('#bt_addUser')->text());
        $this->checkJs();
    }

    public function testApiPage()
    {
        $this->goTo('index.php?v=d&p=api');
        $this->assertEquals('a', $this->crawler->filter('.bt_regenerate_api')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_saveapi')->text());
        $this->checkJs();
    }

    public function testNetworkPage()
    {
        $this->goTo('index.php?v=d&p=network');
        $this->assertEquals('input', $this->crawler->filter('#networkmanagement')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savenetwork')->text());
        $this->checkJs();
    }

    public function testSecurityPage()
    {
        $this->goTo('index.php?v=d&p=security');
        $this->assertContains('Librairie LDAP', $this->crawler->text());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savesecurity')->text());
        $this->checkJs();
    }

    public function testCachePage()
    {
        $this->goTo('index.php?v=d&p=cache');
        $this->assertEquals('Retour', $this->crawler->selectLink('Retour')->text());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savecache')->text());
        $this->assertContains('Vider', $this->crawler->filter('#bt_flushCache')->text());
        $this->checkJs();
    }

    public function testServicePage()
    {
        $this->goTo('index.php?v=d&p=services');
        $this->assertEquals('a', $this->crawler->filter('.testRepoConnection')->getTagName());
        $this->assertEquals('Retour', $this->crawler->selectLink('Retour')->text());
        $this->assertContains('Sauvegarder', $this->crawler->filter('#bt_saveservices')->text());
        $this->checkJs();
    }
}
