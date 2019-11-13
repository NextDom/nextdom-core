#!/usr/bin/env python3
"""Run all tests of specific pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class ObjectPages(BaseGuiTest):
    """Test all specifics pages
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_objects_page(self):
        """Test object page
        """
        self.goto('index.php?v=d&p=object')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_with_text('h3', 'Objets'))

        self.get_element_by_id('bt_showObjectSummary').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_with_text('span', 'Résumé Objets'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

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
        self.assertIsNotNone(self.get_element_by_id('colorpickTagText'))
        # Tab summary
        #self.get_element_by_css('a[href="#summarytab"]').click()
        self.click_on_invisible('a[href="#summarytab"]')
        self.assertIsNotNone(self.get_element_by_id('summarytabsecurity'))

        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    ObjectPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
