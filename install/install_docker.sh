#!/usr/bin/env bash
set -e

#################################################################################################
################################## NextDom Installation from docker #############################
#################################################################################################

## TODO
## To change default configuration, edit the ${CURRENT_DIR}/scripts/config.sh file and set what you need.
## TODO

CURRENT_DIR=$(dirname $0)

bash ${CURRENT_DIR}/scripts/preinst.sh
bash ${CURRENT_DIR}/scripts/postinst.sh


