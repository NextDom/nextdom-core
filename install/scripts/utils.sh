#!/usr/bin/env bash
set -e

#https://stackoverflow.com/questions/59895/get-the-source-directory-of-a-bash-script-from-within-the-script-itself
CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

source ${CURRENT_DIR}/config.sh

#################################################################################################
############################################ Usual functions ####################################
#################################################################################################

####################################### Directory/File management ###############################

checkIfDirectoryExists() {
  local directoryToCheck=$1
  if [[ -e ${directoryToCheck} ]]; then
    return 1
  else
    return 0
  fi
}

createFile() {
  local fileToCreate=$1
  { ##try
    if [[ ! -f ${fileToCreate} ]]; then
      touch ${fileToCreate}
      addLogInfo "File : ${fileToCreate} created"
    fi
  } || { ##catch
    addLogError "Error while creating : ${fileToCreate}"
  }
}

createDirectory() {
  local directoryToCreate=$1
  { ##try
    if [[ ! -e ${directoryToCreate} ]]; then
      mkdir -p ${directoryToCreate}
      addLogInfo "Directory : ${directoryToCreate} created"
    fi
  } || { ##catch
    addLogError "Error while creating : ${directoryToCreate}"
  }
}

goToDirectory() {
  local directory=$1
  { ##try
    checkIfDirectoryExists ${directory}
    cd ${directory} >/dev/null
    pushd ${directory} >/dev/null
  } || { ##catch
    addLogError "Error while going : ${directory}"
  }
}

removeDirectoryOrFile() {
  local directoryToRemove=$1
  { ##try
    if [[ -d "${directoryToRemove}" ]]; then
      rm -Rf "${directoryToRemove}"
    fi
    addLogInfo "Directory or file : ${directoryToRemove} removed"
  } || { ##catch
    addLogError "Error while removing : ${directoryToRemove}"
  }
}
############################################ Log management #####################################

addLogError() {
  local error=$1
  echo -e "${RED}${CURRENT_DATE} ERROR : ${error}${NORMAL}" | tee -a ${LOG_DIRECTORY}/${LOG_FILE}
  result=false
}

addLogInfo() {
  local info=$1
  echo -e "${CURRENT_DATE} INFO : ${info}" | tee -a ${LOG_DIRECTORY}/${LOG_FILE}
}

addLogSuccess() {
  local info=$1
  echo -e "${GREEN}${CURRENT_DATE} SUCCESS : ${info}${NORMAL}" | tee -a ${LOG_DIRECTORY}/${LOG_FILE}
}

addLogStep() {
  local info=$1
  echo -e "${BLUE}${CURRENT_DATE} STEP : ${info}${NORMAL}" | tee -a ${LOG_DIRECTORY}/${LOG_FILE}
}

addLogScript() {
  local info=$1
  echo -e "${GREY}${CURRENT_DATE} SCRIPT : ${info}${NORMAL}" | tee -a ${LOG_DIRECTORY}/${LOG_FILE}
}
########################################### Rights management ###################################

checkCurrentUser() {
  if [[ "${UID}" -ne "${ROOT_UID}" ]]; then
    addLogError "You are not authorized to run this script … Permission Denied !!!"
    //exit ${ERROR_INVALID_USER}
  fi
}

########################################## Services management ##################################

# slow start of docker service
checkMySQLIsRunning() {
  local MYSQL_OPTIONS=$1
  local try=0

  while [[ ${try} -lt 5 ]]; do
    { ##try
      mysql -uroot ${MYSQL_OPTIONS} -e "SHOW DATABASES;" >/dev/null 2>&1
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't connect to database"
  //exit ${ERROR_INVALID_DATABASE_CONNECTION}
}

# slow restart of docker service
restartService() {
  # not needed in docker
  [[ -f /.dockerenv ]] && return 0
  local SERVICE=$1
  local try=0
  while [[ ${try} -lt 5 ]]; do
    { ##try
      service ${SERVICE} restart
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't restart ${SERVICE}"
}

# slow reload of docker service
reloadService() {
  # not needed in docker
  [[ -f /.dockerenv ]] && return 0
  local SERVICE=$1
  local try=0
  while [[ ${try} -lt 5 ]]; do
    { ##try
      service ${SERVICE} reload
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't reload ${SERVICE}"
}

# slow start of docker service
startService() {
  # not needed in docker
  [[ -f /.dockerenv ]] && return 0
  local SERVICE=$1
  local try=0
  while [[ ${try} -lt 5 ]]; do
    { ##try
      service ${SERVICE} start
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't start ${SERVICE}"
}

# slow stop of docker service
stopService() {
  # not needed in docker
  [[ -f /.dockerenv ]] && return 0
  local SERVICE=$1
  local try=0
  while [[ ${try} -lt 5 ]]; do
    { ##try
      service ${SERVICE} stop
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't stop ${SERVICE}"
}

# slow status of docker service
statusService() {
  # not needed in docker
  [[ -f /.dockerenv ]] && return 0
  local SERVICE=$1
  local try=0
  while [[ ${try} -lt 5 ]]; do
    { ##try
      service ${SERVICE} status
    } || { ##catch
      try=$((try + 1))
      sleep 5
      continue
    }
    return 0
  done
  addLogError "Can't stop ${SERVICE}"
}