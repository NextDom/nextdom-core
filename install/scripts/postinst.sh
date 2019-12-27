#!/usr/bin/env bash
set -e

CURRENT_DIR=$(dirname $0)

source ${CURRENT_DIR}/utils.sh


#################################################################################################
########################################### NextDom Steps #######################################
#################################################################################################

step1_create_prerequisite_files_and_directories() {
    result=true
    addLogStep "Postinst -- Create needed files and directories - 1/12"

    local directories=("${ROOT_DIRECTORY}/plugins" "${LIB_DIRECTORY}" "${LIB_DIRECTORY}/market_cache"
    "${LIB_DIRECTORY}/cache" "${LIB_DIRECTORY}/backup" "${LIB_DIRECTORY}/custom/desktop" "${LIB_DIRECTORY}/public/css"
    "${LIB_DIRECTORY}/public/img/plan" "${LIB_DIRECTORY}/public/img/profils" "${LIB_DIRECTORY}/public/img/market_cache"
    "${LOG_DIRECTORY}/scenarioLog")

    for c_dir in ${directories[*]}; do
        createDirectory ${c_dir}
    done

    removeDirectoryOrFile "${LOG_DIRECTORY}/${LOG_FILE}"

    local files=("${LOG_DIRECTORY}/${LOG_FILE}" "${LOG_DIRECTORY}/cron" "${LOG_DIRECTORY}/cron_execution"
    "${LOG_DIRECTORY}/event" "${LOG_DIRECTORY}/http.error" "${LOG_DIRECTORY}/plugin" "${LOG_DIRECTORY}/scenario_execution")

    for c_file in ${files[*]}; do
        createFile ${c_file}
    done

    if [[ ${result} ]] ; then
        addLogSuccess "Files and directories are created with success"
    fi
}

