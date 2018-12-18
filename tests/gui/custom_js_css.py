#!/usr/bin/env python3

import unittest
import sys
import os
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
            desired_capabilities['loggingPrefs'] = { 'browser': 'SEVERE' }
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

    def test_add_custom_js(self):
        self.driver.get(self.url+self.CUSTOM_PAGE)
        # Wait for loading page
        sleep(2)
        # Add javascript
        system_menu = self.driver.find_element_by_css_selector('a[href="#advanced"]')
        system_menu.click()
        js_editor = self.driver.find_element_by_id('ta_jsDesktopContent')
        js_editor.send_keys('console.log(\'just_a_test\');')
        save_button = js_editor = self.driver.find_element_by_id('bt_savecustom')
        save_button.click();
        # Reload the page
        self.driver.get(self.url+self.CUSTOM_PAGE)
        shell_output = subprocess.check_output(["docker", "exec", "-it", "nextdom-test-custom-js-css", "rm", "-f", "/var/www/html/var/custom/custom.js"])
        self.assertIsNotNone(system_menu)
        self.assertEqual(0, len(self.driver.get_log('browser')))
        print(self.driver.get_log('browser'))

# Entry point
if __name__ == "__main__":
    if len(sys.argv) < 4:
        print('Usage : ' + sys.argv[0]+ ' url login password')
    else:
        if sys.argv[1][:-1] == '/':
            RescuePage.url = sys.argv[1]
        else:
            RescuePage.url = sys.argv[1]+'/'
        RescuePage.login = sys.argv[2]
        RescuePage.password = sys.argv[3]
        # unittest use sys.argv
        del sys.argv[1:]
        # failfast=True pour arrêter à la première erreur
        unittest.main()
