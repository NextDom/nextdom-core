#!/usr/bin/env python3
"""Test plugins page
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class PluginsPage(BaseGuiTest):
    """Test all pages linked in administration page
    """

    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_go_to_desktop_plugin_page(self):
        """Test desktop plugin page
        """
        self.goto('index.php?v=d&p=dashboard')
        # Put the mouse hover the menu to show the plugin link
        menu_to_hover = self.get_element_by_css('.treeview>a>i.fa-puzzle-piece')
        menu_hover_action = ActionChains(self.driver).move_to_element(menu_to_hover)
        menu_hover_action.perform()
        # Wait for mouse event
        sleep(1)
        # Put the mouse hover the menu to show the programming link
        self.get_element_by_css('.treeview-menu>.treeview>a>i.fa-code').click()
        # Wait for mouse event
        sleep(1)
        # Click on menu item
        self.get_element_by_css('a[href="index.php?v=d&m=plugin4tests&p=plugin4tests"]').click()
        # Wait for loading page
        sleep(1)
        self.assertIn('Desktop plugin page', self.driver.page_source)

    def test_dashboard_widget(self):
        """Test widget plugin
        """
        self.goto('index.php?v=d&p=dashboard')
        widget = self.get_element_by_id('div_ob1')
        widget_label = self.get_element_by_css('#div_ob1 .widget-name')
        widget_content = self.get_element_by_css('[data-eqlogic_id="1"]')
        self.assertIsNotNone(widget)
        self.assertIsNotNone(widget_label)
        self.assertIsNotNone(widget_content)
        self.assertIn('TEST EQLOGIC', widget_label.text)
        self.assertIn('Cmd 1', widget_content.text)

# Entry point
if __name__ == "__main__":
    PluginsPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
