#!/usr/bin/env python3

import unittest
import sys
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class RescuePage(unittest.TestCase):
    """Test rescue pages and connection process
    """
    driver = None
    url = None
    login = None
    password = None
    RESCUE_PATTERN = 'index.php?v=d&rescue=1'

    @classmethod
    def setUpClass(cls):
        try:
            desired_capabilities = DesiredCapabilities.CHROME.copy()
            desired_capabilities['loggingPrefs'] = { 'browser': 'SEVERE' }
            cls.driver = webdriver.Chrome(desired_capabilities=desired_capabilities)
            cls.driver.get(cls.url)
            cls.connect_to_nextdom()
        except WebDriverException as err:
            print("Chromedriver needed to run tests on Chrome.")
            print("Download it on https://sites.google.com/a/chromium.org/chromedriver/downloads")
            exit(1)

    @classmethod
    def tearDownClass(cls):
        if cls.driver is not None and False:
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

    def test_rescue_page(self):
        self.driver.get(self.url+self.RESCUE_PATTERN)
        # Wait for loading page
        sleep(2)
        system_menu = self.driver.find_element_by_css('a[href="index.php?v=d&p=system&rescue=1"]')
        self.assertIsNotNone(system_menu)
        self.assertEqual(0, len(self.driver.get_log('browser')))

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
