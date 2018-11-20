#!/usr/bin/env python3

import unittest
import sys
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class ConnectionPage(unittest.TestCase):
    """Test connection page and connection process
    """
    driver = None
    url = None
    login = None
    password = None
    LOGOUT_PATTERN = 'index.php?v=d&logout=1'

    @classmethod
    def setUpClass(cls):
        try:
            desired_capabilities = DesiredCapabilities.CHROME.copy()
            desired_capabilities['loggingPrefs'] = { 'browser': 'SEVERE' }
            cls.driver = webdriver.Chrome(desired_capabilities=desired_capabilities)
            cls.driver.get(cls.url)
        except WebDriverException as err:
            print("Chromedriver needed to run tests on Chrome.")
            print("Download it on https://sites.google.com/a/chromium.org/chromedriver/downloads")
            exit(1)

    @classmethod
    def tearDownClass(cls):
        if cls.driver is not None:
            cls.driver.quit()

    def test_connection_page(self):
        self.driver.get(self.url+self.LOGOUT_PATTERN)
        # Wait for loading page
        sleep(2)
        login_input = self.driver.find_element_by_id('in_login_username')
        password_input = self.driver.find_element_by_id('in_login_password')
        connect_button = self.driver.find_element_by_id('bt_login_validate')
        remember_checkbox = self.driver.find_element_by_id('cb_storeConnection')
        self.assertIsNotNone(login_input)
        self.assertIsNotNone(password_input)
        self.assertIsNotNone(connect_button)
        self.assertIsNotNone(remember_checkbox)
        self.assertEquals(0, len(self.driver.get_log('browser')))

    def test_good_connection(self):
        self.driver.get(self.url+self.LOGOUT_PATTERN)
        # Wait for loading page
        sleep(2)
        login_input = self.driver.find_element_by_id('in_login_username')
        password_input = self.driver.find_element_by_id('in_login_password')
        connect_button = self.driver.find_element_by_id('bt_login_validate')
        login_input.send_keys(self.login)
        password_input.send_keys(self.password)
        connect_button.click()
        # Wait dashboard
        sleep(8)
        self.assertIn('Dashboard', self.driver.title)

    def test_enter_key_from_password_field(self):
        self.driver.get(self.url+self.LOGOUT_PATTERN)
        # Wait for loading page
        sleep(2)
        login_input = self.driver.find_element_by_id('in_login_username')
        password_input = self.driver.find_element_by_id('in_login_password')
        login_input.send_keys(self.login)
        password_input.send_keys('If this password work, you\'re crazy!')
        password_input.send_keys(Keys.RETURN)
        # Wait Dashboard
        sleep(8)
        self.assertIn('Connexion', self.driver.title)

# Entry point
if __name__ == "__main__":
    if len(sys.argv) < 4:
        print('Usage : ' + sys.argv[0]+ ' url login password')
    else:
        if sys.argv[1][:-1] == '/':
            ConnectionPage.url = sys.argv[1]
        else:
            ConnectionPage.url = sys.argv[1]+'/'
        ConnectionPage.login = sys.argv[2]
        ConnectionPage.password = sys.argv[3]
        # unittest use sys.argv
        del sys.argv[1:]
        # failfast=True pour arrêter à la première erreur
        unittest.main()
