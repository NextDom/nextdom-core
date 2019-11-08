#!/usr/bin/env python3

"""Launch NextDom GUI tests
"""
import sys
from tests.libs.tests_funcs import *

NEXTDOM_URL = 'http://127.0.0.1:8765'
NEXTDOM_LOGIN = 'admin'
NEXTDOM_PASSWORD = 'nextdom-test'

def ajax_tests():
    """Starts gui tests related to the Custom JS and CSS page
    """
    container_name = 'compatibility'
    print_subtitle('Ajax')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    # Create standard user
    exec_command_in_container(container_name, '/usr/bin/mysql -u root nextdomdev -e "INSERT INTO \\`user\\` VALUES (NULL, \'user\', \'user\', SHA2(\'nextdom-test\', 512), \'{\\\"localOnly\\\":\\\"0\\\",\\\"lastConnection\\\":\\\"\\\"}\', \'VD5OOmHSVT3VFYjng4XEaZF5wAI9jEi8\', \'[]\', 1)"') #pylint: disable=line-too-long
    return_code = os.system('cd .. && ./vendor/bin/phpunit --configuration tests/compatibility/phpunit.xml --testsuite AllTests') #pylint: disable=line-too-long
    remove_test_container(container_name)
    if return_code != 0:
        sys.exit(return_code)
    return False

if __name__ == "__main__":
    TESTS_LIST = {
        'ajax': ajax_tests
    }
    init_docker()
    if len(sys.argv) == 1:
        start_all_tests('Compatibility Tests', TESTS_LIST)
    else:
        start_specific_test(sys.argv[1], TESTS_LIST)
