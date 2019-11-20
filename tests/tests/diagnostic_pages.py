#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of diagnotic section of administration pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class AdminDiagnosticPages(BaseGuiTest):
    """Test all pages linked in admin section of administration page
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_health_page(self):
        """Test health administration page
        """
        self.goto('index.php?v=d&p=health')
        sleep(10)
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        info_boxes = self.driver.find_elements_by_css_selector('.info-box-content')
        self.assertTrue(len(info_boxes) > 15)
        self.assertIsNotNone(self.get_element_by_id('bt_benchmarkNextDom'))
        self.click_on_invisible('a[href="#div_Plugins"]')
        self.assertIsNotNone(self.get_element_by_id('div_Plugins'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_cron_page(self):
        """Test cron administration page
        """
        self.goto('index.php?v=d&p=cron')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        add_cron_button = self.get_element_by_id('bt_addCron')
        self.assertIsNotNone(add_cron_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_eqanalyse_page(self):
        """Test eqanalyse administration page
        """
        self.goto('index.php?v=d&p=eqAnalyse')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('ul_tabBatteryAlert'))
        self.assertIsNotNone(self.get_element_by_css('a[href="index.php?v=d&p=eqlogic"]'))
        self.click_on_invisible('a[href="#battery"]')
        self.assertIsNotNone(self.get_element_by_id('battery'))
        self.click_on_invisible('a[href="#alertEqlogic"]')
        self.assertIsNotNone(self.get_element_by_id('alertEqlogic'))
        self.click_on_invisible('a[href="#actionCmd"]')
        self.assertIsNotNone(self.get_element_by_id('actionCmd'))
        self.click_on_invisible('a[href="#alertCmd"]')
        self.assertIsNotNone(self.get_element_by_id('alertCmd'))
        self.click_on_invisible('a[href="#deadCmd"]')
        self.assertIsNotNone(self.get_element_by_id('deadCmd'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_history_page(self):
        """Test history administration page
        """
        self.goto('index.php?v=d&p=history')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('in_calculHistory'))
        self.assertIsNotNone(self.get_element_by_id('bt_displayCalculHistory'))
        self.get_element_by_id('bt_openCmdHistoryConfigure').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_cmdConfigureCmdHistoryApply'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_timeline_page(self):
        """Test timeline administration page
        """
        self.goto('index.php?v=d&p=timeline')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('table_timeline'))
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
        back_button.click()

    def test_report_page(self):
        """Test report administration page
        """
        self.goto('index.php?v=d&p=report')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        report_search_list = self.get_element_by_id('ul_report')
        self.assertIsNotNone(report_search_list)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_log_page(self):
        """Test log administration page
        """
        self.goto('index.php?v=d&p=log')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        remove_all_button = self.get_element_by_id('bt_removeAllLog')
        self.assertIsNotNone(remove_all_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()


# Entry point
if __name__ == "__main__":
    AdminDiagnosticPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
