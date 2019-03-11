#!/usr/bin/env python3
"""Test connection page
"""

import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from libs.base_gui_test import BaseGuiTest

class ConnectionPage(BaseGuiTest):
    """Test connection page
    """

    LOGOUT_PATTERN = 'index.php?v=d&logout=1'

    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver()

    def test_connection_page(self):
        """Test connection page content
        """
        self.goto(self.LOGOUT_PATTERN)
        login_input = self.get_element_by_id('in_login_username')
        password_input = self.get_element_by_id('in_login_password')
        connect_button = self.get_element_by_id('bt_login_validate')
        remember_checkbox = self.get_element_by_id('cb_storeConnection')
        self.assertIsNotNone(login_input)
        self.assertIsNotNone(password_input)
        self.assertIsNotNone(connect_button)
        self.assertIsNotNone(remember_checkbox)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_good_connection(self):
        """Test connection with good user
        """
        self.goto(self.LOGOUT_PATTERN)
        login_input = self.get_element_by_id('in_login_username')
        password_input = self.get_element_by_id('in_login_password')
        connect_button = self.get_element_by_id('bt_login_validate')
        login_input.send_keys(self.login)
        password_input.send_keys(self.password)
        connect_button.click()
        # Wait dashboard
        sleep(8)
        self.assertIn('Dashboard', self.get_page_title())

    def test_enter_key_from_password_field(self):
        """Test enter key from password
        """
        self.goto(self.LOGOUT_PATTERN)
        login_input = self.get_element_by_id('in_login_username')
        password_input = self.get_element_by_id('in_login_password')
        login_input.send_keys(self.login)
        password_input.send_keys('If this password work, you\'re crazy!')
        password_input.send_keys(Keys.RETURN)
        # Wait Dashboard
        sleep(8)
        self.assertIn('Connexion', self.get_page_title())

# Entry point
if __name__ == "__main__":
    ConnectionPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
