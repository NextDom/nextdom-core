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
      ./scripts/remove_test_container.sh nextdom-test-first-use
      ./scripts/remove_test_container.sh nextdom-test-others
      ./scripts/remove_test_container.sh nextdom-test-migration
    fi
}

function first_use() {
    echo ">>> First use page <<<"
    # Start specific container for first use
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-firstuse
    echo ">>>>> Start"
    python3 -W ignore gui/first_use_page.py
    echo ">>>>> Clear"
    ./scripts/remove_test_container.sh nextdom-test-firstuse
}

function migration() {
    echo ">>> Migration <<<"
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-migration $PASSWORD
    docker cp data/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz nextdom-test-migration:/var/www/html/backup/
    docker exec -it nextdom-test-migration php /var/www/html/install/migrate_jeedom_to_nextdom.php "backup=/var/www/html/backup/backup-Jeedom-3.2.11-2018-11-17-23h26.tar.gz" > /dev/null 2>&1
    docker exec -i nextdom-test-migration /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2('$PASSWORD', 512)"
    echo ">>>>> Start"
    python3 -W ignore gui/migration_page.py "$URL" "$LOGIN" "$PASSWORD"
    echo ">>>>> Clear"
    ./scripts/remove_test_container.sh nextdom-test-migration
}

function custom_js_css() {
    echo ">>> Custom JS/CSS <<<"
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-custom-js-css $PASSWORD
    python3 -W ignore gui/custom_js_css.py "$URL" "$LOGIN" "$PASSWORD"
    ./scripts/remove_test_container.sh nextdom-test-custom-js-css
}

function others() {
    # Start container for all others tests
    echo ">>> Others GUI tests <<<"
    echo ">>>>> Setup"
    ./scripts/start_test_container.sh nextdom-test-others $PASSWORD
    echo ">>> Connect page <<<"
    python3 -W ignore gui/connection_page.py "$URL" "$LOGIN" "$PASSWORD"
    echo ">>> Administration pages <<<"
    python3 -W ignore gui/administrations_page.py "$URL" "$LOGIN" "$PASSWORD"
    echo ">>> Rescue mode <<<"
    python3 -W ignore gui/rescue_page.py "$URL" "$LOGIN" "$PASSWORD"
    echo ">>>>> Clear"
    ./scripts/remove_test_container.sh nextdom-test-others
}

function start_all_tests() {
    init_docker
    echo "*****************"
    echo "*** GUI TESTS ***"
    echo "*****************"
    first_use
    migration
    custom_js_css
    others
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


