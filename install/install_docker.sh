#!/usr/bin/env bash
#set -e

#################################################################################################
################################## NextDom Installation from docker #############################
#################################################################################################

# docker variables are given at run time not build time.

#https://stackoverflow.com/questions/59895/get-the-source-directory-of-a-bash-script-from-within-the-script-itself
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

#remove running part
sed -i 's/preinstall_nextdom$//g' ${CURRENT_DIR}/scripts/preinst.sh
sed -i 's/exit 0$//g' ${CURRENT_DIR}/scripts/preinst.sh
ls -al ${CURRENT_DIR}/scripts
source ${CURRENT_DIR}/scripts/config.sh
source ${CURRENT_DIR}/scripts/utils.sh
source ${CURRENT_DIR}/preinst.sh

addLogScript "============ Starting preinst.sh ============"
#build a script for docker build time
#at build time, unwanted steps are commented
step1_generate_nextdom_assets
#step2_configure_mysql
step3_prepare_var_www_html
step4_configure_apache
#step5_configure_mysql_database
#step6_generate_mysql_structure
step7_configure_php
addLogScript "============ End preinst.sh ============"

sed -i 's/postinstall_nextdom$//g' ${CURRENT_DIR}/postinst.sh
sed -i 's/exit 0$//g' ${CURRENT_DIR}/postinst.sh
source ${CURRENT_DIR}/postinst.sh docker

addLogScript "============ Starting preinst.sh ============"
step1_create_prerequisite_files_and_directories
step2_prepare_directory_layout
#step3_configure_mysql
step4_create_symLink_var_www_html
step5_configure_apache
step6_configure_nextdom
#step7_restart_mysql_database
step8_configure_crontab
#step9_check_nextdom
step10_specific_action_for_OS
step11_configure_file_permissions
step12_change_owner_for_nextdom_directories
addLogScript "============ End postinst.sh ============"