#!/usr/bin/env python3
"""Test plugins features
"""
import unittest
import subprocess
from time import sleep

class PluginsTest(unittest.TestCase):
    """Test plugins features
    """

    def test_plugin_cron_execution(self):
        """Test cron from plugin execution
        """
        # Wait 2 minute for cron execution
        sleep(120)
        shell_output = subprocess.check_output('docker exec -it nextdom-test-plugins ls /var/log/nextdom'.split(' ')) #pylint: disable=line-too-long
        self.assertIn('plugin4tests', shell_output.decode('utf-8'))
        shell_output = subprocess.check_output('docker exec -it nextdom-test-plugins cat /var/log/nextdom/plugin4tests'.split(' ')) #pylint: disable=line-too-long
        self.assertIn('CRON TEST', shell_output.decode('utf-8'))

# Entry point
if __name__ == "__main__":
    # failfast=True pour arrêter à la première erreur
    unittest.main()
