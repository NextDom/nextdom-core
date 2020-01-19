<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class FeatureCronTest extends PHPUnit\Framework\TestCase
{
    public static function setUpClass(): void
    {
        exec('service cron start');
    }

    public static function tearDownClass(): void
    {
        exec('service cron stop');
    }

    public function testScenarioCron()
    {
        exec('rm -fr /var/log/nextdom/scenarioLog');
        exec('rm -fr /tmp/nextdom/cache/*');
        // Wait cron execution
        sleep(160);
        $this->assertTrue(is_file('/var/log/nextdom/scenarioLog/scenario1.log'));
        $this->assertContains('LAUNCHED', file_get_contents('/var/log/nextdom/scenarioLog/scenario1.log'));
    }

    public function testPluginCron()
    {
        exec('rm -fr /var/log/nextdom/plugin4tests');
        exec('rm -fr /tmp/nextdom/cache/*');
        // Wait cron execution
        sleep(180);
        $this->assertTrue(is_file('/var/log/nextdom/plugin4tests'));
        $this->assertContains('CRON TEST', file_get_contents('/var/log/nextdom/plugin4tests'));

    }
}
