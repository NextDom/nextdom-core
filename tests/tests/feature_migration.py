#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Test backup migration feature
"""
import unittest
import sys
from libs.base_gui_test import BaseGuiTest

class MigrationTest(BaseGuiTest):
    """Test backup migration feature
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_dashboard_page(self):
        """Test if dashboard work after migration
        """
        self.goto('index.php?v=d&p=dashboard')
        self.assertIsNotNone(self.driver.page_source.find('Maison'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_backup_page(self):
        """Test if backup is detected
        """
        self.goto('index.php?v=d&p=backup')
        css = 'option[value="/var/lib/nextdom/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz"]' #pylint: disable=line-too-long
        select_item = self.get_element_by_css_wait(css)
        self.assertIsNotNone(select_item)
        self.assertEqual(0, len(self.get_js_logs()))


# Entry point
if __name__ == "__main__":
    MigrationTest.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
