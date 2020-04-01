<?php

require_once(__DIR__ . '/../libs/BasePageTest.php');

class ModalPageTest extends BasePageTest
{
    public function testMessageModal()
    {
        $this->goTo('index.php?v=d&p=dashboard');
        $this->crawler->filter('#bt_messageModal')->click();
        sleep(2);
        $this->assertEquals('a', $this->crawler->filter('#bt_clearMessage')->getTagName());
        $this->checkJs();
    }
}
