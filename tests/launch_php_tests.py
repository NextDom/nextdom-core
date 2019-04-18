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
    exec_command_in_container(container_name, '/bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins')
    exec_command_in_container(container_name, '/bin/chown www-data:www-data -R /var/www/html/plugins')
    exec_command_in_container(container_name, 'bash -c "mysql -u root nextdomdev < /var/www/html/tests/data/phpunit_tests_fixtures.sql"')
    exec_command_in_container(container_name, 'service cron stop > /dev/null')
    exec_command_in_container(container_name, 'apt-get install -y php-xdebug > /dev/null 2>&1')
    exec_command_in_container(container_name, 'bash -c "cd /var/www/html && vendor/bin/phpunit --configuration tests/phpunit_tests/phpunit.xml --testsuite AllTests"')
    copy_file_from_container(container_name, '/var/www/html/tests/coverage/clover.xml', 'coverage/')

if __name__ == "__main__":
    TESTS_LIST = {
        'php': php_tests
    }
    init_docker()
    if len(sys.argv) == 1:
        start_all_tests('PHPUnit Tests', TESTS_LIST, False)
    else:
        start_specific_test(sys.argv[1], TESTS_LIST)
