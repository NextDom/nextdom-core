"""Launch NextDom code consistency tests
"""
from tests.libs.tests_funcs import *

def check_php():
    """Check php code consistency
    """
    print_subtitle('PHP syntax')
    output, status = get_command_output('find . -type f -name "*.php" -not -path "./vendor/*" | xargs -n1 php -l') #pylint: disable=line-too-long
    if status == 0:
        print_info('OK')
    else:
        print_error('Fail')
        print(output)

def check_python():
    """Check python code quality
    """
    print_subtitle('Python code quality')
    folders_to_test = ['', 'tests/', 'tests/libs/']
    error = False
    for folder_to_test in folders_to_test:
        output, status = get_command_output('python3 -m pylint --rcfile=.pylintrc --output-format=colorized ' + folder_to_test + '*.py') #pylint: disable=line-too-long
        if status != 0:
            print(output)
            print_warning('Code quality issues')
            error = True
    if not error:
        print_info('OK')

if __name__ == "__main__":
    print_title('Code consistency')
    check_php()
    check_python()
