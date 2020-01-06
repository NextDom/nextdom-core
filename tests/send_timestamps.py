#!/usr/bin/env python3
"""Analyze junit file
"""
import xml.dom.minidom


def read_test_suite(test_suite_list):
    """Read test suite node list
    :param test_suite_list: Test suite node list
    :type test_suite_list:  list
    """
    for test_suite in test_suite_list:
        test_case_list = test_suite.getElementsByTagName('testcase')
        for test_case in test_case_list:
            print('>> ' + test_case.getAttribute('class') + ' - ' +
                  test_case.getAttribute('name') + ' in ' +
                  test_case.getAttribute('time') + 's')


def read_junit_file(xml_file):
    """Read junit xml
    :param xml_file: Path of the file
    :type xml_file:  str
    """
    junit_content = xml.dom.minidom.parse(xml_file)

    tests_suites = junit_content.getElementsByTagName('testsuites')
    if tests_suites[0].nodeName is not None:
        test_suite = tests_suites[0].getElementsByTagName('testsuite')
        read_test_suite(test_suite)
    else:
        print('Bad file format')


if __name__ == '__main__':
    read_junit_file('coverage/junitlog.xml')
