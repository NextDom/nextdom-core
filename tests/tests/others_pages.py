#!/usr/bin/env python3
"""Run all tests of specific pages
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class OtherPages(BaseGuiTest):
    """Test all specifics pages
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_display_page(self):
        """Test display page
        """
        self.goto('index.php?v=d&p=display')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_with_text('h3', 'My Room'))
        self.get_element_by_id('bt_removeHistory').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_emptyRemoveHistory'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_interact_page(self):
        """Test interact page
        """
        self.goto('index.php?v=d&p=interact')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_regenerateInteract'))
        self.get_element_by_id('bt_testInteract').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('in_testInteractQuery'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_health_page(self):
        """Test health page
        """
        self.goto('index.php?v=d&p=health')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_benchmarkNextDom'))
        self.click_on_invisible('a[href="#div_Plugins"]')
        self.assertIsNotNone(self.get_element_by_id('div_Plugins'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_eqanalyse_page(self):
        """Test eqAnalyse page
        """
        self.goto('index.php?v=d&p=eqAnalyse')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_css('a[href="index.php?v=d&p=eqlogic"]'))
        self.click_on_invisible('a[href="#battery"]')
        self.assertIsNotNone(self.get_element_by_id('battery'))
        self.click_on_invisible('a[href="#alertEqlogic"]')
        self.assertIsNotNone(self.get_element_by_id('alertEqlogic'))
        self.click_on_invisible('a[href="#actionCmd"]')
        self.assertIsNotNone(self.get_element_by_id('actionCmd'))
        self.click_on_invisible('a[href="#alertCmd"]')
        self.assertIsNotNone(self.get_element_by_id('alertCmd'))
        self.click_on_invisible('a[href="#deadCmd"]')
        self.assertIsNotNone(self.get_element_by_id('deadCmd'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_history_page(self):
        """Test history page
        """
        self.goto('index.php?v=d&p=history')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_displayCalculHistory'))
        self.get_element_by_id('bt_openCmdHistoryConfigure').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_cmdConfigureCmdHistoryApply'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_timeline_page(self):
        """Test timeline page
        """
        self.goto('index.php?v=d&p=timeline')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_configureTimelineScenario'))
        self.get_element_by_id('bt_configureTimelineScenario').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_saveSummaryScenario'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.get_element_by_id('bt_configureTimelineCommand').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_cmdConfigureCmdHistoryApply'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_plugin_page(self):
        """Test plugin page
        """
        self.goto('index.php?v=d&p=plugin')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_addPluginFromOtherSource'))
        self.get_element_by_id('bt_addPluginFromOtherSource').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_css('select[data-l1key="source"]'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.click_on_invisible('a[href="#actifs"]')
        self.assertIsNotNone(self.get_element_by_id('actifs'))
        self.click_on_invisible('a[href="#inactifs"]')
        self.assertIsNotNone(self.get_element_by_id('inactifs'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_update_page(self):
        """Test update page
        """
        self.goto('index.php?v=d&p=update')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.get_element_by_id('logDialogButton').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('updateLog'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.click_on_invisible('a[href="#core"]')
        self.assertIsNotNone(self.get_element_by_id('core'))
        self.click_on_invisible('a[href="#plugins"]')
        self.assertIsNotNone(self.get_element_by_id('plugins'))
        self.click_on_invisible('a[href="#widgets"]')
        self.assertIsNotNone(self.get_element_by_id('widgets'))
        self.click_on_invisible('a[href="#scripts"]')
        self.assertIsNotNone(self.get_element_by_id('scripts'))
        self.click_on_invisible('a[href="#others"]')
        self.assertIsNotNone(self.get_element_by_id('others'))
        self.assertEqual(0, len(self.get_js_logs()))

    def test_header_modals(self):
        """Test modals in header
        """
        self.goto('index.php')
        self.get_element_by_id('bt_messageModal').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('bt_clearMessage'))
        ActionChains(self.driver).send_keys(Keys.ESCAPE).perform()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_profils_page(self):
        """Test profils page
        """
        self.goto('index.php?v=d&p=profils')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_saveProfils'))
        # Tab profil
        self.click_on_invisible('a[href="#profil"]')
        self.assertIsNotNone(self.get_element_by_id('avatar-preview'))
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

    def test_users_page(self):
        """Test users page
        """
        self.goto('index.php?v=d&p=users')
        self.assertIsNotNone(self.get_link_by_title('Retour'))
        self.assertIsNotNone(self.get_element_by_id('bt_saveUser'))
        self.assertIsNotNone(self.get_element_with_text('span', 'simple'))
        self.assertIsNotNone(self.get_element_with_text('span', 'admin'))
        self.get_element_by_id('bt_addUser').click()
        sleep(2)
        self.assertIsNotNone(self.get_element_by_id('in_newUserLogin'))
        self.get_element_by_css('#md_newUser a.btn-danger').click()
        sleep(1)
        self.assertEqual(0, len(self.get_js_logs()))

# Entry point
if __name__ == "__main__":
    OtherPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
