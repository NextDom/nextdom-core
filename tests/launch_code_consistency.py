#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Launch NextDom code consistency tests
"""
import sys
import os
from pylibs.tests_funcs import *

def check_php():
    """Check php code consistency
    """
    print_subtitle('PHP syntax')
    cwd = os.path.realpath(os.curdir)
    test_dir = os.path.dirname(os.path.realpath(__file__))
    os.chdir(os.path.dirname(test_dir))
    output, status = get_command_output('find . -type f -name "*.php" -not -path "./vendor/*" -not -name "object.class.php" | xargs -n1 php -l') #pylint: disable=line-too-long
    os.chdir(cwd)
    error = False
    if status == 0:
        print_info('OK')
    else:
        print_error('Fail')
        print(output)
        error = True
    return error

def check_python():
    """Check python code quality
    """
    print_subtitle('Python code quality')
    folders_to_test = ['', 'pylibs/']
    error = False
    for folder_to_test in folders_to_test:
        output, status = get_command_output('python3 -m pylint --rcfile=.pylintrc --output-format=colorized ' + folder_to_test + '*.py') #pylint: disable=line-too-long
        if status != 0:
            print(output)
            print_warning('Code quality issues')
            error = True
    if not error:
        print_info('OK')
    return error

if __name__ == "__main__":
    TESTS_LIST = {
        'php': check_php,
        'python': check_python
    }
    if not start_all_tests('Code consistency', TESTS_LIST):
        sys.exit(1)
