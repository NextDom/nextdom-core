#!/usr/bin/env python3
"""Test scenario features
"""
import subprocess
import unittest
from time import sleep

class ScenariosTest(unittest.TestCase):
    """Test scenarios features
    """

    def test_scenario_cron_execution(self):
        """Test cron execution of a scenario
        """
        # Wait 2 minute for cron execution
        sleep(120)
        shell_output = subprocess.check_output('docker exec -it nextdom-test-scenarios ls /var/log/nextdom/scenarioLog'.split(' ')) #pylint: disable=line-too-long
        self.assertIn('scenario1.log', shell_output.decode('utf-8'))
        shell_output = subprocess.check_output('docker exec -it nextdom-test-scenarios cat /var/log/nextdom/scenarioLog/scenario1.log'.split(' ')) #pylint: disable=line-too-long
        self.assertIn('automatiquement sur programmation', shell_output.decode('utf-8'))

# Entry point
if __name__ == "__main__":
    # failfast=True pour arrêter à la première erreur
    unittest.main()
