#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of view page
"""
import unittest
import sys
from time import sleep
from libs.base_gui_test import BaseGuiTest

class PlanPage(BaseGuiTest):
    """Test all page linked to plan
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_plan_page(self):
        """Test plan page
        """
        self.goto('index.php?v=d&p=plan')
        sleep(2)
        self.assertIsNotNone(self.get_element_by_css('div[data-eqlogic_id="4"]'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_config_modal(self):
        """Test config modal
        """
        self.goto('index.php?v=d&p=plan')
        sleep(3)
        self.execute_js('showConfigModal();')
        sleep(4)
        self.assertIsNotNone(self.get_element_by_id('bt_saveConfigurePlanHeader'))
        self.assertIsNotNone(self.get_element_by_id('bt_chooseIcon'))
        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    PlanPage.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
