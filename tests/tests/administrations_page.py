#!/usr/bin/env python3
"""Run all tests of administrations page
"""
import unittest
import sys
from time import sleep
from selenium.webdriver.common.action_chains import ActionChains
from libs.base_gui_test import BaseGuiTest

class AdministrationPages(BaseGuiTest):
    """Test all pages linked in administration page
    """
    @classmethod
    def setUpClass(cls):
        """Init chrome driver
        """
        cls.init_driver(True)

    def test_go_to_administration(self):
        """Go to the administration like human
        """
        # Wait for dashboard objets loading
        sleep(12)
        # Put the mouse hover the menu to show the administration link
        menu_to_hover = self.get_element_by_css('.treeview>a>i.fa-cog')
        menu_hover_action = ActionChains(self.driver).move_to_element(menu_to_hover)
        menu_hover_action.perform()
        # Wait for mouse event
        sleep(2)
        # Click on menu item
        self.get_element_by_css('a[href="index.php?v=d&p=administration"]').click()
        # Wait for loading page
        sleep(3)
        api_button = self.get_element_by_css('a[href="index.php?v=d&p=api"]')
        self.assertIsNotNone(api_button)

    def test_administration_page(self):
        """Test global administration page
        """
        self.goto('index.php?v=d&p=administration')
        users_button = self.get_element_by_css('a[href="index.php?v=d&p=users"]')
        interact_admin_button = self.get_element_by_css('a[href="index.php?v=d&p=interact_admin"]')
        cache_button = self.get_element_by_css('a[href="index.php?v=d&p=cache"]')
        self.assertIsNotNone(users_button)
        self.assertIsNotNone(interact_admin_button)
        self.assertIsNotNone(cache_button)
        self.assertEqual(0, len(self.get_js_logs()))

    def test_users_page(self):
        """Test user administration page
        """
        self.goto('index.php?v=d&p=users')
        change_hash_button = self.get_element_by_css('.bt_changeHash')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(change_hash_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_api_page(self):
        """Test API administration page
        """
        self.goto('index.php?v=d&p=api')
        regenerate_api_button = self.get_element_by_css('.bt_regenerate_api')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(regenerate_api_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_network_page(self):
        """Test network administration page
        """
        self.goto('index.php?v=d&p=network')
        local_ip_input = self.get_element_by_css('input[data-l1key="network::localip"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(local_ip_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_security_page(self):
        """Test security administration page
        """
        self.goto('index.php?v=d&p=security')
        remove_banned_button = self.get_element_by_id('bt_savesecurity')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(remove_banned_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_cache_page(self):
        """Test cache administration page
        """
        self.goto('index.php?v=d&p=cache')
        clean_cache_button = self.get_element_by_id('bt_cleanCache')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(clean_cache_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_service_page(self):
        """Test service administration page
        """
        self.goto('index.php?v=d&p=update_admin')
        test_repo_buttons = self.driver.find_element_by_class_name('testRepoConnection')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(test_repo_buttons)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_general_page(self):
        """Test general administration page
        """
        self.goto('index.php?v=d&p=general')
        reset_hardware_button = self.get_element_by_id('bt_resetHardwareType')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(reset_hardware_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_custom_page(self):
        """Test custom administration page
        """
        self.goto('index.php?v=d&p=custom')
        color2_input = self.get_element_by_css('input[data-l1key="theme:color2"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(color2_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_profils_page(self):
        """Test profils administration page
        """
        self.goto('index.php?v=d&p=profils')
        avatar_upload_button = self.get_element_by_id('user_avatar')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(avatar_upload_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_commandes_page(self):
        """Test commands administration page
        """
        self.goto('index.php?v=d&p=commandes')
        display_stats_input = self.get_element_by_css('input[data-l1key="displayStatsWidget"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(display_stats_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_links_page(self):
        """Test links administration page
        """
        tested_css_selector = 'input[data-l1key="graphlink::scenario::drill"]'
        self.goto('index.php?v=d&p=links')
        scenario_depth_input = self.get_element_by_css(tested_css_selector)
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(scenario_depth_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_interact_admin_page(self):
        """Test interact administration page
        """
        self.goto('index.php?v=d&p=interact_admin')
        add_color_button = self.get_element_by_id('bt_addColorConvert')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(add_color_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_summary_page(self):
        """Test summary administration page
        """
        self.goto('index.php?v=d&p=summary')
        add_summary_button = self.get_element_by_id('bt_addObjectSummary')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(add_summary_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_reports_admin_page(self):
        """Test reports administration page
        """
        self.goto('index.php?v=d&p=reports_admin')
        report_delay_input = self.get_element_by_css('input[data-l1key="report::delay"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(report_delay_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_log_admin_page(self):
        """Test log view administration page
        """
        self.goto('index.php?v=d&p=log_admin')
        max_event_input = self.get_element_by_css('input[data-l1key="timeline::maxevent"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(max_event_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_health_page(self):
        """Test health administration page
        """
        self.goto('index.php?v=d&p=health')
        sleep(10)
        info_boxes = self.driver.find_elements_by_css_selector('.info-box-content')
        back_button = self.get_link_by_title('Retour')
        self.assertTrue(len(info_boxes) > 15)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_cron_page(self):
        """Test cron administration page
        """
        self.goto('index.php?v=d&p=cron')
        add_cron_button = self.get_element_by_id('bt_addCron')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(add_cron_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_eqanalyse_page(self):
        """Test eqanalyse administration page
        """
        self.goto('index.php?v=d&p=eqAnalyse')
        tabs_div = self.get_element_by_id('ul_tabBatteryAlert')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(tabs_div)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_realtime_page(self):
        """Test realtime administration page
        """
        self.goto('index.php?v=d&p=realtime')
        remove_logs_button = self.get_element_by_id('bt_logrealtimeremoveLog')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(remove_logs_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_history_page(self):
        """Test history administration page
        """
        self.goto('index.php?v=d&p=history')
        calc_history_input = self.get_element_by_id('in_calculHistory')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(calc_history_input)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_report_page(self):
        """Test report administration page
        """
        self.goto('index.php?v=d&p=report')
        report_search_list = self.get_element_by_id('ul_report')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(report_search_list)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_log_page(self):
        """Test log administration page
        """
        self.goto('index.php?v=d&p=log')
        remove_all_button = self.get_element_by_id('bt_removeAllLog')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(remove_all_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_display_page(self):
        """Test display administration page
        """
        self.goto('index.php?v=d&p=display')
        activ_display_checkbox = self.get_element_by_id('cb_actifDisplay')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(activ_display_checkbox)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_backup_page(self):
        """Test backup administration page
        """
        self.goto('index.php?v=d&p=backup')
        log_button = self.get_element_by_id('bt_saveOpenLog')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(log_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_update_page(self):
        """Test update administration page
        """
        self.goto('index.php?v=d&p=update')
        sleep(10)
        selective_update_button = self.get_element_by_id('bt_updateNextDom')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(selective_update_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_note_page(self):
        """Test note administration page
        """
        self.goto('index.php?v=d&p=note')
        add_note_button = self.get_element_by_id('bt_noteManagerAdd')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(add_note_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_osdb_page(self):
        """Test osdb administration page
        """
        self.goto('index.php?v=d&p=osdb')
        console_button = self.driver.find_element_by_xpath('//a[@href="index.php?v=d&p=system"]')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(console_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

    def test_interact_page(self):
        """Test interact administration page
        """
        self.goto('index.php?v=d&p=interact')
        interact_button = self.get_element_by_id('bt_testInteract2')
        back_button = self.get_link_by_title('Retour')
        self.assertIsNotNone(interact_button)
        self.assertIsNotNone(back_button)
        self.assertEqual(0, len(self.get_js_logs()))
        back_button.click()

# Entry point
if __name__ == "__main__":
    AdministrationPages.parse_cli_args()
    # unittest use sys.argv
    del sys.argv[1:]
    # failfast=True pour arrêter à la première erreur
    unittest.main(failfast=True)
