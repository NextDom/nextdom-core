#!/usr/bin/env bash
#set -e

#################################################################################################
################################## NextDom Installation from docker #############################
#################################################################################################

# docker variables are given at run time not build time.

#https://stackoverflow.com/questions/59895/get-the-source-directory-of-a-bash-script-from-within-the-script-itself
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

#remove running parts
sed -i 's/preinstall_nextdom$//g' ${CURRENT_DIR}/scripts/preinst.sh
sed -i 's/exit 0$//g' ${CURRENT_DIR}/scripts/preinst.sh
#not known at build time
sed -i 's/PHP_DIRECTORY=/#PHP_DIRECTORY=/g' ${CURRENT_DIR}//scripts/config.sh

source ${CURRENT_DIR}/scripts/config.sh
source ${CURRENT_DIR}/scripts/utils.sh
source ${CURRENT_DIR}/preinst.sh

#build a script for docker build time
#at build time, unwanted steps are commented
#needed to generate assets
savePRODUCTION=${PRODUCTION}
PRODUCTION=false
step0_prepare_prerequisites
addLogScript "============ Starting preinst.sh ============"
step1_generate_nextdom_assets
#step2_configure_mysql
step3_prepare_var_www_html
step4_configure_apache
#step5_configure_mysql_database
#step6_generate_mysql_structure
#restore wanted status (prod or dev php ini config)
PRODUCTION=${savePRODUCTION}
step7_configure_php

addLogScript "============ End preinst.sh ============"

sed -i 's/postinstall_nextdom$//g' ${CURRENT_DIR}/postinst.sh
sed -i 's/exit 0$//g' ${CURRENT_DIR}/postinst.sh
source ${CURRENT_DIR}/postinst.sh

addLogScript "============ Starting postinst.sh ============"
#commented step are the one needed at runtime not build time
step1_create_prerequisite_files_and_directories
step2_prepare_directory_layout
step5_create_symLink_var_www_html
step6_configure_apache
step7_configure_crontab
step9_specific_action_for_OS
step10_configure_file_permissions
step11_change_owner_for_nextdom_directories
addLogScript "============ End postinst.sh ============"

#add supervisor
apt install -y supervisor
mkdir -p /var/log/supervisor/
cp ${CURRENT_DIR}/../supervisord.conf /etc/supervisor/conf.d/supervisord.conf