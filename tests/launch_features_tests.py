"""Launch NextDom features tests
"""
import sys
from tests.libs.tests_funcs import *

def scenarios_tests():
    """Starts tests related to the scenarios
    """
    container_name = 'scenarios'
    print_subtitle('Scenarios')
    start_test_container(container_name)
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/smallest_scenario.sql')
    run_test('tests/scenarios.py')
    remove_test_container(container_name)

def plugins_tests():
    """Starts tests related to the plugins
    """
    container_name = 'plugins'
    print_subtitle('Plugins')
    start_test_container(container_name)
    exec_command_in_container(
        container_name,
        '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/plugin_test.sql')
    run_test('tests/plugins.py')
    remove_test_container(container_name)

if __name__ == "__main__":
    TESTS_LIST = {
        'scenarios': scenarios_tests,
        'plugins': plugins_tests
    }
    init_docker()
    if len(sys.argv) == 1:
        start_all_tests('Features', TESTS_LIST)
    else:
        start_specific_test(sys.argv[1], TESTS_LIST)
