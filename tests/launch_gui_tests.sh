#!/bin/bash

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
  ./scripts/remove_test_container.sh nextdom-test-first-use
  ./scripts/remove_test_container.sh nextdom-test-others
  ./scripts/remove_test_container.sh nextdom-test-migration
fi

echo "*****************"
echo "*** GUI TESTS ***"
echo "*****************"
echo ">>> First use page <<<"
# Start specific container for first use
echo ">>>>> Setup"
./scripts/start_test_container.sh nextdom-test-firstuse
echo ">>>>> Start"
python3 -W ignore gui/first_use_page.py
echo ">>>>> Clear"
./scripts/remove_test_container.sh nextdom-test-firstuse

echo ">>> Migration <<<"
echo ">>>>> Setup"
./scripts/start_test_container.sh nextdom-test-migration $PASSWORD
# Remove first use, welcome message and set admin password user
docker cp data/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz nextdom-test-migration:/var/www/html/backup/
docker exec -it nextdom-test-migration php /var/www/html/install/migrate_jeedom_to_nextdom.php "backup=/var/www/html/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz" > /dev/null 2>&1
docker exec -i nextdom-test-migration /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2('$PASSWORD', 512)"
echo ">>>>> Start"
python3 -W ignore gui/migration_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>>>> Clear"
./scripts/remove_test_container.sh nextdom-test-migration

# Start container for all others tests
echo ">>> Others GUI tests <<<"
echo ">>>>> Setup"
./scripts/start_test_container.sh nextdom-test-others $PASSWORD
# Remove first use, welcome message and set admin password user
echo ">>> Connect page <<<"
python3 -W ignore gui/connection_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Administration pages <<<"
python3 -W ignore gui/administrations_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Rescue mode <<<"
python3 -W ignore gui/rescue_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>>>> Clear"
./scripts/remove_test_container.sh nextdom-test-others
