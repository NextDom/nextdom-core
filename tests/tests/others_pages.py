#!/usr/bin/env python3
"""Run all tests of specific pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class OtherPages(BaseGuiTest):
    """Test all specifics pages
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_display_page(self):
        """Test display page
        """
        self.goto('index.php?v=d&p=display')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_with_text('h3', 'My Room'))
        self.get_element_by_id('bt_removeHistory').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_emptyRemoveHistory'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_interact_page(self):
        """Test interact page
        """
        self.goto('index.php?v=d&p=interact')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_regenerateInteract'))
        self.get_element_by_id('bt_testInteract').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('in_testInteractQuery'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_health_page(self):
        """Test health page
        """
        self.goto('index.php?v=d&p=health')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_benchmarkNextDom'))
        self.get_element_by_css('a[href="#div_Plugins"]').click()
        self.assertIsNotNone(self.get_element_by_id('div_Plugins'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_eqanalyse_page(self):
        """Test eqAnalyse page
        """
        self.goto('index.php?v=d&p=eqAnalyse')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_css('a[href="index.php?v=d&p=eqlogic"]'))
        self.get_element_by_css('a[href="#battery"]').click()
        self.assertIsNotNone(self.get_element_by_id('battery'))
        self.get_element_by_css('a[href="#alertEqlogic"]').click()
        self.assertIsNotNone(self.get_element_by_id('alertEqlogic'))
        self.get_element_by_css('a[href="#actionCmd"]').click()
        self.assertIsNotNone(self.get_element_by_id('actionCmd'))
        self.get_element_by_css('a[href="#alertCmd"]').click()
        self.assertIsNotNone(self.get_element_by_id('alertCmd'))
        self.get_element_by_css('a[href="#deadCmd"]').click()
        self.assertIsNotNone(self.get_element_by_id('deadCmd'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_history_page(self):
        """Test history page
        """
        self.goto('index.php?v=d&p=history')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_displayCalculHistory'))
        self.get_element_by_id('bt_openCmdHistoryConfigure').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_cmdConfigureCmdHistoryApply'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_timeline_page(self):
        """Test timeline page
        """
        self.goto('index.php?v=d&p=timeline')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_configureTimelineScenario'))
        self.get_element_by_id('bt_configureTimelineScenario').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_saveSummaryScenario'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.get_element_by_id('bt_configureTimelineCommand').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_cmdConfigureCmdHistoryApply'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_plugin_page(self):
        """Test plugin page
        """
        self.goto('index.php?v=d&p=plugin')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_addPluginFromOtherSource'))
        self.get_element_by_id('bt_addPluginFromOtherSource').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_css('select[data-l1key="source"]'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.get_element_by_css('a[href="#actifs"]').click()
        self.assertIsNotNone(self.get_element_by_id('actifs'))
        self.get_element_by_css('a[href="#inactifs"]').click()
        self.assertIsNotNone(self.get_element_by_id('inactifs'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_update_page(self):
        """Test update page
        """
        self.goto('index.php?v=d&p=update')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.get_element_by_id('logDialogButton').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('updateLog'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.get_element_by_css('a[href="#core"]').click()
        self.assertIsNotNone(self.get_element_by_id('core'))
        self.get_element_by_css('a[href="#plugins"]').click()
        self.assertIsNotNone(self.get_element_by_id('plugins'))
        self.get_element_by_css('a[href="#widgets"]').click()
        self.assertIsNotNone(self.get_element_by_id('widgets'))
        self.get_element_by_css('a[href="#scripts"]').click()
        self.assertIsNotNone(self.get_element_by_id('scripts'))
        self.get_element_by_css('a[href="#others"]').click()
        self.assertIsNotNone(self.get_element_by_id('others'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_header_modals(self):
        """Test modals in header
        """
        self.goto('index.php')
        self.get_element_by_id('bt_messageModal').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_clearMessage'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    OtherPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
