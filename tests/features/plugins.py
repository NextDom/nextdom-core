#!/usr/bin/env python3

import unittest
import sys
import os
import subprocess
from time import sleep

class PluginsTest(unittest.TestCase):
    """Test scenarios features
    """

    def test_plugin_cron_execution(self):
        # Wait 2 minute for cron execution
        sleep(120)
        shell_output = subprocess.check_output(["docker", "exec", "-it", "nextdom-test-plugins", "ls", "/var/log/nextdom"])
        self.assertIn('plugin4tests', shell_output.decode('utf-8'))
        shell_output = subprocess.check_output(["docker", "exec", "-it", "nextdom-test-plugins", "cat", "/var/log/nextdom/plugin4tests"])
        self.assertIn('CRON TEST', shell_output.decode('utf-8'))

# Entry point
if __name__ == "__main__":
    # failfast=True pour arrêter à la première erreur
    unittest.main()
