<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class PlanPageTest extends BasePageTest
{
    public function testPlanPage()
    {
        $this->goTo('index.php?v=d&p=plan');
        $this->assertEquals('div', $this->crawler->filter('div[data-eqlogic_id="4"]')->getTagName());
        $this->checkJs();
    }

    public function testViewEditPage()
    {
        $this->goTo('index.php?v=d&p=plan');

        $this->client->executeScript('showConfigModal();');
        sleep(3);
        $this->assertEquals('a', $this->crawler->filter('#bt_saveConfigurePlanHeader')->getTagName());
        $this->assertEquals('a', $this->crawler->filter('#bt_chooseIcon')->getTagName());
        $this->checkJs();
    }
}
