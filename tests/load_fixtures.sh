#!/bin/bash

DATABASE=nextdomdev

if [[ $# -eq 0 ]]; then
    echo "Usage $0"
    echo ""
    echo " --reset: Reset database with fixtures (must be called first)"
    echo " --firstuse: Reset data and show first use"
    echo " --nofirstuse: Stop to show first use"
fi

while [[ $# -gt 0 ]]
do
    args="$1"

    case $args in
        --firstuse)
        mysql -u root ${DATABASE} -e "UPDATE user SET PASSWORD = SHA2('admin', 512) WHERE login = 'admin'"
        mysql -u root ${DATABASE} -e "UPDATE config SET \`value\` = 1 WHERE \`key\` = 'nextdom::firstUse'"
        ;;
        --nofirstuse)
        mysql -u root ${DATABASE} -e "UPDATE user SET PASSWORD = SHA2('nextdom-test', 512) WHERE login = 'admin'"
        mysql -u root ${DATABASE} -e "UPDATE config SET \`value\` = 0 WHERE \`key\` = 'nextdom::firstUse'"
        ;;
        --reset)
        mysql -u root -e "DROP DATABASE ${DATABASE}"
        mysql -u root -e "CREATE DATABASE ${DATABASE}"
        mysql -u root ${DATABASE} < /usr/share/nextdom/install/install.sql
        mysql -u root ${DATABASE} < /usr/share/nextdom/tests/data/tests_fixtures.sql
        cp -fr /usr/share/nextdom/tests/data/plugin4tests /usr/share/nextdom/plugins/
        ;;
        --reset-admin-password)
        mysql -u root ${DATABASE} -e "UPDATE user SET PASSWORD = SHA2('nextdom-test', 512) WHERE login = 'admin'"
        ;;
        *)
        echo "$args : not recognized"
        ;;
    esac
    shift
done
