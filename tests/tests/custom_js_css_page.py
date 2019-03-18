#!/usr/bin/env python3
"""Test customization Javascript and CSS from user
"""

import unittest
import sys
import subprocess
from time import sleep
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class CustomJsCssPage(BaseGuiTest):
    """Test customization Javascript and CSS from user
    """

    CUSTOM_PAGE = 'index.php?v=d&p=custom'

    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True, 'ALL')

    def setUp(self):
        """Reset Customisation
        """
        # Reset CSS custom activation
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "/usr/bin/mysql", "-u", "root", "nextdomdev", "-e", "UPDATE config SET `value` = 0 WHERE `key` = 'enableCustomCss'"]) #pylint: disable=line-too-long
        # Delete created files by tests
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "rm", "-f", "/var/www/html/var/custom/desktop/custom.js"]) #pylint: disable=line-too-long
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "rm", "-f", "/var/www/html/var/custom/desktop/custom.css"]) #pylint: disable=line-too-long

    def test_add_custom_js(self):
        """Test custom javascript
        """
        self.goto(self.CUSTOM_PAGE)
        # Add javascript
        system_menu = self.get_element_by_css('a[href="#advanced"]')
        system_menu.click()
        sleep(2)
        enable_custom_button = self.get_element_by_id('enableCustomCss')
        enable_custom_button.click()
        # Write code in CodeMirror
        js_editor = self.get_element_by_css('#desktop>div>fieldset:first-of-type>.CodeMirror.cm-s-default>.CodeMirror-scroll>.CodeMirror-sizer>div>.CodeMirror-lines') #pylint: disable=line-too-long
        js_editor.click()
        sleep(1)
        write_code_action = ActionChains(self.driver)
        write_code_action.send_keys('console.log(\'just_a_test\');')
        write_code_action.perform()
        sleep(1)
        save_button = self.get_element_by_id('bt_savecustom')
        save_button.click()
        sleep(2)
        # Reload the page
        self.driver.refresh()
        sleep(2)
        system_menu = self.get_element_by_css('a[href="#advanced"]')
        self.assertIsNotNone(system_menu)
        docker_cat = "docker exec -it nextdom-test-custom-js-css cat /var/www/html/var/custom/desktop/custom.js" #pylint: disable=line-too-long
        file_content = subprocess.check_output(docker_cat.split(' '))
        self.assertEqual(file_content, b"console.log('just_a_test');")
        javascript_logs = self.get_js_logs()
        self.assertTrue(len(javascript_logs) > 0)
        for javascript_log in javascript_logs:
            self.assertNotEqual('SEVERE', javascript_log['level'])
        self.assertIn('just_a_test', javascript_logs[-1:][0]['message'])

    def test_add_custom_css(self):
        """Test custom css
        """
        self.goto(self.CUSTOM_PAGE)
        # Add javascript
        system_menu = self.get_element_by_css('a[href="#advanced"]')
        system_menu.click()
        sleep(2)
        enable_custom_button = self.get_element_by_id('enableCustomCss')
        enable_custom_button.click()
        # Write code in CodeMirror
        css_editor = self.get_element_by_css('#desktop>div>fieldset:last-of-type>.CodeMirror.cm-s-default>.CodeMirror-scroll>.CodeMirror-sizer>div>.CodeMirror-lines') #pylint: disable=line-too-long
        css_editor.click()
        sleep(1)
        write_code_action = ActionChains(self.driver)
        write_code_action.send_keys('* { color: blue !important; }')
        write_code_action.perform()
        sleep(1)
        save_button = self.get_element_by_id('bt_savecustom')
        save_button.click()
        sleep(2)
        # Reload the page
        self.driver.refresh()
        sleep(2)
        system_menu = self.get_element_by_css('a[href="#advanced"]')
        self.assertIsNotNone(system_menu)
        docker_cat = 'docker exec -it nextdom-test-custom-js-css cat /var/www/html/var/custom/desktop/custom.css' #pylint: disable=line-too-long
        file_content = subprocess.check_output(docker_cat.split(' '))
        self.assertEqual(file_content, b"* { color: blue !important; }")
        javascript_logs = self.get_js_logs()
        for javascript_log in javascript_logs:
            self.assertNotEqual('SEVERE', javascript_log['level'])

# Entry point
if __name__ == "__main__":
    CustomJsCssPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
