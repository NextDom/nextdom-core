#!/usr/bin/env python3

"""Check Jeedom compatibility
"""
import re
import sys
import os
from tests.libs.tests_funcs import *

AJAX_PATH = '/tmp/jeedom-core/core/ajax/'
CLASS_PATH = '/tmp/jeedom-core/core/class/'
NEXTDOM_CLASS_PATH = '../core/class/'
NEXTDOM_ENTITY_PATH = '../src/Model/Entity/'
TESTS_PATH = 'compatibility/'

def get_ajax_actions_from_file(ajax_file):
    """Get ajax actions from a single Jeedom file
    :param ajax_file: Path of the ajax file
    :type ajax_file:  str
    :return:          List of actions
    :rtype:           list
    """
    with open(AJAX_PATH + ajax_file, 'r', encoding="utf-8") as file_content:
        content = file_content.read()
        return re.findall(r'action\'\) == \'(.*?)\'', content)

def get_class_methods_from_file(class_file):
    """Get class methods from a single Jeedom file
    :param ajax_file: Path of the class file
    :type ajax_file:  str
    :return:          List of methods
    :rtype:           list
    """
    with open(CLASS_PATH + class_file, 'r', encoding="utf-8") as file_content:
        content = file_content.read()
        return re.findall(r'public (?:static )?function (\w+)\(', content)

def get_class_methods():
    """Get all class methods from Jeedom
    :return: All class methods actions
    :rtype:  dict
    """
    result = {}
    ignored_files = ('plan3d.class.php', 'eqReal.class.php', 'plan3dHeader.class.php')
    for class_file in os.listdir(CLASS_PATH):
        if class_file not in ('.', '..') and class_file not in ignored_files:
            class_methods = get_class_methods_from_file(class_file)
            if class_methods:
                result[class_file] = class_methods
    return result

def get_ajax_actions():
    """Get all ajax actions from Jeedom
    :return: All ajax actions
    :rtype:  dict
    """
    result = {}
    for ajax_file in os.listdir(AJAX_PATH):
        if ajax_file not in ('.', '..'):
            ajax_actions = get_ajax_actions_from_file(ajax_file)
            if ajax_actions:
                result[ajax_file] = ajax_actions
    return result

def check_ajax_file(file_to_check, actions_list):
    """Check actions from file
    :param file_to_check: Ajax file to check
    :param actions_list:  List of actions
    :type:                str
    :type:                list
    :return:              True if all actions are tested
    :rtype:               bool
    """
    result = True
    test_name = file_to_check.replace('.ajax.php', '').capitalize()
    if test_name == 'Jeedom':
        test_name = 'NextDom'
    # Ignore migration functionnality
    if test_name in ('Migrate', 'Plan3d'):
        return True
    # Special names
    if test_name == 'Datastore':
        test_name = 'DataStore'
    if test_name == 'Eqlogic':
        test_name = 'EqLogic'
    if test_name == 'Widgets':
        test_name = 'Widget'
    test_file = TESTS_PATH + 'Ajax' + test_name + 'Test.php'
    if os.path.isfile(test_file):
        with open(test_file, 'r', encoding="utf-8") as test_file_content:
            test_content = test_file_content.read()
            test_content = test_content.lower()
            for action in actions_list:
                if test_content.find('test' + action.lower()) == -1:
                    # Skip removed functions
                    if test_name == 'NextDom' and action in ('saveCustom', 'systemCorrectPackage'):
                        continue
                    if test_name == 'Repo' and action == 'pullInstall':
                        continue
                    print_warning('In ' + test_name + ', test for action ' + action + ' not found.') #pylint: disable=line-too-long
                    result = False
    else:
        print('File ' + test_file + ' not found.')
        result = False
    return result

