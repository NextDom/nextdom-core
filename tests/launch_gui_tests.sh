#!/bin/sh

function create_docker() {
  echo "Create docker container for tests"
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
  docker stop nextdom-test-firstuse > /dev/null 2>&1 || true && docker rm nextdom-test-firstuse > /dev/null 2>&1
  docker stop nextdom-test-others > /dev/null 2>&1 || true && docker rm nextdom-test-others > /dev/null 2>&1
  docker stop nextdom-test-migration > /dev/null 2>&1 || true && docker rm nextdom-test-migration > /dev/null 2>&1
fi

echo "*** GUI Tests ***"
echo ">>> First use page <<<"
# Start specific container for first use
docker run -d -p 8765:80 --name=nextdom-test-firstuse nextdom-test-snap:latest /launch.sh > /dev/null 2>&1
sleep 8
python3 -W ignore gui/first_use_page.py
docker kill nextdom-test-firstuse > /dev/null 2>&1
docker rm nextdom-test-firstuse > /dev/null 2>&1

echo ">>> Migration <<<"
docker run -d -p 8765:80 --name=nextdom-test-migration nextdom-test-snap:latest /launch.sh > /dev/null 2>&1
sleep 8
# Remove first use, welcome message and set admin password user
./scripts/sed_in_docker.sh "nextdom::firstUse = 1" "nextdom::firstUse = 0" /var/www/html/core/config/default.config.ini nextdom-test-migration
./scripts/sed_in_docker.sh "nextdom::Welcome = 1" "nextdom::Welcome = 0" /var/www/html/core/config/default.config.ini nextdom-test-migration
docker cp data/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz nextdom-test-migration:/var/www/html/backup/
docker exec -it nextdom-test-migration php /var/www/html/install/migrate_jeedom_to_nextdom.php "backup=/var/www/html/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz" > /dev/null 2>&1
docker exec -i nextdom-test-migration /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2('$PASSWORD', 512)"
python3 -W ignore gui/migration_page.py "$URL" "$LOGIN" "$PASSWORD"
docker kill nextdom-test-migration
docker rm nextdom-test-migration

# Start container for all others tests
docker run -d -p 8765:80 --name=nextdom-test-others nextdom-test-snap:latest /launch.sh > /dev/null 2>&1
sleep 8
# Remove first use, welcome message and set admin password user
./scripts/sed_in_docker.sh "nextdom::firstUse = 1" "nextdom::firstUse = 0" /var/www/html/core/config/default.config.ini nextdom-test-others
./scripts/sed_in_docker.sh "nextdom::Welcome = 1" "nextdom::Welcome = 0" /var/www/html/core/config/default.config.ini nextdom-test-others
docker exec -i nextdom-test-others /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2('$PASSWORD', 512)"
echo ">>> Connect page <<<"
python3 -W ignore gui/connection_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Administration pages <<<"
python3 -W ignore gui/administrations_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Rescue mode <<<"
python3 -W ignore gui/rescue_page.py "$URL" "$LOGIN" "$PASSWORD"
docker kill nextdom-test-others > /dev/null 2>&1
docker rm nextdom-test-others > /dev/null 2>&1
#./scripts/remove_docker.sh
