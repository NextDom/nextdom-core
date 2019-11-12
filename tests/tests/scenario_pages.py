#!/usr/bin/env python3
"""Run all tests of specific pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class ScenarioPages(BaseGuiTest):
    """Test all specifics pages
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_scenarios_page(self):
        """Test scenarios page
        """
        self.goto('index.php?v=d&p=scenario')
        back_button = self.get_link_by_title('Retour')
        disable_button = self.get_element_by_id('bt_changeAllScenarioState')
        scenario_button = self.get_element_by_css('div[data-scenario_id="1"]')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(disable_button)
        self.assertIsNotNone(scenario_button)
        var_button = self.get_element_by_css('a.bt_displayScenarioVariable')
        var_button.click()
        sleep(2)
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        expr_button = self.get_element_by_css('a.bt_showExpressionTest')
        expr_button.click()
        sleep(2)
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        show_button = self.get_element_by_css('a.bt_showScenarioSummary')
        show_button.click()
        sleep(2)
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_scenarios_edit_page(self):
        """Test scenario edit page
        """
        self.goto('index.php?v=d&p=scenario')
        scenario_button = self.get_element_by_css('div[data-scenario_id="1"]')
        scenario_button.click()
        sleep(2)
        self.scroll_bottom()
        sleep(1)
        self.scroll_top()
        sleep(1)
        scenario_name_input = self.get_element_by_css('input[data-l1key="name"]')
        self.assertIsNotNone(scenario_name_input)
        self.assertEqual('Test scenario', scenario_name_input.get_attribute('value'))
        # Tab condition
        self.get_element_by_css('a[href="#conditiontab"]').click()
        self.assertIsNotNone(self.get_element_by_id('scenarioVisibleAttr'))
        # Tab Programmation
        self.get_element_by_css('a[href="#scenariotab"]').click()
        self.assertIsNotNone(self.get_element_by_id('div_scenarioElement'))
        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    ScenarioPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
