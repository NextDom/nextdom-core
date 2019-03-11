#!/usr/bin/env python3
"""Test first use process pages
"""

import unittest
import sys
import os
from time import sleep
from libs.base_gui_test import BaseGuiTest

class FirstUsePage(BaseGuiTest):
    """Test first use process pages
    """

    INITIAL_PASSWORD = 'admin'

    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver()

    def setUp(self):
        """Reset process
        """
        # Reset firstUse status
        os.system('./scripts/sed_in_docker.sh "nextdom::firstUse = 0" "nextdom::firstUse = 1" /var/lib/nextdom/config/default.config.ini nextdom-test-firstuse') #pylint: disable=line-too-long
        # Reset user password
        os.system('docker exec -i nextdom-test-firstuse /usr/bin/mysql -u root nextdomdev -e "UPDATE user SET password = SHA2(\'' + self.INITIAL_PASSWORD + '\', 512)"') #pylint: disable=line-too-long
        # Reset firstUse status in database
        os.system('docker exec -i nextdom-test-firstuse /usr/bin/mysql -u root nextdomdev -e "UPDATE config SET \\`value\\` = 1 WHERE \\`key\\` = \'nextdom::firstUse\'"') #pylint: disable=line-too-long

    def test_first_use_shortcuts(self): #pylint: disable=too-many-statements
        """Test shortcuts
        """
        self.goto()
        self.assertEqual(0, len(self.get_js_logs()))
        step_shortcuts = []
        for i in range(1, 6):
            step_shortcut = self.get_element_by_css('a[href="#step-' + str(i) + '"]')
            self.assertIsNotNone(step_shortcut)
            step_shortcuts.append(step_shortcut)
        step_2_button = self.get_element_by_id('toStep2')
        password1_button = self.get_element_by_id('in_change_password')
        password2_button = self.get_element_by_id('in_change_passwordToo')
        step_3_button = self.get_element_by_id('toStep3')
        step_3_skip_button = self.get_element_by_id('skipStep4')
        white_theme_checkbox = self.get_element_by_css('input[value="white"]')
        dark_theme_checkbox = self.get_element_by_css('input[value="dark"]')
        step_4_button = self.get_element_by_id('toStep4')
        step_5_button = self.get_element_by_id('toStep5')
        finish_button = self.get_element_by_id('finishConf')

        for step_shortcut in step_shortcuts:
            self.assertTrue(step_shortcut.is_displayed())
        # Step 1
        sleep(5)
        self.assertTrue(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_shortcuts[1].click()
        # Step 2
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertTrue(password1_button.is_displayed())
        self.assertTrue(password2_button.is_displayed())
        self.assertTrue(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_shortcuts[2].click()
        # Step 3
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertTrue(step_3_skip_button.is_displayed())
        self.assertTrue(step_4_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_shortcuts[3].click()
        # Step 4
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertTrue(white_theme_checkbox.is_displayed())
        self.assertTrue(dark_theme_checkbox.is_displayed())
        self.assertTrue(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_shortcuts[4].click()
        # Step 5
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertTrue(finish_button.is_displayed())
        finish_button.click()
        # Stay in fist connect page
        self.assertEqual(0, len(self.get_js_logs()))
        self.assertIn("connexion", self.get_page_title())

    def test_full_process(self): #pylint: disable=too-many-statements
        """Test full process
        """
        self.goto()

        step_2_button = self.get_element_by_id('toStep2')
        password1_button = self.get_element_by_id('in_change_password')
        password2_button = self.get_element_by_id('in_change_passwordToo')
        step_3_button = self.get_element_by_id('toStep3')
        step_3_skip_button = self.get_element_by_id('skipStep4')
        white_theme_checkbox = self.get_element_by_css('input[value="white"]')
        dark_theme_checkbox = self.get_element_by_css('input[value="dark"]')
        step_4_button = self.get_element_by_id('toStep4')
        step_5_button = self.get_element_by_id('toStep5')
        finish_button = self.get_element_by_id('finishConf')

        # Step 1
        sleep(5)
        self.assertTrue(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_2_button.click()
        # Step 2
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertTrue(password1_button.is_displayed())
        self.assertTrue(password2_button.is_displayed())
        self.assertTrue(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        password1_button.send_keys('nextdom')
        password2_button.send_keys('nextdom')
        step_3_button.click()
        # Step 3
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertTrue(step_3_skip_button.is_displayed())
        self.assertTrue(step_4_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_3_skip_button.click()
        # Step 4
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertTrue(white_theme_checkbox.is_displayed())
        self.assertTrue(dark_theme_checkbox.is_displayed())
        self.assertTrue(step_5_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        dark_theme_checkbox.click()
        step_5_button.click()
        # Step 5
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_3_skip_button.is_displayed())
        self.assertFalse(white_theme_checkbox.is_displayed())
        self.assertFalse(dark_theme_checkbox.is_displayed())
        self.assertFalse(step_4_button.is_displayed())
        self.assertFalse(step_5_button.is_displayed())
        self.assertTrue(finish_button.is_displayed())
        finish_button.click()
        # Stay in fist connect page
        self.assertEqual(0, len(self.get_js_logs()))
        self.assertIn("Dashboard", self.get_page_title())

# Entry point
if __name__ == "__main__":
    FirstUsePage.parse_cli_args()
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
