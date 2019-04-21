"""Launch NextDom GUI tests
"""
from tests.libs.tests_funcs import *

NEXTDOM_URL = 'http://127.0.0.1:8765'
NEXTDOM_LOGIN = 'admin'
NEXTDOM_PASSWORD = 'nextdom-test'

if __name__ == "__main__":
    init_docker()
    start_test_container('test', NEXTDOM_PASSWORD)
    input('Press enter to stop container')
    remove_test_container('test')

