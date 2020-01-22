#!/usr/bin/env bash
set -e

#################################################################################################
################################### NextDom Installation from git ###############################
#################################################################################################

CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
cd ${CURRENT_DIR}/../
export ROOT_DIRECTORY=${PWD}
bash ${CURRENT_DIR}/scripts/preinst.sh
bash ${CURRENT_DIR}/scripts/postinst.sh



