#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Run all tests of params section of administration pages
"""
import unittest
import sys
from libs.base_gui_test import BaseGuiTest

class AdminParamsPages(BaseGuiTest):
    """Test all pages linked in params section administration page
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_general_page(self):
        """Test general administration page
        """
        self.goto('index.php?v=d&p=general')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        reset_hardware_button = self.get_element_by_id('bt_refreshHardwareType')
        self.assertIsNotNone(reset_hardware_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_profils_page(self):
        """Test profils administration page
        """
        self.goto('index.php?v=d&p=profils')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        self.assertIsNotNone(self.get_element_by_id('bt_saveProfils'))
        # Tab profil
        self.click_on_invisible('a[href="#profil"]')
        self.assertIsNotNone(self.get_element_by_id('avatar-preview'))
        self.assertIsNotNone(self.get_element_by_id('user_avatar'))
        # Tab theme
        self.click_on_invisible('a[href="#themetab"]')
        self.assertIsNotNone(self.get_element_by_id('themeIcon'))
        # Tab widget
        self.click_on_invisible('a[href="#widgettab"]')
        self.assertIsNotNone(self.get_element_by_css('input[data-l2key="widget::background-opacity::plan"]')) #pylint: disable=line-too-long
        # Tab tiles
        self.click_on_invisible('a[href="#colortab"]')
        self.assertIsNotNone(self.get_element_by_css('input[data-l1key="widget::radius"]'))
        # Tab notification
        self.click_on_invisible('a[href="#notificationtab"]')
        self.assertIsNotNone(self.get_element_by_css('input[data-l1key="notify::timeout"]'))
        # Tab interface
        self.click_on_invisible('a[href="#interfacetab"]')
        self.assertIsNotNone(self.get_element_by_id('displayViewByDefault'))
        # Tab security
        self.click_on_invisible('a[href="#securitytab"]')
        self.assertIsNotNone(self.get_element_by_id('bt_configureTwoFactorAuthentification'))
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_commandes_page(self):
        """Test commands administration page
        """
        self.goto('index.php?v=d&p=commandes')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        display_stats_input = self.get_element_by_css('input[data-l1key="displayStatsWidget"]')
        self.assertIsNotNone(display_stats_input)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_links_page(self):
        """Test links administration page
        """
        self.goto('index.php?v=d&p=links')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        scenario_depth_input = self.get_element_by_css('input[data-l1key="graphlink::scenario::drill"]') #pylint: disable=line-too-long
        self.assertIsNotNone(scenario_depth_input)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_interact_config_page(self):
        """Test interact config administration page
        """
        self.goto('index.php?v=d&p=interact_config')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        add_color_button = self.get_element_by_id('bt_addColorConvert')
        self.assertIsNotNone(add_color_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_eqlogic_page(self):
        """Test eqlogic administration page
        """
        self.goto('index.php?v=d&p=eqlogic')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        eqlogic_disable_try = self.get_element_by_css('input[data-l1key="numberOfTryBeforeEqLogicDisable"]')
        self.assertIsNotNone(eqlogic_disable_try)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_summary_page(self):
        """Test summary administration page
        """
        self.goto('index.php?v=d&p=summary')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        add_summary_button = self.get_element_by_id('bt_addObjectSummary')
        self.assertIsNotNone(add_summary_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_report_config_page(self):
        """Test report config administration page
        """
        self.goto('index.php?v=d&p=report_config')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        report_delay_input = self.get_element_by_css('input[data-l1key="report::delay"]')
        self.assertIsNotNone(report_delay_input)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_log_config_page(self):
        """Test log config administration page
        """
        self.goto('index.php?v=d&p=log_config')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(back_button)
        max_event_input = self.get_element_by_css('input[data-l1key="timeline::maxevent"]')
        self.assertIsNotNone(max_event_input)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()


# Entry point
if __name__ == "__main__":
    AdminParamsPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
