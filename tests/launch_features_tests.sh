#!/bin/bash

function create_docker() {
  echo ">>> Create docker container for tests"
  ./scripts/remove_docker.sh
  ./scripts/prepare_docker.sh
}

function init_docker() {
    # Test if base container exists
    if [[ "$(docker images -q nextdom-test-snap:latest 2> /dev/null)" == "" ]]; then
      create_docker
    else
      read -p "Reset base test container (y/N)?" choice
      if [ "$choice" = "y" -o "$choice" = "Y" ]; then
        create_docker
      fi
      # Suppress old container
      echo "*** Clear env ***"
      ./scripts/remove_test_container.sh nextdom-test-scenarios
    fi
}

function scenarios() {
    echo ">>> Scénarios <<<"
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-scenarios
    docker exec -i nextdom-test-scenarios /usr/bin/mysql -u root nextdomdev < data/smallest_scenario.sql
    echo ">>>>> Start"
    python3 -W ignore features/scenarios.py
    echo ">>>>> Clear"
    ./scripts/remove_test_container.sh nextdom-test-scenarios
}

function plugins() {
    echo ">>> Plugins <<<"
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-plugins
    docker exec -i nextdom-test-plugins /bin/cp -fr /var/www/html/tests/data/plugin4tests /var/www/html/plugins
    docker exec -i nextdom-test-plugins /usr/bin/mysql -u root nextdomdev < data/plugin_test.sql
    echo ">>>>> Start"
    python3 -W ignore features/plugins.py
    echo ">>>>> Clear"
    ./scripts/remove_test_container.sh nextdom-test-plugins
}

function start_all_tests() {
    init_docker
    echo "**********************"
    echo "*** FEATURES TESTS ***"
    echo "**********************"
    scenarios
    plugins
}

cd "$( dirname "${BASH_SOURCE[0]}" )"

URL='http://127.0.0.1:8765'
LOGIN='admin'
PASSWORD='nextdom-test'

if [[ $# -eq 0 ]]; then
    start_all_tests
else
    declare -f -F $1 > /dev/null
    if [[ $? -eq 0 ]]; then
        init_docker
        $1
    else
        echo "Tests $1 doesn't exists"
    fi
fi


