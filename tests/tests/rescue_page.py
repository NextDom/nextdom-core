#!/usr/bin/env python3
"""Test rescue mode pages
"""
import unittest
import sys
from libs.base_gui_test import BaseGuiTest

class RescuePage(BaseGuiTest):
    """Test rescue mode pages
    """
    RESCUE_PATTERN = 'index.php?v=d&rescue=1'

    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_rescue_page(self):
        """Test rescue first page
        """
        self.goto(self.RESCUE_PATTERN)
        system_menu = self.get_element_by_css('a[href="index.php?v=d&p=system&rescue=1"]')
        self.assertIsNotNone(system_menu)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_system_page(self):
        """Test rescue system page
        """
        self.goto(self.RESCUE_PATTERN+'&p=system')
        commands_list = self.get_element_by_id('ul_listSystemHistory')
        self.assertIsNotNone(commands_list)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_database_page(self):
        """Test rescue database page
        """
        self.goto(self.RESCUE_PATTERN+'&p=database')
        sql_requests_list = self.get_element_by_id('ul_listSqlRequest')
        self.assertIsNotNone(sql_requests_list)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_editor_page(self):
        """Test rescue editor page
        """
        self.goto(self.RESCUE_PATTERN+'&p=editor')
        new_file_button = self.get_element_by_id('bt_createFile')
        self.assertIsNotNone(new_file_button)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_custom_page(self):
        """Test rescue custom page
        """
        self.goto(self.RESCUE_PATTERN+'&p=custom')
        custom_tabs_div = self.get_element_by_css('div.nav-tabs-custom')
        self.assertIsNotNone(custom_tabs_div)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_backup_page(self):
        """Test rescue backup page
        """
        self.goto(self.RESCUE_PATTERN+'&p=backup')
        launch_button = self.get_element_by_id('bt_backupNextDom')
        self.assertIsNotNone(launch_button)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_cron_page(self):
        """Test rescue cron page
        """
        self.goto(self.RESCUE_PATTERN+'&p=cron')
        add_cron_button = self.get_element_by_id('bt_addCron')
        self.assertIsNotNone(add_cron_button)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_log_page(self):
        """Test rescue log page
        """
        self.goto(self.RESCUE_PATTERN+'&p=log')
        remove_all_button = self.get_element_by_id('bt_removeAllLog')
        self.assertIsNotNone(remove_all_button)
        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    RescuePage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
