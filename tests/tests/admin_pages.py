#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of admin section of administration pages
"""
import unittest
import sys
from time import sleep
from libs.base_gui_test import BaseGuiTest

class AdminAdminPages(BaseGuiTest):
    """Test all pages linked in admin section of administration page
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_users_page(self):
        """Test user administration page
        """
        self.goto('index.php?v=d&p=users')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('bt_saveUser'))
        self.assertIsNotNone(self.get_element_by_css('.bt_changeHash'))
        self.assertIsNotNone(self.get_element_with_text('span', 'simple'))
        self.assertIsNotNone(self.get_element_with_text('span', 'admin'))
        self.get_element_by_id('bt_addUser').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('in_newUserLogin'))
        self.get_element_by_css('#md_newUser a.btn-danger').click()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_api_page(self):
        """Test API administration page
        """
        self.goto('index.php?v=d&p=api')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        regenerate_api_button = self.get_element_by_css('.bt_regenerate_api')
        self.assertIsNotNone(regenerate_api_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_network_page(self):
        """Test network administration page
        """
        self.goto('index.php?v=d&p=network')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        local_ip_input = self.get_element_by_css('input[data-l1key="network::localip"]')
        self.assertIsNotNone(local_ip_input)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_security_page(self):
        """Test security administration page
        """
        self.goto('index.php?v=d&p=security')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        remove_banned_button = self.get_element_by_id('bt_savesecurity')
        self.assertIsNotNone(remove_banned_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_cache_page(self):
        """Test cache administration page
        """
        self.goto('index.php?v=d&p=cache')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        clean_cache_button = self.get_element_by_id('bt_cleanCache')
        self.assertIsNotNone(clean_cache_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_service_page(self):
        """Test services administration page
        """
        self.goto('index.php?v=d&p=services')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        test_repo_buttons = self.driver.find_element_by_class_name('testRepoConnection')
        self.assertIsNotNone(test_repo_buttons)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()


# Entry point
if __name__ == "__main__":
    AdminAdminPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
