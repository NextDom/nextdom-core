"""Base class for gui tests
"""
import unittest
import os
import sys
from time import sleep
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.common.exceptions import WebDriverException

class BaseGuiTest(unittest.TestCase):
    """Base class for gui tests
    """
    driver = None
    url = None
    login = None
    password = None

    @classmethod
    def parse_cli_args(cls):
        """Parse CLI aguments
        """
        nb_args = len(sys.argv)
        if nb_args < 2:
            print('Usage : ' + sys.argv[0]+ ' url [login] [password]')
            sys.exit(1)
        else:
            if sys.argv[1][:-1] == '/':
                cls.url = sys.argv[1]
            else:
                cls.url = sys.argv[1]+'/'
            if nb_args > 2:
                cls.login = sys.argv[2]
            if nb_args > 3:
                cls.password = sys.argv[3]

    @classmethod
    def init_driver(cls, connect=False, js_logs='SEVERE'):
        """Init selenium web driver
        :param connect: Connect with user
        :type connect:  bool
        """
        driver_path = os.path.dirname(os.path.abspath(__file__ + os.sep + '..')) + os.sep + 'chromedriver' #pylint: disable=line-too-long
        try:
            options = webdriver.ChromeOptions()
            # For travis environment, disable render
            if os.uname().nodename.startswith('travis'):
                options.add_argument('headless')
                options.add_argument('disable-gpu')
            options.add_argument('window-size=1920x1080')
            desired_capabilities = DesiredCapabilities.CHROME.copy()
            desired_capabilities['loggingPrefs'] = {'browser': js_logs}
            cls.driver = webdriver.Chrome(desired_capabilities=desired_capabilities,
                                          executable_path=driver_path,
                                          chrome_options=options)
            cls.driver.get(cls.url)
            if connect:
                cls.connect_to_nextdom()
        except WebDriverException as err:
            print("Chromedriver needed to run tests on Chrome.")
            print("Download it on https://sites.google.com/a/chromium.org/chromedriver/downloads")
            print(err)
            exit(1)

    @classmethod
    def connect_to_nextdom(cls):
        """Login with user
        """
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

    @classmethod
    def tearDownClass(cls):
        """Close chrome at the end of tests
        """
        if cls.driver is not None:
            cls.driver.quit()

    def tearDown(self):
        """Wait 4 seconds after each tests
        """
        sleep(4)

    def get_element_by_id(self, css_id):
        """Get Html element by his id
        :param css_id: CSS id of the element
        :type css_id:  str
        :return:       Html element
        :rtype:        WebElement
        """
        return self.driver.find_element_by_id(css_id)

    def get_element_by_css(self, css_selector):
        """Get Html element by CSS selector
        :param css_selector: CSS id of the element
        :type css_selector:  str
        :return:             Html element
        :rtype:              WebElement
        """
        return self.driver.find_element_by_css_selector(css_selector)

    def get_link_by_title(self, title):
        """Get link html element by text title
        :param css_selector: Title of the link
        :type css_selector:  str
        :return:             Html element
        :rtype:              WebElement
        """
        return self.driver.find_element_by_link_text(title)

    def get_page_title(self):
        """Get the title of the page
        :return: Title of the page
        :rtype:  str
        """
        return self.driver.title

    def goto(self, page=''):
        """Goto to page
        :param page: Page to go
        :type page:  str
        """
        self.driver.get(self.url + page)
        sleep(4)

    def get_js_logs(self):
        """Get javascript browser logs
        :return: Javascript logs
        :rtype:  array
        """
        return self.driver.get_log('browser')
