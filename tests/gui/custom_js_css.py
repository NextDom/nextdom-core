#!/usr/bin/env python3

import unittest
import sys
import os
import subprocess
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class CustomJsCssPage(unittest.TestCase):
    """Test customization Javascript and CSS from user
    """
    driver = None
    url = None
    login = None
    password = None
    CUSTOM_PAGE = 'index.php?v=d&p=custom'

    @classmethod
    def setUpClass(cls):
        driver_path = os.path.dirname(os.path.abspath(__file__)) + os.sep + 'chromedriver'
        try:
            desired_capabilities = DesiredCapabilities.CHROME.copy()
            desired_capabilities['loggingPrefs'] = { 'browser': 'ALL' }
            cls.driver = webdriver.Chrome(desired_capabilities=desired_capabilities,executable_path=driver_path)
            cls.driver.get(cls.url)
            cls.connect_to_nextdom()
        except WebDriverException as err:
            if err.code == -32000:
                print('Impossible to access to '+cls.url)
            else:
                print("Chromedriver needed to run tests on Chrome.")
                print("Download it on https://sites.google.com/a/chromium.org/chromedriver/downloads")
            exit(1)

    @classmethod
    def tearDownClass(cls):
        if cls.driver is not None:
            cls.driver.quit()

    @classmethod
    def connect_to_nextdom(cls):
        # Wait for loading page
        sleep(2)
        login_input = cls.driver.find_element_by_id('in_login_username')
        password_input = cls.driver.find_element_by_id('in_login_password')
        connect_button = cls.driver.find_element_by_id('bt_login_validate')
        login_input.send_keys(cls.login)
        password_input.send_keys(cls.password)
        connect_button.click()
        # Wait animation ending
        sleep(4)

    def setUp(self):
        # Reset CSS custom activation
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "/usr/bin/mysql", "-u", "root", "nextdomdev", "-e", "UPDATE config SET `value` = 0 WHERE `key` = 'enableCustomCss'"])
        # Delete created files by tests
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "rm", "-f", "/var/www/html/var/custom/desktop/custom.js"])
        subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "rm", "-f", "/var/www/html/var/custom/desktop/custom.css"])

    def test_add_custom_js(self):
        self.driver.get(self.url+self.CUSTOM_PAGE)
        # Wait for loading page
        sleep(2)
        # Add javascript
        system_menu = self.driver.find_element_by_css_selector('a[href="#advanced"]')
        system_menu.click()
        sleep(2)
        enable_custom_button = self.driver.find_element_by_id('enableCustomCss')
        enable_custom_button.click()
        # Write code in CodeMirror
        js_editor = self.driver.find_element_by_css_selector('#desktop>div>fieldset:first-of-type>.CodeMirror.cm-s-default>.CodeMirror-scroll>.CodeMirror-sizer>div>.CodeMirror-lines')
        js_editor.click()
        sleep(1)
        write_code_action = ActionChains(self.driver)
        write_code_action.send_keys('console.log(\'just_a_test\');')
        write_code_action.perform()
        sleep(1)
        save_button = self.driver.find_element_by_id('bt_savecustom')
        save_button.click();
        sleep(2)
        # Reload the page
        self.driver.refresh()
        sleep(2)
        system_menu = self.driver.find_element_by_css_selector('a[href="#advanced"]')
        self.assertIsNotNone(system_menu)
        file_content = subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "cat", "/var/www/html/var/custom/desktop/custom.js"])
        self.assertEqual(file_content, b"console.log('just_a_test');")
        javascript_logs = self.driver.get_log('browser')
        self.assertTrue(len(javascript_logs) > 0)
        for javascript_log in javascript_logs:
            self.assertNotEqual('SEVERE', javascript_log['level'])
        self.assertIn('just_a_test', javascript_logs[-1:][0]['message'])

    def test_add_custom_css(self):
        self.driver.get(self.url+self.CUSTOM_PAGE)
        # Wait for loading page
        sleep(2)
        # Add javascript
        system_menu = self.driver.find_element_by_css_selector('a[href="#advanced"]')
        system_menu.click()
        sleep(2)
        enable_custom_button = self.driver.find_element_by_id('enableCustomCss')
        enable_custom_button.click()
        # Write code in CodeMirror
        css_editor = self.driver.find_element_by_css_selector('#desktop>div>fieldset:last-of-type>.CodeMirror.cm-s-default>.CodeMirror-scroll>.CodeMirror-sizer>div>.CodeMirror-lines')
        css_editor.click()
        sleep(1)
        write_code_action = ActionChains(self.driver)
        write_code_action.send_keys('* { color: blue !important; }')
        write_code_action.perform()
        sleep(1)
        save_button = self.driver.find_element_by_id('bt_savecustom')
        save_button.click();
        sleep(2)
        # Reload the page
        self.driver.refresh()
        sleep(2)
        system_menu = self.driver.find_element_by_css_selector('a[href="#advanced"]')
        self.assertIsNotNone(system_menu)
        file_content = subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "cat", "/var/www/html/var/custom/desktop/custom.css"])
        self.assertEqual(file_content, b"* { color: blue !important; }")
        javascript_logs = self.driver.get_log('browser')
        for javascript_log in javascript_logs:
            self.assertNotEqual('SEVERE', javascript_log['level'])

# Entry point
if __name__ == "__main__":
    if len(sys.argv) < 4:
        print('Usage : ' + sys.argv[0]+ ' url login password')
    else:
        if sys.argv[1][:-1] == '/':
            CustomJsCssPage.url = sys.argv[1]
        else:
            CustomJsCssPage.url = sys.argv[1]+'/'
        CustomJsCssPage.login = sys.argv[2]
        CustomJsCssPage.password = sys.argv[3]
        # unittest use sys.argv
        del sys.argv[1:]
        # failfast=True pour arrêter à la première erreur
        unittest.main()
