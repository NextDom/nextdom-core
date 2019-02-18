#!/bin/bash

./launch_features_tests.sh
./launch_gui_tests.sh
php test_yaml.php fr_FR "An impossible sentence" "An impossible sentence"
php test_yaml.php fr_FR "connection.password-placeholder" "Mot de passe..."
php test_yaml.php en_US "core.error-401" "401 - Access not allowed"
php test_yaml.php fr_FR "core.error-401" "401 - Accès non autorisé"
../vendor/bin/phpunit phpunit_tests