def check_if_ajax_action_is_tested(actions_to_check):
    """Check if file has tests
    :param actions_to_check: List of all actions to test
    :type actions_to_check:  dict
    """
    result = True
    for file_to_check in actions_to_check.keys():
        if not check_ajax_file(file_to_check, actions_to_check[file_to_check]):
            result = False
    return result

def get_entity_content_if_exists(base_class_file_content):
    """Get content of the entity file
    :param base_class_file_content: Name of the entity file
    :type base_class_file_content:  str
    :return:                        Content of the entity file if exists
    :rtype:                         str
    """
    result = ''
    entity_regex = r'extends \\?NextDom\\Model\\Entity\\(\w+)'
    entity_file_re = re.findall(entity_regex, base_class_file_content)
    if entity_file_re:
        filename = NEXTDOM_ENTITY_PATH + entity_file_re[0] + '.php'
        with open(filename, encoding="utf-8") as entity_content:
            result = entity_content.read()
    return result

def check_class_methods_file(file_to_check, methods_list):
    """Check class methods from file
    :param file_to_check: Ajax file to check
    :param actions_list:  List of actions
    :type:                str
    :type:                list
    :return:              True if all actions are tested
    :rtype:               bool
    """
    result = True
    if file_to_check == 'jeedom.class.php':
        file_to_check = 'nextdom.class.php'
    # Ignore migration functionnality
    if file_to_check == 'migrate.class.php':
        return True
    if os.path.isfile(NEXTDOM_CLASS_PATH + file_to_check):
        if os.system('php check_class_methods.php ' +
                     file_to_check.replace('.class.php', '') + ' ' +
                     ' '.join(methods_list)) != 0:
            result = False
    else:
        print('Class ' + file_to_check.replace('.class.php', '') + ' not found.')
        result = False
    return result

def check_if_class_methods_exists(class_methods_to_check):
    """Check if methods exists
    :param class_methods_to_check: Methods to check
    :type class_methods_to_check:  dict
    """
    result = True
    for file_to_check in class_methods_to_check.keys():
        if not check_class_methods_file(file_to_check, class_methods_to_check[file_to_check]):
            result = False
    return result

def start_tests():
    """Test compatibility with jeedom
    :return: False if error found
    :rtype:  bool
    """
    print_subtitle('Test ajax')
    error = False
    jeedom_ajax_actions = get_ajax_actions()
    if not check_if_ajax_action_is_tested(jeedom_ajax_actions):
        error = True
    else:
        print_info('OK')

    print_subtitle('Test classes')
    jeedom_class_methods = get_class_methods()
    if not check_if_class_methods_exists(jeedom_class_methods):
        error = True
    else:
        print_info('OK')

    return error

def checkout_jeedom():
    """Checkout or update jeedom-core
    """
    checkout_cmd = "git clone https://github.com/jeedom/core /tmp/jeedom-core > /dev/null 2>&1"
    branch_cmd = "cd /tmp/jeedom-core && git checkout V4-stable -f > /dev/null 2>&1"
    update_cmd = "cd /tmp/jeedom-core && git fetch -apt > /dev/null 2>&1 && git pull -f origin master > /dev/null 2>&1" #pylint: disable=line-too-long
    if os.path.exists("/tmp/jeedom-core"):
        print_info("updating jeedom-core in /tmp/jeedom-core")
        if os.system(update_cmd) != 0:
            print_error("unable to update jeedom stable branch in /tmp/jeedom-core")
            sys.exit(1)
    else:
        print_info("fetching jeedom-core in /tmp/jeedom-core")
        if os.system(checkout_cmd) != 0:
            print_error("unable to clone jeedom in /tmp/jeedom-core")
            sys.exit(1)
        if os.system(branch_cmd) != 0:
            print_error("unable to switch to jeedom stable branch in /tmp/jeedom-core")
            sys.exit(1)

if __name__ == "__main__":
    print_title('Compatibility with Jeedom')
    print_subtitle('Cloning jeedom/core')
    checkout_jeedom()
    if start_tests():
        sys.exit(1)
