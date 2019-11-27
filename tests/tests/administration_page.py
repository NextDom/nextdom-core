#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of administration page
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class AdministrationPage(BaseGuiTest):
    """Test administration page
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_go_to_administration(self):
        """Go to the administration like human
        """
        # Wait for dashboard objets loading
        sleep(12)
        # Put the mouse hover the menu to show the administration link
        menu_to_hover = self.get_element_by_css('.treeview>a>i.fa-cog')
        menu_hover_action = ActionChains(self.driver).move_to_element(menu_to_hover)
        menu_hover_action.perform()
        # Wait for mouse event
        sleep(2)
        # Click on menu item
        self.get_element_by_css('a[href="index.php?v=d&p=administration"]').click()
        # Wait for loading page
        sleep(3)
        api_button = self.get_element_by_css('a[href="index.php?v=d&p=api"]')
        self.assertIsNotNone(api_button)

    def test_administration_page(self):
        """Test global administration page
        """
        self.goto('index.php?v=d&p=administration')
        link_buttons = ['users', 'api', 'network', 'security', 'cache', 'services',
                        'general', 'profils', 'commandes', 'links', 'interact_config',
                        'eqlogic', 'summary', 'report_config', 'log_config',
                        'health', 'cron', 'eqAnalyse', 'history', 'timeline',
                        'report', 'log',
                        'display', 'backup', 'update', 'osdb', 'interact', 'scenario',
                        'object', 'plugin']
        for link_button in link_buttons:
            admin_button = self.get_element_by_css('a[href="index.php?v=d&p='+link_button+'"]')
            self.assertIsNotNone(admin_button)
        self.assertEqual(0, len(self.get_js_logs()))


# Entry point
if __name__ == "__main__":
    AdministrationPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
