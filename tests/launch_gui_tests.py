#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Launch NextDom GUI tests
"""
import sys
from tests.libs.tests_funcs import *

NEXTDOM_URL = 'http://127.0.0.1:8765'
NEXTDOM_LOGIN = 'admin'
NEXTDOM_PASSWORD = 'nextdom-test'


def first_use_tests():
    """Starts gui tests related to the first use page
    """
    container_name = 'firstuse'
    print_subtitle('First use tests')
    start_test_container(container_name)
    test_result = run_test('tests/first_use_page.py', [NEXTDOM_URL])
    remove_test_container(container_name)
    print(test_result)
    return test_result


def administration_tests():
    """Starts administration gui tests
    """
    container_name = 'administration'
    print_subtitle('Administration tests')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    # Load somes data
    exec_command_in_container(
        container_name,
        '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/tests_fixtures.sql')
    print_subtitle('Administration page')
    test_result = run_test('tests/administration_page.py',
                           [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    print_subtitle('Admins pages')
    test_result += run_test('tests/admin_pages.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    print_subtitle('Diagnotics page')
    test_result += run_test('tests/diagnostic_pages.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    print_subtitle('Params page')
    test_result += run_test('tests/params_pages.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    print_subtitle('Tools page')
    test_result += run_test('tests/tools_pages.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    remove_test_container(container_name)
    print(test_result)
    return test_result


def specific_tests():
    """Starts others gui tests
    """
    container_name = 'specific'
    print_subtitle('Specific tests')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    # Load somes data
    exec_command_in_container(
        container_name,
        '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/tests_fixtures.sql')
    print_subtitle('Connection page')
    test_result = run_test('tests/connection_page.py',
                           [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])  # pylint: disable=line-too-long
    test_result += run_test('tests/view_page.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])  # pylint: disable=line-too-long
    test_result += run_test('tests/plan_page.py',
                            [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])  # pylint: disable=line-too-long
    remove_test_container(container_name)
    print(test_result)
    return test_result


def plugins_tests():
    """Starts gui tests related to the plugin page
    """
    container_name = 'plugins'
    print_subtitle('Plugins tests')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    exec_command_in_container(
        container_name,
        '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/tests_fixtures.sql')
    test_result = run_test('tests/plugins_page.py', [NEXTDOM_URL, NEXTDOM_LOGIN,
                                                     NEXTDOM_PASSWORD])  # pylint: disable=line-too-long
    remove_test_container(container_name)
    print(test_result)
    return test_result


def modal_tests():
    """Starts gui tests related to the modal pages
    """
    container_name = 'modal'
    print_subtitle('Modal tests')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    exec_command_in_container(
        container_name,
        '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/tests_fixtures.sql')
    test_result = run_test('tests/modal_pages.py', [NEXTDOM_URL, NEXTDOM_LOGIN,
                                                    NEXTDOM_PASSWORD])  # pylint: disable=line-too-long
    remove_test_container(container_name)
    print(test_result)
    return test_result


if __name__ == "__main__":
    TESTS_LIST = {
        'first_use': first_use_tests,
        'administration': administration_tests,
        'specific': specific_tests,
        'plugins': plugins_tests,
        'modal': modal_tests
    }
    init_docker()
    RESULT = False
    if len(sys.argv) == 1:
        RESULT = start_all_tests('GUI Tests', TESTS_LIST)
    else:
        if sys.argv[1] == '--headless':
            RESULT = start_all_tests('GUI Tests', TESTS_LIST)
        else:
            print_title('GUI Tests')
            RESULT = start_specific_test(sys.argv[1], TESTS_LIST)
    print("RESULT")
    print(RESULT)
    if RESULT != 0:
        sys.exit(1)
