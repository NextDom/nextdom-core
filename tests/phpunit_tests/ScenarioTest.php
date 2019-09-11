<?php

use NextDom\Enums\ScenarioState;
use NextDom\Managers\ScenarioManager;

require_once(__DIR__ . '/../../src/core.php');

class ScenarioTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {

    }

    public function tearDown() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(null);
        $scenario->setIsActive(1);
        $scenario->setDisplay('icon', '');
    }

    public function testGetIconInProgressOnlyClass() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::IN_PROGRESS);
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-spinner fa-spin');
    }

    public function testGetIconErrorInactiveOnlyClass() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::ERROR);
        $scenario->setIsActive(0);
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-times');
    }

    public function testGetIconCustomOnlyClass() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STOP);
        $scenario->setDisplay('icon', '<i class="fas fa-glass"></i>');
        $icon = $scenario->getIcon(true);
        $this->assertEquals($icon, 'fas fa-glass');
    }

    public function testGetIconInProgressWithHtml() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STARTING);
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-hourglass-start"></i>');
    }

    public function testGetIconErrorInactiveWithHtml() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::ERROR);
        $scenario->setIsActive(0);
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-times"></i>');
    }

    public function testGetIconCustomWithHtml() {
        $scenario = ScenarioManager::byId(1);
        $scenario->setState(ScenarioState::STOP);
        $scenario->setDisplay('icon', '<i class="fas fa-glass"></i>');
        $icon = $scenario->getIcon(false);
        $this->assertEquals($icon, '<i class="fas fa-glass"></i>');
    }
}