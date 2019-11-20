#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of modal pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class ModalPages(BaseGuiTest):
    """Test all specifics pages
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_message_modals(self):
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
    ModalPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
