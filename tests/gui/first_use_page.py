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

class FirstUsePage(unittest.TestCase):
    """Test connection page and connection process
    """
    driver = None
    url = 'http://127.0.0.1:8765'
    login = 'admin'
    password = 'admin'

    @classmethod
    def setUpClass(cls):
        driver_path = os.path.dirname(os.path.abspath(__file__)) + os.sep + 'chromedriver'
        try:
            desired_capabilities = DesiredCapabilities.CHROME.copy()
            desired_capabilities['loggingPrefs'] = { 'browser': 'SEVERE' }
            cls.driver = webdriver.Chrome(desired_capabilities=desired_capabilities,executable_path=driver_path)
            cls.driver.get(cls.url)
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

    def reset_first_use(self):
        os.system('./scripts/sed_in_docker.sh "nextdom::firstUse = 0" "nextdom::firstUse = 1" /var/www/html/core/config/default.config.ini nextdom-test-firstuse')
        os.system('docker exec -i nextdom-test-firstuse /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2(\'' + self.password + '\', 512)"')
        os.system('docker exec -i nextdom-test-firstuse /usr/bin/mysql -u root nextdomdev <<< "UPDATE config SET \\`value\\` = 1 WHERE \\`key\\` = \'nextdom::firstUse\'"')

    def test_first_use_shortcuts(self):
        self.reset_first_use()
        self.driver.get(self.url)
        # Wait for loading page
        sleep(5)
        self.assertEquals(0, len(self.driver.get_log('browser')))
        step_1_shortcut = self.driver.find_element_by_css_selector('a[href="#step-1"]')
        step_2_shortcut = self.driver.find_element_by_css_selector('a[href="#step-2"]')
        step_3_shortcut = self.driver.find_element_by_css_selector('a[href="#step-3"]')
        step_4_shortcut = self.driver.find_element_by_css_selector('a[href="#step-4"]')
        self.assertIsNotNone(step_1_shortcut)
        self.assertIsNotNone(step_2_shortcut)
        self.assertIsNotNone(step_3_shortcut)
        self.assertIsNotNone(step_4_shortcut)
        step_2_button = self.driver.find_element_by_id('toStep2')
        password1_button = self.driver.find_element_by_id('in_change_password')
        password2_button = self.driver.find_element_by_id('in_change_passwordToo')
        step_3_button = self.driver.find_element_by_id('toStep3')
        step_4_skip_button = self.driver.find_element_by_id('skipStep4')
        finish_button = self.driver.find_element_by_id('finishConf')

        self.assertTrue(step_1_shortcut.is_displayed())
        self.assertTrue(step_2_shortcut.is_displayed())
        self.assertTrue(step_3_shortcut.is_displayed())
        self.assertTrue(step_4_shortcut.is_displayed())
        # Step 1
        sleep(5)
        self.assertTrue(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_2_shortcut.click()
        # Step 2
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertTrue(password1_button.is_displayed())
        self.assertTrue(password2_button.is_displayed())
        self.assertTrue(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_3_shortcut.click()
        # Step 3
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertTrue(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_4_shortcut.click()
        # Step 4
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(password1_button.is_displayed())
        self.assertFalse(password2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertTrue(finish_button.is_displayed())
        finish_button.click()
        # Stay in fist connect page
        self.assertEquals(0, len(self.driver.get_log('browser')))
        self.assertIn("connexion", self.driver.title)

    def test_full_process(self):
        self.reset_first_use()
        self.driver.get(self.url)
        # Wait for loading page
        sleep(5)
        self.assertEquals(0, len(self.driver.get_log('browser')))
        step_2_button = self.driver.find_element_by_id('toStep2')
        password1_button = self.driver.find_element_by_id('in_change_password')
        password2_button = self.driver.find_element_by_id('in_change_passwordToo')
        step_3_button = self.driver.find_element_by_id('toStep3')
        step_4_skip_button = self.driver.find_element_by_id('skipStep4')
        finish_button = self.driver.find_element_by_id('finishConf')
        self.assertIsNotNone(step_2_button)
        self.assertIsNotNone(password1_button)
        self.assertIsNotNone(password2_button)
        self.assertIsNotNone(step_3_button)
        self.assertIsNotNone(step_4_skip_button)
        self.assertIsNotNone(finish_button)
        # Step 1
        sleep(5)
        self.assertTrue(step_2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_2_button.click()
        # Step 2
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertTrue(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        password1_button.send_keys(self.password)
        password2_button.send_keys(self.password)
        step_3_button.click()
        # Step 3
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertTrue(step_4_skip_button.is_displayed())
        self.assertFalse(finish_button.is_displayed())
        step_4_skip_button.click()
        # Step 4
        sleep(5)
        self.assertFalse(step_2_button.is_displayed())
        self.assertFalse(step_3_button.is_displayed())
        self.assertFalse(step_4_skip_button.is_displayed())
        self.assertTrue(finish_button.is_displayed())
        finish_button.click()
        # Stay in fist connect page
        self.assertEquals(0, len(self.driver.get_log('browser')))
        self.assertIn("Dashboard", self.driver.title)

# Entry point
if __name__ == "__main__":
    # failfast=True pour arrêter à la première erreur
    unittest.main()
