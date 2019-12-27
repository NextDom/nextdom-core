#!/usr/bin/env bash
set -e
#################################################################################################
############################################ Global variables ###################################
#################################################################################################

# false for dev
PRODUCTION=true

# For log output
BLUE="\\033[1;34m"
GREEN="\\033[1;92m"
GREY="\\033[1;30m"
NORMAL="\\033[0;39m"
RED="\\033[1;31m"
CURRENT_DATE=$(date "+%D %r")

# root UID
ROOT_UID=0

# Apache path
APACHE_CONFIG_DIRECTORY="/etc/apache2/sites-available"
APACHE_HTML_DIRECTORY="/var/www/html"
APACHE_SYSTEMD_DIRECTORY="/etc/systemd/system/apache2.service.d"

### PHP path
PHP_DIRECTORY=$(php --ini | head -n 1 | sed -E "s/.*Path: (.*)\/cli/\\1/")


### NextDom path
CONFIG_DIRECTORY="/etc/nextdom"
LIB_DIRECTORY="/var/lib/nextdom"
LOG_DIRECTORY="/var/log/nextdom"
LOG_FILE="install.log"
ROOT_DIRECTORY="/usr/share/nextdom"
TMP_DIRECTORY="/tmp/nextdom"

### MySQL/MariaDB configuration

MYSQL_HOSTNAME="localhost"
MYSQL_PORT="3306"
#MYSQL_ROOT_PASSWD=""
MYSQL_NEXTDOM_DB="nextdomdev"
MYSQL_NEXTDOM_USER="nextdomdev"
MYSQL_NEXTDOM_PASSWD="$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 15)"

