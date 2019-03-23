"""Functions used by tests
"""
import subprocess
import os
import sys

RED = '\033[0;31m'
GREEN = '\033[0;32m'
YELLOW = '\033[0;33m'
BLUE = '\033[0;34m'
NC = '\033[0m'

def print_title(title):
    """Print title with decoration
    :param title: Title to show
    """
    nb_stars = len(title) + 8
    print(BLUE + ('*' * nb_stars) + NC)
    print(BLUE + '*** ' + RED + title + ' ' + BLUE + '***' + NC)
    print(BLUE + ('*' * nb_stars) + NC)

def print_subtitle(subtitle):
    """Print subtitle with decoration
    :param subtitle: Subtitle to show
    """
    print('>>> ' + RED + subtitle + NC)

def print_info(information_msg):
    """Print information message with decoration
    :param information_msg: Information to show
    """
    print('>>>>> ' + GREEN + information_msg + NC)

def print_warning(warning_msg):
    """Print warning message with decoration
    :param warning_msg: Warning to show
    """
    print(YELLOW + '/!\\ ' + warning_msg + NC)

def print_error(error_msg):
    """Print error message with decoration
    :param error_msg: Error to show
    """
    print(RED + '!!! ' + error_msg + NC)

def ask_y_n(question, default='y'):
    """Ask for a question which answer is yes or no
    :param question: Question to show
    :param default:  Default answer
    :type question:  str
    :type default:   str
    :return:         Answer
    :rtype:          str
    """
    choices = 'Y/n'
    if default != 'y':
        choices = 'y/N'
    choice = input('%s [%s] : ' % (question, choices)).lower()
    if choice in (default, ''):
        return default
    return choice

def get_command_output(command):
    """Execute command and get the ouput
    :param command: Command to execute
    :type command:  str
    :return:        (command output, return value)
    :rtype:         tuble
    """
    cmd_process = subprocess.Popen(command,
                                   stdout=subprocess.PIPE,
                                   stderr=subprocess.STDOUT,
                                   shell=True)
    std_out, _ = cmd_process.communicate()
    status = cmd_process.wait()
    return std_out.decode('utf-8'), status

def is_docker_image_initialized():
    """Test if docker image is initialized
    :return: True if the docker image nextdom-test-snap exists
    :rtype:  bool
    """
    output, status = get_command_output('docker images -q nextdom-test-snap:latest')
    if status == 0 and output == '':
        return False
    return True

def create_docker():
    """Create docker image for tests
    """
    print_info('Create docker image for tests')
    os.system('./scripts/remove_docker.sh')
    os.system('./scripts/prepare_docker.sh')

def clear_docker():
    """Remove and kill all containers used for tests
    """
    containers_to_remove, _ = get_command_output('docker ps -a --filter "name=nextdom-test" -q')
    containers_list = containers_to_remove.split('\n')
    if len(containers_list) > 1:
        print_info('Clear docker')
        for container_id in containers_list:
            if container_id != '':
                os.system('docker kill ' + container_id + ' > /dev/null 2>&1')
                os.system('docker rm ' + container_id + ' > /dev/null 2>&1')

def init_docker():
    """Create docker image or ask for reset it. Avoid in travis environment.
    """
    if 'travis' not in os.uname().nodename:
        if not is_docker_image_initialized():
            create_docker()
        else:
            reset_answer = ask_y_n('Reset base test container', 'n')
            if reset_answer == 'y':
                create_docker()
        clear_docker()

def start_test_container(container_name, default_password=''):
    """Start a test container
    :param container_name:   Name of the container
    :param default_password: Default password of admin user
    :type container_name:    str
    :type default_password:  str
    """
    print_info('Setup')
    if default_password == '':
        os.system('./scripts/start_test_container.sh nextdom-test-' + container_name)
    else:
        os.system('./scripts/start_test_container.sh nextdom-test-' + container_name + ' ' + default_password) #pylint: disable=line-too-long

def remove_test_container(container_name):
    """Remove a test container
    :param container_name: Name of the container
    :type container_name:  str
    """
    print_info('Clear')
    os.system('./scripts/remove_test_container.sh nextdom-test-' + container_name)

def exec_command_in_container(container_name, command):
    """Execute a command a test container
    :param container_name: Name of the container
    :param command:        Command to execute
    :type container_name:  str
    :type command:         str
    """
    os.system('docker exec -i nextdom-test-' + container_name + ' ' + command)

def copy_file_in_container(container_name, src, dest):
    """Copy a file in a test container
    :param container_name: Name of the container
    :param src:            Source file
    :param dest:           Destination directory
    :type container_name:  str
    :type src:             str
    :type dest:            str
    """
    os.system('docker cp ' + src + ' nextdom-test-' + container_name + ':' + dest)

def start_all_tests(title, tests_list, use_docker=True):
    """Start all tests
    :param title:      Title of the type of tests
    :param tests_list: List of all tests
    :param use_docker: True if docker is used during tests
    :type title:       str
    :type tests_list:  dict
    :type use_docker:  bool
    :return:           True if all tests pass
    :rtype:            bool
    """
    if use_docker:
        clear_docker()
    print_title(title)
    all_tests_pass = True
    for test in tests_list:
        if tests_list[test]():
            all_tests_pass = False
    return all_tests_pass

def start_specific_test(test_name, tests_list):
    """Start specific test choosed by the user
    :param test_name:  Name of the test
    :param tests_list: List of all tests
    :type test_name:   str
    :type tests_list:  dict
    """
    if test_name in tests_list:
        tests_list[test_name]()
    else:
        print_error('Tests ' + test_name + ' not found')

def run_test(path, parameters=None):
    """Run a test file. Stop script with error on fail.
    :param path:       Path of the test file
    :param parameters: Parameters for the tests
    :type path:        str
    :type parameters:  list
    """
    print_info('Run tests')
    test_cmd = 'python3 -W ignore ' + path
    if parameters is not None:
        test_cmd = test_cmd + ' ' + ' '.join(parameters)
    std_out, status = get_command_output(test_cmd)
    print(std_out)
    if status != 0:
        sys.exit(1)
