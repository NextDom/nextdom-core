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


def php_tests():
    #pylint: disable=line-too-long
    """Starts gui tests related to the Custom JS and CSS page
    """
    container_name = 'phpunit'
    start_test_container(container_name, NEXTDOM_PASSWORD)
    exec_command_in_container(
        container_name, 'bash /usr/share/nextdom/tests/install_chrome.sh > /dev/null 2>&1')
    exec_command_in_container(
        container_name, 'bash /usr/share/nextdom/tests/load_fixtures.sh --reset')
    exec_command_in_container(
        container_name, 'apt-get install -y php-phpdbg > /dev/null 2>&1')
    return_code = exec_command_in_container(
        container_name, 'bash -c "cd /var/www/html && export PANTHER_NO_SANDBOX=1 && export PANTHER_CHROME_ARGUMENTS=\"--disable-dev-shm-usage\" && phpdbg -d memory_limit=-1 -qrr phpunit --configuration tests/phpunit_tests/phpunit.xml --testsuite AllTests"')  # pylint: disable=line-too-long
#    return_code = exec_command_in_container(
#        container_name, 'bash -c "cd /var/www/html && phpdbg -d memory_limit=-1 -qrr phpunit --configuration tests/phpunit_tests/phpunit.xml --testsuite AllTestsNoGui"')  # pylint: disable=line-too-long
    copy_file_from_container(container_name, '/var/www/html/tests/coverage/clover.xml',
                             'coverage/')  # pylint: disable=line-too-long
    copy_file_from_container(container_name, '/var/www/html/tests/coverage/junitlog.xml',
                             'coverage/')  # pylint: disable=line-too-long
    remove_test_container(container_name)
    if return_code != 0:
        sys.exit(1)


if __name__ == "__main__":
    TESTS_LIST = {
        'php': php_tests
    }
    init_docker()
    if len(sys.argv) == 1:
        start_all_tests('PHPUnit Tests', TESTS_LIST, False)
    else:
        start_specific_test(sys.argv[1], TESTS_LIST)
