"""Launch NextDom GUI tests
"""
import sys
from tests.libs.tests_funcs import *

def php_tests():
    """Starts gui tests related to the Custom JS and CSS page
    """
    os.system('cd .. && ./vendor/bin/phpunit --configuration tests/phpunit_tests/phpunit.xml --testsuite AllTests') #pylint: disable=line-too-long

if __name__ == "__main__":
    TESTS_LIST = {
        'php': php_tests
    }
    if len(sys.argv) == 1:
        start_all_tests('PHPUnit Tests', TESTS_LIST, False)
    else:
        start_specific_test(sys.argv[1], TESTS_LIST)
