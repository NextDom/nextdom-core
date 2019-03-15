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

class AdministrationPages(unittest.TestCase):
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
        login_input = cls.driver.find_element_by_id('login')
        password_input = cls.driver.find_element_by_id('password')
        connect_button = cls.driver.find_element_by_id('submit')
        login_input.send_keys(cls.login)
        password_input.send_keys(cls.password)
        connect_button.click()
        # Wait animation ending
        sleep(4)

    def test_go_to_desktop_plugin_page(self):
        self.driver.get(self.url+'index.php?v=d&p=dashboard')
        # Wait for dashboard objets loading
        sleep(5)
        # Put the mouse hover the menu to show the plugin link
        menu_to_hover = self.driver.find_element_by_css_selector('.treeview>a>i.fa-puzzle-piece')
        menu_hover_action = ActionChains(self.driver).move_to_element(menu_to_hover)
        menu_hover_action.perform()
        # Wait for mouse event
        sleep(1)
        # Put the mouse hover the menu to show the programming link
        menu_programming_plugin = self.driver.find_element_by_css_selector('.treeview-menu>.treeview>a>i.fa-code').click()
        # Wait for mouse event
        sleep(1)
        # Click on menu item
        self.driver.find_element_by_css_selector('a[href="index.php?v=d&m=plugin4tests&p=plugin4tests"]').click()
        # Wait for loading page
        sleep(1)
        self.assertIn('Desktop plugin page', self.driver.page_source)

    def test_dashboard_widget(self):
        self.driver.get(self.url+'index.php?v=d&p=dashboard')
        sleep(4)
        widget = self.driver.find_element_by_id('div_ob1')
        widget_label = self.driver.find_element_by_css_selector('#div_ob1 .widget-name')
        widget_content = self.driver.find_element_by_css_selector('[data-eqlogic_id="1"]')
        self.assertIsNotNone(widget)
        self.assertIsNotNone(widget_label)
        self.assertIsNotNone(widget_content)
        self.assertIn('TEST EQLOGIC', widget_label.text)
        self.assertIn('Cmd 1', widget_content.text)

# Entry point
if __name__ == "__main__":
    if len(sys.argv) < 4:
        print('Usage : ' + sys.argv[0]+ ' url login password')
    else:
        if sys.argv[1][:-1] == '/':
            AdministrationPages.url = sys.argv[1]
        else:
            AdministrationPages.url = sys.argv[1]+'/'
        AdministrationPages.login = sys.argv[2]
        AdministrationPages.password = sys.argv[3]
        # unittest use sys.argv
        del sys.argv[1:]
        # failfast=True pour arrêter à la première erreur
        unittest.main()
