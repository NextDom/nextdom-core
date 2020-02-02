<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class AdministrationPageTest extends BasePageTest
{
    public function testAdministrationPage()
    {
        $this->goTo('index.php?v=d&p=administration');

        // Start test page
        $linkButtons = [
            'users', 'api', 'network', 'security', 'cache', 'services',
            'general', 'profils', 'commandes', 'links', 'interact_config',
            'eqlogic', 'summary', 'report_config', 'log_config',
            'health', 'cron', 'eqAnalyse', 'history', 'timeline',
            'report', 'log',
            'display', 'backup', 'update', 'osdb', 'interact', 'scenario',
            'object', 'plugin'];
        foreach ($linkButtons as $linkButton) {
            $button = $this->crawler->filterXPath('//a[@href="index.php?v=d&p=' . $linkButton . '"]');
            $this->assertNotNull($button);
        }
    }
}
