<?php

use NextDom\Enums\ScenarioState;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ScenarioManager;

require_once(__DIR__ . '/../../src/core.php');

class ScenarioTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        ConfigManager::save('enableScenario', 1);
        LogHelper::clear('scenario');
        LogHelper::clear('scenarioLog/scenario5.log');
        $scenario5 = ScenarioManager::byId(5);
        $scenario5->setState('stop');
        $scenario5->setConfiguration('syncmode', 1);
        $scenario5->clearLog();
        $scenario5->save();
    }

    public function tearDown(): void
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(null);
        $scenario->setIsActive(1);
        $scenario->setDisplay('icon', '');
    }

    public function testGetIconInProgressOnlyClass()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::IN_PROGRESS);
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-spinner fa-spin');
    }

    public function testGetIconErrorInactiveOnlyClass()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::ERROR);
        $scenario->setIsActive(0);
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-times');
    }

    public function testGetIconCustomOnlyClass()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STOP);
        $scenario->setDisplay('icon', '<i class="fas fa-glass"></i>');
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-glass');
    }

    public function testGetIconInProgressWithHtml()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STARTING);
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-hourglass-start"></i>');
    }

    public function testGetIconErrorInactiveWithHtml()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::ERROR);
        $scenario->setIsActive(0);
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-times"></i>');
    }

    public function testGetIconCustomWithHtml()
    {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STOP);
        $scenario->setDisplay('icon', '<i class="fas fa-glass"></i>');
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-glass"></i>');
    }

    public function testTestTrigger()
    {
        $scenario = ScenarioManager::byId(5);
        $this->assertFalse($scenario->testTrigger('a bad trigger'));
        $this->assertTrue($scenario->testTrigger('#1#'));
    }

    public function testLaunchWithScenarioEngineDisabled()
    {
        ConfigManager::save('enableScenario', 0);
        $scenario = ScenarioManager::byId(1);
        $this->assertFalse($scenario->launch());
    }

    public function testLaunchWithScenarioDisabled()
    {
        $scenario = ScenarioManager::byId(4);
        $this->assertFalse($scenario->launch());
    }

    public function testLaunchWithScenarioAlreadyLaunchedSince60s()
    {
        $scenario = ScenarioManager::byId(5);
        $scenario->setState('starting');
        $scenario->setCache('startingTime', strtotime('now') - 60);
        $this->assertTrue($scenario->launch());
        $logs = LogHelper::get('scenario');
        $this->assertCount(1, $logs);
        $this->assertStringContainsString('scenario_execution', $logs[0]);
    }

    public function testLaunchWithScenarioAlreadyLaunchedSinceTooLong()
    {
        $currentTime = strtotime('now');
        $scenario = ScenarioManager::byId(5);
        $scenario->setState('starting');
        $scenario->setCache('startingTime', $currentTime + 3);
        $this->assertFalse($scenario->launch());
        $logs = LogHelper::get('scenario');
        $this->assertCount(1, $logs);
        $this->assertStringContainsString('"Trigger scenario"', $logs[0]);
        $this->assertTrue(strtotime('now') > $currentTime + 10);
    }

    public function testLaunchSyncModeEnabled()
    {
        $scenario = ScenarioManager::byId(5);
        $scenario->launch();
        $logs = $scenario->getLog();
        $this->assertStringContainsString('Lancement', $logs);
        $this->assertStringContainsString('Log this message', $logs);
    }

    public function testGetHumanNameSimple()
    {
        $this->assertEquals('[Test scenario]', ScenarioManager::byId(1)->getHumanName());
        $this->assertEquals('[My Room][Scenario for tests][Disabled scenario]', ScenarioManager::byId(4)->getHumanName());
    }

    public function testGetHumanNameFullInfo()
    {
        $this->assertEquals('[Aucun][Aucun][Test scenario]', ScenarioManager::byId(1)->getHumanName(true));
        $this->assertEquals('[My Room][Scenario for tests][Disabled scenario]', ScenarioManager::byId(4)->getHumanName(true));
    }

    public function testGetHumanNameWithoutGroup()
    {
        $this->assertEquals('[Aucun][Test scenario]', ScenarioManager::byId(1)->getHumanName(true, true));
        $this->assertEquals('[My Room][Disabled scenario]', ScenarioManager::byId(4)->getHumanName(true, true));
        $this->assertEquals('[Test scenario]', ScenarioManager::byId(1)->getHumanName(false, true));
        $this->assertEquals('[My Room][Disabled scenario]', ScenarioManager::byId(4)->getHumanName(false, true));
    }

    public function testGetHumanNameWithoutScenarioName()
    {
        $this->assertEquals('[Aucun][Aucun]', ScenarioManager::byId(1)->getHumanName(true, false, false, false, true));
        $this->assertEquals('[My Room][Scenario for tests]', ScenarioManager::byId(4)->getHumanName(true, false, false, false, true));
    }

    public function testGetHumanNameFullOptions()
    {
        $this->assertEquals('<span class="label label-default label-sticker">Aucun</span><p class="title">Empty scenario</p>', ScenarioManager::byId(2)->getHumanName(true, true, true, true, false, true));
        $this->assertEquals('<span class="label label-primary label-sticker">My Room</span><p class="title">Scenario with expressions</p>', ScenarioManager::byId(3)->getHumanName(true, true, true, true, false, true));
    }
}