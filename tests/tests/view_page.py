#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of view page
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class ViewPage(BaseGuiTest):
    """Test all page linked to view
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_view_page(self):
        """Test view page
        """
        self.goto('index.php?v=d&p=view')
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_editViewWidgetOrder'))
        self.assertIsNotNone(self.get_element_by_css('div[data-eqlogic_id="4"]'))
        self.get_element_by_css('a[href="index.php?v=d&p=view_edit&view_id=1"]').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_addView'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_view_edit_page(self):
        """Test view edit page
        """
        self.goto('index.php?v=d&p=view_edit&view_id=1')
        sleep(2)
        self.get_element_by_id('bt_editView').click()
        sleep(3)
        self.assertIsNotNone(self.get_element_by_id('bt_saveConfigureView'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.get_element_by_id('bt_addviewZone').click()
        sleep(1)
        self.assertIsNotNone(self.get_element_by_id('sel_addEditviewZoneType'))
        self.get_element_by_css('.modal-dialog a.btn.btn-danger').click()
        sleep(1)
        self.get_element_by_css('.bt_addViewWidget').click()
        sleep(1)
        self.assertIsNotNone(self.get_element_by_id('table_mod_insertEqLogicValue_valueEqLogicToMessage'))
        self.get_element_by_css('.ui-dialog-buttonset button:first-child').click()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))





# Entry point
if __name__ == "__main__":
    ViewPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
