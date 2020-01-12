<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class ToolsPageTest extends BasePageTest
{
    public function testDisplayPage()
    {
        $this->goTo('index.php?v=d&p=display');
        $this->crawler->filter('#bt_removeHistory')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_emptyRemoveHistory')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->assertEquals('div', $this->crawler->filter('#accordionDisplay')->getTagName());
        $this->checkJs();
    }

    public function testBackupPage()
    {
        $this->goTo('index.php?v=d&p=backup');
        sleep(3);
        // Don't know why on this page
        $this->invisibleClick('a[href="#tabDist"]');
        $this->assertEquals('div', $this->crawler->filter('#tabDist')->getTagName());
        $this->invisibleClick('a[href="#tabLocales"]');
        $this->assertEquals('div', $this->crawler->filter('#tabLocales')->getTagName());
        $this->invisibleClick('a[href="#tabParams"]');
        $this->assertEquals('div', $this->crawler->filter('#tabParams')->getTagName());

        $this->crawler->filter('#bt_saveOpenLog')->click();
        sleep(3);
        $this->assertEquals('div', $this->crawler->filter('#md_backupInfo')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_backupInfo"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->checkJs();
    }

    public function testUpdatePage()
    {
        $this->goTo('index.php?v=d&p=update');
        $this->crawler->filter('#logDialogButton')->click();
        sleep(3);
        $this->assertEquals('pre', $this->crawler->filter('#updateLog')->getTagName());
        $this->crawler->filter('div[aria-describedby="updateInfoModal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->crawler->filter('a[href="#core"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#core')->getTagName());
        $this->crawler->filter('a[href="#plugins"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#plugins')->getTagName());
        $this->crawler->filter('a[href="#widgets"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#widgets')->getTagName());
        $this->crawler->filter('a[href="#scripts"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#scripts')->getTagName());
        $this->crawler->filter('a[href="#others"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#others')->getTagName());
        $this->checkJs();
    }

    public function testNotePage()
    {
        $this->goTo('index.php?v=d&p=note');
        $this->assertContains('Une autre note', $this->crawler->filter('#note')->text());
        $this->assertEquals('Sauvegarder', $this->crawler->filter('#bt_noteManagerSave')->text());
        $this->checkJs();
    }

    public function testOsDbPage()
    {
        $this->goTo('index.php?v=d&p=osdb');
        $this->assertEquals('a', $this->crawler->filterXPath('//a[@href="index.php?v=d&p=system"]')->getTagName());
        $this->checkJs();
    }

    public function testInteractPage()
    {
        $this->goTo('index.php?v=d&p=interact');
        $this->crawler->filter('#bt_testInteract')->click();
        sleep(3);
        $this->assertEquals('input', $this->crawler->filter('#in_testInteractQuery')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->assertEquals('Régénérer', $this->crawler->filter('#bt_regenerateInteract')->text());
        $this->checkJs();
    }

    public function testScenarioPage()
    {
        $this->goTo('index.php?v=d&p=scenario');
        sleep(3);
        $this->crawler->filter('#scenarioThumbnailDisplay .bt_showExpressionTest')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_executeExpressionOk')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#scenarioThumbnailDisplay .bt_displayScenarioVariable')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_dataStoreManagementAdd')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#scenarioThumbnailDisplay .bt_showScenarioSummary')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_saveSummaryScenario')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->checkJs();
    }

    public function testScenarioEditPage()
    {
        $this->goTo('index.php?v=d&p=scenario');
        sleep(3);
        $this->crawler->filter('div[data-scenario_id="1"]')->click();
        sleep(4);
        $this->crawler->filter('a[href="#generaltab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#generaltab')->getTagName());
        $this->crawler->filter('a[href="#conditiontab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#conditiontab')->getTagName());
        $this->crawler->filter('a[href="#conditiontab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#scenariotab')->getTagName());
        $this->crawler->filter('a[href="#usedtab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#usedtab')->getTagName());

        $this->crawler->filter('#div_editScenario .bt_showExpressionTest')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_executeExpressionOk')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#div_editScenario .bt_displayScenarioVariable')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_dataStoreManagementAdd')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#bt_logScenario')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_scenarioLogEmpty')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#bt_graphScenario')->click();
        sleep(3);
        $this->assertEquals('div', $this->crawler->filter('#div_graphLinkRenderer')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->crawler->filter('#bt_templateScenario')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_scenarioTemplateConvert')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);

        $this->checkJs();
    }

    public function testObjectPage()
    {
        $this->goTo('index.php?v=d&p=object');
        $this->assertCount(4, $this->crawler->filter('.objectDisplayCard'));
        $this->crawler->filter('#bt_showObjectSummary')->click();
        sleep(3);
        $this->assertEquals('tr', $this->crawler->filter('.tablesorter-headerRow')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->assertEquals('Ajouter', $this->crawler->filter('#bt_addObject')->text());
        $this->checkJs();
    }

    public function testObjectEditPage()
    {
        $this->goTo('index.php?v=d&p=object');
        $this->crawler->filter('div[data-object_id="1"] .bt_detailsObject')->click();
        sleep(3);
        $this->crawler->filter('a[href="#objecttab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#objecttab')->getTagName());
        $this->crawler->filter('a[href="#colortab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#colortab')->getTagName());
        $this->crawler->filter('a[href="#summarytab"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#summarytab')->getTagName());
        $this->checkJs();
    }

    public function testPluginPage()
    {
        $this->goTo('index.php?v=d&p=plugin');
        $this->crawler->filter('#bt_addPluginFromOtherSource')->click();
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_repoAddSaveUpdate')->getTagName());
        $this->crawler->filter('div[aria-describedby="md_modal"] .ui-dialog-titlebar-close')->click();
        sleep(3);
        $this->crawler->filter('a[href="#actifs"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#actifs')->getTagName());
        $this->crawler->filter('a[href="#inactifs"]')->click();
        $this->assertEquals('div', $this->crawler->filter('#inactifs')->getTagName());
        $this->checkJs();
    }

    /*
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
    */
}
