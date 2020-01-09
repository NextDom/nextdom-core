#!/usr/bin/env bash
set -e

CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

#################################################################################################
################################### NextDom Installation from git ###############################
#################################################################################################

sed -i "s|PRODUCTION=true|PRODUCTION=false|g" ${CURRENT_DIR}/scripts/config.sh

bash ${CURRENT_DIR}/scripts/preinst.sh
bash ${CURRENT_DIR}/scripts/postinst.sh