step2_prepare_directory_layout() {
  result=true

  addLogStep "Postinst -- Prepare directory layout - 2/12"

  # we delete existing config since it is regenerated from asset sample (step_nextdom_configuration)
  removeDirectoryOrFile ${LIB_DIRECTORY}/config
  cp -r  ${ROOT_DIRECTORY}/assets/config ${LIB_DIRECTORY}
  addLogInfo "created configuration directory ${LIB_DIRECTORY}/config"

  # we delete existing data, since its re-imported from assets
  removeDirectoryOrFile ${LIB_DIRECTORY}/data
  cp -r  ${ROOT_DIRECTORY}/assets/data   ${LIB_DIRECTORY}
  addLogInfo "created data directory ${LIB_DIRECTORY}/data"

  # jeedom backup compatibility: ./core/config is a symlink
  if [[ -L ${ROOT_DIRECTORY}/core/config ]]; then
      removeDirectoryOrFile ${ROOT_DIRECTORY}/core/config
  fi
  ln -s ${LIB_DIRECTORY}/config ${ROOT_DIRECTORY}/core/config
  addLogInfo "created core configuration symlink: ${ROOT_DIRECTORY}/core/config"

  # jeedom backup compatibility:  ./var is a symlink
  if [[ -L ${ROOT_DIRECTORY}/var ]]; then
      removeDirectoryOrFile ${ROOT_DIRECTORY}/var
  fi
  if [[ -d ${ROOT_DIRECTORY}/var ]]; then
      content=$(ls -A ${ROOT_DIRECTORY}/var)
      if [[ ! -z "${content}" ]]; then
          tmpvar=$(mktemp -d ${ROOT_DIRECTORY}/var.XXXXXXXX)
          mv ${ROOT_DIRECTORY}/var/* ${tmpvar}/
      fi
      removeDirectoryOrFile ${ROOT_DIRECTORY}/var
  fi
  ln -s ${LIB_DIRECTORY} ${ROOT_DIRECTORY}/var
  addLogInfo "created var symlink: ${ROOT_DIRECTORY}/var"

  # jeedom backup compatibility:  ./core/css is a symlink
  # -> some important plugins like widget are writing direclty to core/css/...
  #    and there fore need www-data write permission
  if [[ -L ${ROOT_DIRECTORY}/core/css ]]; then
      removeDirectoryOrFile ${ROOT_DIRECTORY}/core/css
  fi
  if [[ -d ${ROOT_DIRECTORY}/core/css ]]; then
      mv ${ROOT_DIRECTORY}/core/css/* ${LIB_DIRECTORY}/public/css/
      removeDirectoryOrFile ${ROOT_DIRECTORY}/core/css
  fi
  ln -s ${LIB_DIRECTORY}/public/css/ ${ROOT_DIRECTORY}/core/css
  addLogInfo "created core/css symlink: ${ROOT_DIRECTORY}/core/css"

  # jeedom javascript compatibility
  if [[ ! -e ${ROOT_DIRECTORY}/core/js ]]; then
      ln -s ${ROOT_DIRECTORY}/assets/js/core/ ${ROOT_DIRECTORY}/core/js
  fi
  addLogInfo "created core/js symlink: ${ROOT_DIRECTORY}/assets/js/core"

  # jeedom template location compatibility
  if [[ ! -e ${ROOT_DIRECTORY}/core/template ]]; then
      ln -s ${ROOT_DIRECTORY}/views/templates/ ${ROOT_DIRECTORY}/core/template
  fi
  addLogInfo "created core/template symlink: ${ROOT_DIRECTORY}/core/template"

  # jeedom backup compatibility:  ./data is a symlink
  if [[ -L ${ROOT_DIRECTORY}/data ]]; then
      removeDirectoryOrFile ${ROOT_DIRECTORY}/data
  fi
  if [[ -d ${ROOT_DIRECTORY}/data ]]; then
      content=$(ls -A ${ROOT_DIRECTORY}/data)
      if [[ ! -z "${content}" ]]; then
          tmpvar=$(mktemp -d ${ROOT_DIRECTORY}/data.XXXXXXXX)
          mv ${ROOT_DIRECTORY}/data/* ${tmpvar}/
      fi
      removeDirectoryOrFile ${ROOT_DIRECTORY}/data
  fi
  { ##try
      if [[ -f ${ROOT_DIRECTORY}/data ]]; then
          removeDirectoryOrFile ${ROOT_DIRECTORY}/data
      fi
      if [[ ! -e ${ROOT_DIRECTORY}/data ]]; then
          ln -s ${LIB_DIRECTORY}/data ${ROOT_DIRECTORY}/data
      fi
      addLogInfo "created data symlink: ${ROOT_DIRECTORY}/data"
  } || { ##catch
      addLogError "Error while linking ${ROOT_DIRECTORY}/data"
  }
  { ##try
      # jeedom logs compatibility
      if [[ ! -f ${ROOT_DIRECTORY}/log ]]; then
        removeDirectoryOrFile ${ROOT_DIRECTORY}/log
      fi
      if [[ ! -e ${ROOT_DIRECTORY}/log ]]; then
          ln -s ${LOG_DIRECTORY} ${ROOT_DIRECTORY}/log
      fi
  } || { ##catch
      addLogError "Error while linking ${LOG_DIRECTORY}"
  }
  { ##try
      #clear cache
      sh ${ROOT_DIRECTORY}/scripts/clear_cache.sh
      addLogInfo "cache cleared"
  } || { ##catch
      addLogError "Error while clearing cache"
  }
  if [[ ${result} ]] ; then
    addLogSuccess "NextDom is configured with success"
  fi
}

##TODO A virer après update du paquet debian
step3_configure_mysql() {
  # check that mysql is locally installed before any further configuration
  # default value for mysql_host is localhost
  result=true

  addLogStep "Postinst -- Configure MySQL/MariaDB - 3/12"

  [[ "localhost" != "${MYSQL_HOSTNAME}" ]] && {
    addLogInfo "Remote mysql server detected"
    return 0
  }

  { ##try
      mysqladmin -u root status
      isService=$?
      if [[ ${isService} -gt 0 ]]; then
        addLogInfo "no mysql service locally"
        return 0
      elif [[ ! -f /etc/init.d/mysql ]]; then
        addLogInfo "no mysql service locally"
        return 0
      fi
  } || { ##catch
    addLogError "Error while checking mysql status"
  }

  stopService mysql

  { ##try
    rm -f /var/lib/mysql/ib_logfile*
  } || { ##catch
    addLogError "Error while cleaning mysql data"
  }

  { ##try
      if [[ -d /etc/mysql/conf.d ]]; then
          cat - > /etc/mysql/conf.d/nextdom_my.cnf <<EOS
[mysqld]
skip-name-resolve
key_buffer_size = 16M
thread_cache_size = 16
tmp_table_size = 48M
max_heap_table_size = 48M
query_cache_type =1
query_cache_size = 32M
query_cache_limit = 2M
query_cache_min_res_unit=3K
innodb_flush_method = O_DIRECT
innodb_flush_log_at_trx_commit = 2
innodb_log_file_size = 32M
EOS
      fi
      addLogInfo "created nextdom mysql configuration: /etc/mysql/conf.d/nextdom_my.cnf"
  } || { ##catch
    addLogError "Error while creating /etc/mysql/conf.d/nextdom_my.cnf"
  }

  startService mysql

  if [[ ${result} ]] ; then
    addLogSuccess "MySQL is configured with success"
  fi
}

step4_create_symLink_var_www_html() {
  # Link ${ROOT_DIRECTORY} to /var/www/html. Required by old plugins that may hardcode this path.
  # Any previously installed content are moved to temporairy directories in check_var_www_html()
  # link /var/www/html to nextdom root
  result=true

  addLogStep "Postinst -- Configure symbolic links - 4/12"

  { ##try
        ln -s "${ROOT_DIRECTORY}" ${APACHE_HTML_DIRECTORY}
  } || { ##catch
        addLogError "Error while linking ${ROOT_DIRECTORY} to ${APACHE_HTML_DIRECTORY}"
  }

  if [[ ${result} ]] ; then
    addLogSuccess "${ROOT_DIRECTORY} linked with success to ${APACHE_HTML_DIRECTORY}"
  fi
}

step5_configure_apache() {
  result=true

  addLogStep "Postinst -- Configure Apache - 5/12"

  # check that APACHE_HTML_DIRECTORY is readable by www-data
  { ##try
    sudo -u www-data test -r "${APACHE_HTML_DIRECTORY}"
  } || {
    addLogError "${APACHE_HTML_DIRECTORY} is not readable by www-data user \n enabled compatibility mode, DocumentRoot targets /var/www/html"
  }

  { ##try
    a2enmod ssl              
    addLogInfo "apache: enable module ssl"
    a2enmod rewrite          
    addLogInfo "apache: enable module rewrite"
    a2dismod status          
    addLogInfo "apache: disable module status"
    a2dissite 000-default    
    addLogInfo "apache: disabled site default"
    a2dissite default-ssl    
    addLogInfo "apache: disabled site default-ssl"
    a2ensite nextdom-ssl     
    addLogInfo "apache: enabled site nextdom-ssl"
    a2ensite nextdom         
    addLogInfo "apache: enabled site nextdom"
    restartService apache2
  } || { ##catch
    addLogError "Error while configuring Apache service"
  }
  if [[ ${result} ]] ; then
    addLogSuccess "Apache is configured with success"
  fi
}

step6_configure_nextdom() {
  result=true

  addLogStep "Postinst -- Configure NextDom - 6/12"

  { ##try
    # recreate configuration from sample
    cp ${ROOT_DIRECTORY}/core/config/common.config.sample.php ${ROOT_DIRECTORY}/core/config/common.config.php
  } || { ##catch
    addLogError "Error while copying ${ROOT_DIRECTORY}/core/config/common.config.php"
  }
  { ##try
      SECRET_KEY=$(</dev/urandom tr -dc '1234567890azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN_@;=' | head -c30; echo "")
      # Add a special char
      SECRET_KEY=$SECRET_KEY$(</dev/urandom tr -dc '*&!@#' | head -c1; echo "")
      # Add numeric char
      SECRET_KEY=$SECRET_KEY$(</dev/urandom tr -dc '1234567890' | head -c1; echo "")
  } || { ##catch
    addLogError "Error while generating Secret key"
  }

  { ##try
    sed -i "s/#PASSWORD#/${MYSQL_NEXTDOM_PASSWD}/g" ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s/#DBNAME#/${MYSQL_NEXTDOM_DB}/g"       ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s/#USERNAME#/${MYSQL_NEXTDOM_USER}/g"   ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s/#PORT#/${MYSQL_PORT}/g"               ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s/#HOST#/${MYSQL_HOSTNAME}/g"           ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s%#LOG_DIR#%${LOG_DIRECTORY}%g"         ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s%#LIB_DIR#%${LIB_DIRECTORY}%g"         ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s%#TMP_DIR#%${TMP_DIRECTORY}%g"         ${ROOT_DIRECTORY}/core/config/common.config.php
    sed -i "s%#SECRET_KEY#%${SECRET_KEY}%g"         ${ROOT_DIRECTORY}/core/config/common.config.php
    addLogInfo "wrote configuration file: ${ROOT_DIRECTORY}/core/config/common.config.php"
  } || { ##catch
    addLogError "Error while writing in: ${ROOT_DIRECTORY}/core/config/common.config.php"
  }

  { ##try
      # some other compatibilty ugly stuff
      if [[ -d "/tmp/jeedom" ]]; then
          if [[ -L "/tmp/jeedom" ]]; then
              removeDirectoryOrFile /tmp/jeedom
              if [[ ! -d "${TMP_DIRECTORY}" ]]; then
                  mkdir -p ${TMP_DIRECTORY}
              fi
              ln -s ${TMP_DIRECTORY} /tmp/jeedom
          else
            if [[ -d "${TMP_DIRECTORY}" ]]; then
                mv /tmp/jeedom/* ${TMP_DIRECTORY}/
            else
              mv /tmp/jeedom ${TMP_DIRECTORY}
              ln -s ${TMP_DIRECTORY} /tmp/jeedom
            fi
          fi
      else
        if [[ ! -d "${TMP_DIRECTORY}" ]]; then
            mkdir -p ${TMP_DIRECTORY}
        fi
        removeDirectoryOrFile /tmp/jeedom
        ln -s ${TMP_DIRECTORY} /tmp/jeedom
      fi
      addLogInfo "created temporary directory: ${TMP_DIRECTORY}"
  } || { ##catch
    addLogError "Error while creating tmp folders/links"
  }

  { ##try
    # allow www-data to use usb/serial ports
    usermod -a -G dialout,tty www-data
  } || { ##catch
    addLogError "Error while setting rights for usb/serial ports to www-data user"
  }
  { ##try
    # set tmp directory as ramfs mount point if enough memory is available
      if [[ -f "/proc/meminfo" ]] ; then
          if [[ $(cat /proc/meminfo | grep MemTotal | awk '{ print $2 }') -gt 600000 ]]; then
              if [[ -f "/etc/fstab" ]] ; then
                  if [[ $(cat /etc/fstab | grep ${TMP_DIRECTORY} | grep tmpfs | wc -l) -eq 0 ]]; then
                      cat - >> /etc/fstab <<EOS
tmpfs ${TMP_DIRECTORY} tmpfs defaults,size=128M 0 0
EOS
                  fi
              fi
          fi
      fi
  } || { ##catch
    addLogError "Error while adding tmp directory as ramfs mount"
  }

  { ##try
      # add www-data in sudoers with no password
      if [[ $(grep "www-data ALL=(ALL) NOPASSWD: ALL" /etc/sudoers | wc -l ) -eq 0 ]]; then
          echo "www-data ALL=(ALL) NOPASSWD: ALL" | (EDITOR="tee -a" visudo) >/dev/null
          if [[ $? -ne 0 ]]; then
              print_error "unable to add www-data to sudoers"
          fi
      fi
      addLogInfo "added user as sudoer: www-data"
  } || { ##catch
    addLogError "Error while adding www-data as sudoer"
  }
  if [[ ${result} ]] ; then
    addLogSuccess "NextDom is configured with success"
  fi
}

step7_restart_mysql_database() {
    result=true

    addLogStep "Postinst -- Restart MySQL/MariaDB - 7/12"

    { ##try
        restartService mysql
    } || { ##catch
        addLogError "MySQL/MariaDB is not running"
    }

    if [[ ${result} ]] ; then
        addLogSuccess "MySQL/MariaDB is configured with success"
    fi
}

step8_configure_crontab() {
  result=true

  addLogStep "Postinst -- Configure Cron - 8/12"

  { ##try
    cat - > /etc/cron.d/nextdom << EOS
* * * * * www-data /usr/bin/php ${APACHE_HTML_DIRECTORY}/src/Api/start_cron.php >> /dev/null
EOS
    addLogInfo "created nextdom cron configuration: /etc/cron.d/nextdom"
  } || { ##catch
    addLogError "Error while creating /etc/cron.d/nextdom"
  }
  {
    cat - > /etc/cron.d/nextdom_watchdog << EOS
*/5 * * * * root /usr/bin/php ${APACHE_HTML_DIRECTORY}/scripts/watchdog.php >> /dev/null
EOS
    addLogInfo "created nextdom cron configuration: /etc/cron.d/nextdom_watchdog"
  } || { ##catch
    addLogError "Error while creating /etc/cron.d/nextdom_watchdog"
  }

  reloadService cron

  if [[ ${result} ]] ; then
    addLogSuccess "Cron is configured with success"
  fi
}

