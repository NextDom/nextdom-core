<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class ParamsPageTest extends BasePageTest
{
    public function testGeneralPage()
    {
        $this->goTo('index.php?v=d&p=general');
        $this->crawler->filter('a[href="#tabParams"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabParams')->getTagName());
        $this->crawler->filter('a[href="#tabLanguage"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabLanguage')->getTagName());
        $this->crawler->filter('a[href="#tabHorodatage"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabHorodatage')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_saveGeneral')->text());
        $this->checkJs();
    }

    public function testProfilsPage()
    {
        $this->goTo('index.php?v=d&p=profils');
        $this->crawler->filter('a[href="#profil"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#profil')->getTagName());
        $this->crawler->filter('a[href="#themetab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#themetab')->getTagName());
        $this->crawler->filter('a[href="#widgettab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#widgettab')->getTagName());
        $this->crawler->filter('a[href="#colortab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#colortab')->getTagName());
        $this->crawler->filter('a[href="#notificationtab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#notificationtab')->getTagName());
        $this->crawler->filter('a[href="#interfacetab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#interfacetab')->getTagName());
        $this->crawler->filter('a[href="#securitytab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#securitytab')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_saveProfils')->text());
        $this->checkJs();
    }

    public function testCommandesPage()
    {
        $this->goTo('index.php?v=d&p=commandes');
        $this->crawler->filter('a[href="#tabPub"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabPub')->getTagName());
        $this->crawler->filter('a[href="#tabStat"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabStat')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savecommandes')->text());
        $this->checkJs();
    }

    public function testLinksPage()
    {
        $this->goTo('index.php?v=d&p=links');
        $this->assertEquals('div', $this->crawler->filter('#links')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savelinks')->text());
        $this->checkJs();
    }

    public function testInteractConfigPage()
    {
        $this->goTo('index.php?v=d&p=interact_config');
        $this->crawler->filter('a[href="#tabParams"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabParams')->getTagName());
        $this->crawler->filter('a[href="#tabIntelli"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabIntelli')->getTagName());
        $this->crawler->filter('a[href="#tabSynonyme"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabSynonyme')->getTagName());
        $this->crawler->filter('a[href="#tabColors"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabColors')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_saveinteract_config')->text());
        $this->checkJs();
    }

    public function testEqlogicPage()
    {
        $this->goTo('index.php?v=d&p=eqlogic');
        $this->assertEquals('div', $this->crawler->filter('#eqlogic')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_saveeqlogic')->text());
        $this->checkJs();
    }

    public function testSummaryPage()
    {
        $this->goTo('index.php?v=d&p=summary');
        $this->assertEquals('a', $this->crawler->filter('#bt_addObjectSummary')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savesummary')->text());
        $this->checkJs();
    }

    public function testReportConfigPage()
    {
        $this->goTo('index.php?v=d&p=report_config');
        $this->assertEquals('div', $this->crawler->filter('#report_config')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savereport_config')->text());
        $this->checkJs();
    }

    public function testLogConfigPage()
    {
        $this->goTo('index.php?v=d&p=log_config');
        $this->crawler->filter('a[href="#tabParams"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabParams')->getTagName());
        $this->crawler->filter('a[href="#tabAlert"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabAlert')->getTagName());
        $this->crawler->filter('a[href="#tabSystem"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabSystem')->getTagName());
        $this->crawler->filter('a[href="#tabPlugins"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#tabPlugins')->getTagName());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_savelog_config')->text());
        $this->checkJs();
    }
}
