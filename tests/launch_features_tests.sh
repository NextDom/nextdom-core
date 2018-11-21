#!/bin/sh

function create_docker() {
  echo ">>> Create docker container for tests"
  ./scripts/remove_docker.sh
  ./scripts/prepare_docker.sh
}

cd "$( dirname "${BASH_SOURCE[0]}" )"

URL='http://127.0.0.1:8765'
LOGIN='admin'
PASSWORD='nextdom-test'

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

echo "**********************"
echo "*** FEATURES TESTS ***"
echo "**********************"
echo ">>> Scénarios <<<"
echo ">>>>> Setup"
./scripts/start_test_container.sh nextdom-test-scenarios
docker exec -i nextdom-test-scenarios /bin/ls
docker exec -i nextdom-test-scenarios /usr/bin/mysql -u root nextdomdev < data/smallest_scenario.sql
echo ">>>>> Start"
python3 -W ignore features/scenarios.py
echo ">>>>> Clear"
./scripts/remove_test_container.sh nextdom-test-scenarios
