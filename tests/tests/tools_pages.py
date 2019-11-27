#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of tools section of administration pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class AdminToolsPages(BaseGuiTest):
    """Test all pages linked in tools section of administration page
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
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('cb_actifDisplay'))
        self.assertIsNotNone(self.get_element_with_text('h3', 'My Room'))
        self.get_element_by_id('bt_removeHistory').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_emptyRemoveHistory'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_backup_page(self):
        """Test backup page
        """
        self.goto('index.php?v=d&p=backup')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        log_button = self.get_element_by_id('bt_saveOpenLog')
        self.assertIsNotNone(log_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_update_page(self):
        """Test update page
        """
        self.goto('index.php?v=d&p=update')
        sleep(10)
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('selectiveUpdateButton'))
        self.get_element_by_id('logDialogButton').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('updateLog'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.click_on_invisible('a[href="#core"]')
        self.assertIsNotNone(self.get_element_by_id('core'))
        self.click_on_invisible('a[href="#plugins"]')
        self.assertIsNotNone(self.get_element_by_id('plugins'))
        self.click_on_invisible('a[href="#widgets"]')
        self.assertIsNotNone(self.get_element_by_id('widgets'))
        self.click_on_invisible('a[href="#scripts"]')
        self.assertIsNotNone(self.get_element_by_id('scripts'))
        self.click_on_invisible('a[href="#others"]')
        self.assertIsNotNone(self.get_element_by_id('others'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_note_page(self):
        """Test note page
        """
        self.goto('index.php?v=d&p=note')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        add_note_button = self.get_element_by_id('bt_noteManagerAdd')
        self.assertIsNotNone(add_note_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_osdb_page(self):
        """Test osdb page
        """
        self.goto('index.php?v=d&p=osdb')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        console_button = self.driver.find_element_by_xpath('//a[@href="index.php?v=d&p=system"]')
        self.assertIsNotNone(console_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_interact_page(self):
        """Test interact page
        """
        self.goto('index.php?v=d&p=interact')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('bt_regenerateInteract'))
        self.assertIsNotNone(self.get_element_by_id('bt_testInteract'))
        self.get_element_by_id('bt_testInteract').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('in_testInteractQuery'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_scenarios_page(self):
        """Test scenarios page
        """
        self.goto('index.php?v=d&p=scenario')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        disable_button = self.get_element_by_id('bt_changeAllScenarioState')
        scenario_button = self.get_element_by_css('div[data-scenario_id="1"]')
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
        back_button.click()

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
        sleep(2)
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        scenario_name_input = self.get_element_by_css('input[data-l1key="name"]')
        self.assertIsNotNone(scenario_name_input)
        self.assertEqual('Test scenario', scenario_name_input.get_attribute('value'))
        # Tab condition
        self.click_on_invisible('a[href="#conditiontab"]')
        self.assertIsNotNone(self.get_element_by_id('scenarioVisibleAttr'))
        # Tab Programmation
        self.click_on_invisible('a[href="#scenariotab"]')
        self.assertIsNotNone(self.get_element_by_id('div_scenarioElement'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_objects_page(self):
        """Test object page
        """
        self.goto('index.php?v=d&p=object')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_with_text('h3', 'Objets'))
        object_button = self.get_element_by_id('bt_showObjectSummary')
        self.assertIsNotNone(object_button)
        object_button.click()
        sleep(2)
        self.assertIsNotNone(self.get_element_with_text('span', 'Résumé Objets'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_object_edit_page(self):
        """Test object edit page
        """
        self.goto('index.php?v=d&p=object')
        sleep(2)
        self.get_element_by_css('div[data-object_id="1"] .bt_detailsObject').click()
        sleep(2)
        self.scroll_bottom()
        sleep(1)
        self.scroll_top()
        sleep(1)
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('colorpickTagText'))
        # Tab summary
        self.click_on_invisible('a[href="#summarytab"]')
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('summarytabsecurity'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_plugins_page(self):
        """Test plugins page
        """
        self.goto('index.php?v=d&p=plugin')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('bt_addPluginFromOtherSource'))
        self.get_element_by_id('bt_addPluginFromOtherSource').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_css('select[data-l1key="source"]'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.click_on_invisible('a[href="#actifs"]')
        self.assertIsNotNone(self.get_element_by_id('actifs'))
        self.click_on_invisible('a[href="#inactifs"]')
        self.assertIsNotNone(self.get_element_by_id('inactifs'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()


# Entry point
if __name__ == "__main__":
    AdminToolsPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