step9_check_nextdom() {
  result=true

  addLogStep "Postinst -- Check NextDom - 9/12"

  { ##try
    php ${APACHE_HTML_DIRECTORY}/scripts/sick.php
  } || { ##catch
    addLogError "Error while checking nextdom"
  }
  if [[ ${result} ]] ; then
    addLogSuccess "Check is done with success"
  fi
}

step10_specific_action_for_OS(){
  result=true

  addLogStep "Postinst -- Execute scripts for specific OS- 10/12"

  { ##try
      if [[ -f /etc/armbian.txt ]]; then
          cat ${ROOT_DIRECTORY}/install/OS_specific/armbian/post-install.sh | bash
      fi
  } || { ##catch
    addLogError "Error while specific action for armbian"
  }

  { ##try
      if [[ -f /usr/bin/raspi-config ]]; then
          cat ${ROOT_DIRECTORY}/install/OS_specific/rpi/post-install.sh | bash
      fi
  } || { ##catch
    addLogError "Error while specific action for raspberry pi"
  }
  if [[ ${result} ]] ; then
    addLogSuccess "OS specific actions are done with success"
  fi
}

step11_configure_file_permissions() {
    # configure file permissions
    # ${ROOT_DIRECTORY}/plugins and ${ROOT_DIRECTORY}/public/img should not be given
    # www-data ownership, still needed until proper migration handling
    result=true

    addLogStep "Postinst -- Configure file permission - 11/12"

    { ##try
      local directories=("${LIB_DIRECTORY}" "${LOG_DIRECTORY}" "${TMP_DIRECTORY}" "${ROOT_DIRECTORY}/plugins" "${ROOT_DIRECTORY}/public/img")
      for c_dir in ${directories[*]}; do
        if [[ checkIfDirectoryExists ${c_dir} ]]; then
            chown -R www-data:www-data ${c_dir}
            find ${c_dir} -type d -exec chmod 0755 {} \;
            find ${c_dir} -type f -exec chmod 0644 {} \;
            addLogInfo "set file owner: www-data, perms: 0755/0644 on directory ${c_dir}"
         fi
      done
    } || { ##catch
        addLogError "Error while checking file permission"
    }

    if [[ ${result} ]] ; then
        addLogSuccess "Files permissions are configured with success"
    fi
}

