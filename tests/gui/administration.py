#!/usr/bin/env python3

import unittest
import sys
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains


class AdministrationPages(unittest.TestCase):
    driver = None
    url = None
    login = None
    password = None

    @classmethod
    def setUpClass(cls):
        try:
            cls.driver = webdriver.Firefox()
            cls.driver.get(cls.url)
            cls.connect_to_nextdom()
        except WebDriverException as err:
            print("Geckodriver needed to run tests on Firefox.")
            print("Download it on https://github.com/mozilla/geckodriver/releases")
            exit(1)

    @classmethod
    def connect_to_nextdom(cls):
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

    def test_go_to_administration(self):
        # Wait for dashboard objets loading
        sleep(12)
        # Put the mouse hover the menu to show the administration link
        menu_to_hover = self.driver.find_element_by_css_selector('.treeview>a>i.fa-cog')
        menu_hover_action = ActionChains(self.driver).move_to_element(menu_to_hover)
        menu_hover_action.perform()
        # Wait for mouse event
        sleep(2)
        # Click on menu item
        self.driver.find_element_by_css_selector('a[href="index.php?v=d&p=administration"]').click()
        # Wait for loading page
        sleep(3)
        api_button = self.driver.find_element_by_css_selector('a[href="index.php?v=d&p=api"]')
        self.assertIsNotNone(api_button)

    def test_administration_page(self):
        self.driver.get(self.url+'index.php?v=d&p=administration')
        sleep(4)
        users_button = self.driver.find_element_by_css_selector('a[href="index.php?v=d&p=users"]')
        interact_admin_button = self.driver.find_element_by_css_selector('a[href="index.php?v=d&p=interact_admin"]')
        migration_button = self.driver.find_element_by_css_selector('a[href="index.php?v=d&p=migration"]')
        self.assertIsNotNone(users_button)
        self.assertIsNotNone(interact_admin_button)
        self.assertIsNotNone(migration_button)

    def test_users_page(self):
        self.driver.get(self.url+'index.php?v=d&p=users')
        sleep(4)
        change_hash_button = self.driver.find_element_by_css_selector('.bt_changeHash')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(change_hash_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_api_page(self):
        self.driver.get(self.url+'index.php?v=d&p=api')
        sleep(4)
        regenerate_api_button = self.driver.find_element_by_css_selector('.bt_regenerate_api')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(regenerate_api_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_network_page(self):
        self.driver.get(self.url+'index.php?v=d&p=network')
        sleep(4)
        local_ip_input = self.driver.find_element_by_css_selector('input[data-l1key="network::localip"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(local_ip_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_security_page(self):
        self.driver.get(self.url+'index.php?v=d&p=security')
        sleep(4)
        ldap_checkbox = self.driver.find_element_by_css_selector('input[data-l1key="ldap:enable"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(ldap_checkbox)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_cache_page(self):
        self.driver.get(self.url+'index.php?v=d&p=cache')
        sleep(4)
        clean_cache_button = self.driver.find_element_by_id('bt_cleanCache')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(clean_cache_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_service_page(self):
        self.driver.get(self.url+'index.php?v=d&p=update_admin')
        sleep(4)
        test_repo_buttons = self.driver.find_element_by_class_name('testRepoConnection')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(test_repo_buttons)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_general_page(self):
        self.driver.get(self.url+'index.php?v=d&p=general')
        sleep(4)
        reset_hardware_button = self.driver.find_element_by_id('bt_resetHardwareType')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(reset_hardware_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_custom_page(self):
        self.driver.get(self.url+'index.php?v=d&p=custom')
        sleep(4)
        color2_input = self.driver.find_element_by_css_selector('input[data-l1key="theme:color2"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(color2_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_profils_page(self):
        self.driver.get(self.url+'index.php?v=d&p=profils')
        sleep(4)
        avatar_upload_button = self.driver.find_element_by_id('user_avatar')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(avatar_upload_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_commandes_page(self):
        self.driver.get(self.url+'index.php?v=d&p=commandes')
        sleep(4)
        display_stats_input = self.driver.find_element_by_css_selector('input[data-l1key="displayStatsWidget"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(display_stats_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_links_page(self):
        self.driver.get(self.url+'index.php?v=d&p=links')
        sleep(4)
        scenario_depth_input = self.driver.find_element_by_css_selector('input[data-l1key="graphlink::scenario::drill"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(scenario_depth_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_interact_admin_page(self):
        self.driver.get(self.url+'index.php?v=d&p=interact_admin')
        sleep(4)
        add_color_button = self.driver.find_element_by_id('bt_addColorConvert')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(add_color_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_eqAnalyse_page(self):
        self.driver.get(self.url+'index.php?v=d&p=eqAnalyse')
        sleep(4)
        fails_limit_input = self.driver.find_element_by_css_selector('input[data-l1key="numberOfTryBeforeEqLogicDisable"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(fails_limit_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_summary_page(self):
        self.driver.get(self.url+'index.php?v=d&p=summary')
        sleep(4)
        add_summary_button = self.driver.find_element_by_id('bt_addObjectSummary')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(add_summary_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_reports_admin_page(self):
        self.driver.get(self.url+'index.php?v=d&p=reports_admin')
        sleep(4)
        report_delay_input = self.driver.find_element_by_css_selector('input[data-l1key="report::delay"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(report_delay_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_log_admin_page(self):
        self.driver.get(self.url+'index.php?v=d&p=log_admin')
        sleep(5)
        max_event_input = self.driver.find_element_by_css_selector('input[data-l1key="timeline::maxevent"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(max_event_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_health_page(self):
        self.driver.get(self.url+'index.php?v=d&p=health')
        sleep(15)
        info_boxes = self.driver.find_elements_by_css_selector('.info-box-content')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertTrue(len(info_boxes) > 15)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_cron_page(self):
        self.driver.get(self.url+'index.php?v=d&p=cron')
        sleep(4)
        add_cron_button = self.driver.find_element_by_id('bt_addCron')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(add_cron_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_eqAnalyse_page(self):
        self.driver.get(self.url+'index.php?v=d&p=eqAnalyse')
        sleep(4)
        tabs_div = self.driver.find_element_by_id('ul_tabBatteryAlert')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(tabs_div)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_realtime_page(self):
        self.driver.get(self.url+'index.php?v=d&p=realtime')
        sleep(4)
        remove_logs_button = self.driver.find_element_by_id('bt_logrealtimeremoveLog')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(remove_logs_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_history_page(self):
        self.driver.get(self.url+'index.php?v=d&p=history')
        sleep(4)
        calc_history_input = self.driver.find_element_by_id('in_calculHistory')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(calc_history_input)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_report_page(self):
        self.driver.get(self.url+'index.php?v=d&p=report')
        sleep(4)
        report_search_list = self.driver.find_element_by_id('ul_report')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(report_search_list)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_log_page(self):
        self.driver.get(self.url+'index.php?v=d&p=log')
        sleep(4)
        remove_all_button = self.driver.find_element_by_id('bt_removeAllLog')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(remove_all_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_display_page(self):
        self.driver.get(self.url+'index.php?v=d&p=display')
        sleep(4)
        activ_display_checkbox = self.driver.find_element_by_id('cb_actifDisplay')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(activ_display_checkbox)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_migration_page(self):
        self.driver.get(self.url+'index.php?v=d&p=migration')
        sleep(4)
        migration_button = self.driver.find_element_by_id('bt_migrationNextDom')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(migration_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_backup_page(self):
        self.driver.get(self.url+'index.php?v=d&p=backup')
        sleep(4)
        log_button = self.driver.find_element_by_id('bt_saveOpenLog')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(log_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_update_page(self):
        self.driver.get(self.url+'index.php?v=d&p=update')
        sleep(4)
        selective_update_button = self.driver.find_element_by_id('bt_updateNextDom')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(selective_update_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_note_page(self):
        self.driver.get(self.url+'index.php?v=d&p=note')
        sleep(4)
        add_note_button = self.driver.find_element_by_id('bt_noteManagerAdd')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(add_note_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_osdb_page(self):
        self.driver.get(self.url+'index.php?v=d&p=osdb')
        sleep(4)
        console_button = self.driver.find_element_by_xpath('//a[@href="index.php?v=d&p=system"]')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(console_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

    def test_interact_page(self):
        self.driver.get(self.url+'index.php?v=d&p=interact')
        sleep(4)
        interact_button = self.driver.find_element_by_id('bt_testInteract2')
        back_button = self.driver.find_element_by_link_text('Retour')
        self.assertIsNotNone(interact_button)
        self.assertIsNotNone(back_button)
        back_button.click()
        sleep(4)

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
