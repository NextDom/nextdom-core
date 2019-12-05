#!/usr/bin/env python3
# line 100 caracters max or #pylint: disable=line-too-long
####################################################################################################
"""Launch NextDom features tests
"""
import sys
from time import sleep
from tests.libs.tests_funcs import *

NEXTDOM_URL = 'http://127.0.0.1:8765'
NEXTDOM_LOGIN = 'admin'
NEXTDOM_PASSWORD = 'nextdom-test'


def migration_tests():
    """Starts gui tests related to the migration page
    """
    container_name = 'migration'
    print_subtitle('Migration')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    # Copy minimal Jeedom backup in the container
    copy_file_in_container(container_name, 'data/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz',
                           '/var/lib/nextdom/backup/')  # pylint: disable=line-too-long
    # Execute the migration
    exec_command_in_container(
        container_name, 'sudo -u www-data php /var/www/html/install/restore.php file=/var/lib/nextdom/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz > /dev/null 2>&1')  # pylint: disable=line-too-long
    # Reset admin password
    exec_command_in_container(
        container_name, '/usr/bin/mysql -u root nextdomdev -e "UPDATE user SET password = SHA2(\'nextdom-test\', 512)"')  # pylint: disable=line-too-long
    test_result = run_test('tests/feature_migration.py',
                           [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    remove_test_container(container_name)
    return test_result


def migration_with_last_backup_file_tests():
    """Starts gui tests related to the migration page
    """
    container_name = 'migration_last'
    print_subtitle('Migration with last backup')
    start_test_container(container_name, NEXTDOM_PASSWORD)
    # Create fake backup file
    exec_command_in_container(
        container_name, 'sudo touch /var/lib/nextdom/backup/backup-Jeedom-3.2.11-2018-11-17-22h26.tar.gz')  # pylint: disable=line-too-long
    sleep(2)
    # Copy minimal Jeedom backup in the container
    copy_file_in_container(container_name, 'data/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz',
                           '/var/lib/nextdom/backup/')  # pylint: disable=line-too-long
    exec_command_in_container(
        container_name, 'sudo touch /var/lib/nextdom/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz')  # pylint: disable=line-too-long
    # Execute the migration
    exec_command_in_container(
        container_name, 'sudo -u www-data php /var/www/html/install/restore.php > /dev/null 2>&1')  # pylint: disable=line-too-long
    # Reset admin password
    exec_command_in_container(
        container_name, '/usr/bin/mysql -u root nextdomdev -e "UPDATE user SET password = SHA2(\'nextdom-test\', 512)"')  # pylint: disable=line-too-long
    test_result = run_test('tests/feature_migration.py',
                           [NEXTDOM_URL, NEXTDOM_LOGIN, NEXTDOM_PASSWORD])
    remove_test_container(container_name)
    return test_result


def scenarios_tests():
    """Starts tests related to the scenarios
    """
    container_name = 'scenarios'
    print_subtitle('Scenarios')
    start_test_container(container_name)
    exec_command_in_container(
        container_name,
        '/usr/bin/mysql -u root nextdomdev < data/smallest_scenario.sql')
    test_result = run_test('tests/feature_scenarios.py')
    remove_test_container(container_name)
    print(test_result)
    return test_result


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
    test_result = run_test('tests/feature_plugins.py')
    remove_test_container(container_name)
    print(test_result)
    return test_result


if __name__ == "__main__":
    TESTS_LIST = {
        'migration': migration_tests,
        'migration_last': migration_with_last_backup_file_tests,
        'scenarios': scenarios_tests,
        'plugins': plugins_tests
    }
    init_docker()
    RESULT = False
    if len(sys.argv) == 1:
        RESULT = start_all_tests('Features', TESTS_LIST)
    else:
        RESULT = start_specific_test(sys.argv[1], TESTS_LIST)
    if not RESULT:
        sys.exit(1)
