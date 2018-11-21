#!/usr/bin/env python3

import unittest
import sys
import os
import subprocess
from time import sleep

class ScenariosTest(unittest.TestCase):
    """Test scenarios features
    """

    def test_scenario_cron_execution(self):
        # Wait 1 minute for cron execution
        sleep(60)
        shell_output = subprocess.Popen(["cat", "/var/log/nextdom/scenarioLog/scenario1.log"])
        print(shell_output)

# Entry point
if __name__ == "__main__":
    # failfast=True pour arrêter à la première erreur
    unittest.main()