step12_change_owner_for_nextdom_directories() {
  result=true

  addLogStep "Postinst -- Configure owner for NextDom directory - 12/12"

  { ##try
    chown -R www-data:www-data "${ROOT_DIRECTORY}"
  } || { ##catch
    addLogError "Error while changing owner on ${ROOT_DIRECTORY}"
  }
  { ##try
    if [[ -d "${TMP_DIRECOTRY}" ]]; then
     chown -R www-data:www-data "${TMP_DIRECOTRY}"
    fi
  } || { ##catch
    addLogError "Error while changing owner on ${TMP_DIRECTORY}"
  }

  if [[ ${result} ]] ; then
    addLogSuccess "${ROOT_DIRECTORY} and ${TMP_DIRECTORY} owner is changed with success"
  fi
}



#################################################################################################
############################################# Installation ######################################
#################################################################################################

postinstall_nextdom() {

  if [[ $(id -u) != 0 ]] ; then
      addLogError "Les droits de super-utilisateur (root) sont requis pour installer NextDom
        Veuillez lancer sudo $0 ou connectez-vous en tant que root, puis relancez $0"
      exit 1
  fi

  addLogScript "============ Starting postinst.sh ============"

  # Start all services
  startService apache2
  startService cron
  startService mysql

  step1_create_prerequisite_files_and_directories
  step2_prepare_directory_layout
  step3_configure_mysql
  step4_create_symLink_var_www_html
  step5_configure_apache
  step6_configure_nextdom
  step7_restart_mysql_database
  step8_configure_crontab
  step9_check_nextdom
  step10_specific_action_for_OS
  step11_configure_file_permissions
  step12_change_owner_for_nextdom_directories

  [[ -f /root/.mysqlroot ]] && rm -f /root/.mysqlroot

      cat - <<EOS
  Installation dir  : ${ROOT_DIRECTORY}

  Database host     : ${MYSQL_HOSTNAME}
  Database port     : ${MYSQL_PORT}
  Database name     : ${MYSQL_NEXTDOM_DB}
  Database user     : ${MYSQL_NEXTDOM_USER}
  Database password : ${MYSQL_NEXTDOM_PASSWD}

  > A self-signed SSL Certificate created in /etc/nextdom/ssl/
  > Please feel free to use another Certificate

  >>>>> COMPLETED <<<<<
EOS

  addLogScript "============ Postinst.sh is executed ... ============"

}

postinstall_nextdom

exit 0;