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

class MigrationPage(unittest.TestCase):
    """Test all pages linked in administration page
    """
    driver = None
    url = None
    login = None
    password = None

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
            print(err.keys())
            print(err['code'])
            if err['code'] == -32000:
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

    def test_dashboard_page(self):
        self.driver.get(self.url+'index.php?v=d&p=dashboard')
        sleep(4)
        self.assertIsNotNone(self.driver.page_source.find('Maison'))
        self.assertEqual(0, len(self.driver.get_log('browser')))

    def test_dashboard_page(self):
        self.driver.get(self.url+'index.php?v=d&p=backup')
        sleep(4)
        self.assertIsNotNone(self.driver.find_element_by_css_selector('option[value="/var/www/html/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz"]'))
        self.assertEqual(0, len(self.driver.get_log('browser')))

# Entry point
if __name__ == "__main__":
    if len(sys.argv) < 4:
        print('Usage : ' + sys.argv[0]+ ' url login password')
    else:
        if sys.argv[1][:-1] == '/':
            MigrationPage.url = sys.argv[1]
        else:
            MigrationPage.url = sys.argv[1]+'/'
        MigrationPage.login = sys.argv[2]
        MigrationPage.password = sys.argv[3]
        # unittest use sys.argv
        del sys.argv[1:]
        # failfast=True pour arrêter à la première erreur
        unittest.main()
