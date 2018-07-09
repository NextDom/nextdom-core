#!/bin/bash
DEBUG=$1
VERT="\\033[1;32m"
NORMAL="\\033[0;39m"
ROUGE="\\033[1;31m"
ROSE="\\033[1;35m"
BLEU="\\033[1;34m"
BLANC="\\033[0;02m"
BLANCLAIR="\\033[1;08m"
JAUNE="\\033[1;33m"
CYAN="\\033[1;36m"
clear

if [ $(id -u) != 0 ] ; then
    echo "Les droits de super-utilisateur (root) sont requis pour installer NextDom"
    echo "Veuillez lancer 'sudo $0' ou connectez-vous en tant que root, puis relancez $0"
    exit 1
fi

if [ -z "$1" ] ; then
    DEBUG="/tmp/output.txt"
else
    DEBUG=""
fi

delay(){
    sleep 1
}

apt_install() {
    apt-get -y install "$@"
    if [ $? -ne 0 ]; then
        printf "${ROUGE}Ne peut installer $@ - Annulation${NORMAL}"
        exit 1
    fi
}

mysql_sql() {
    echo "$@" | mysql -uroot -p${MYSQL_ROOT_PASSWD}
    if [ $? -ne 0 ]; then
        printf "${ROUGE}Ne peut exécuter $@ dans MySQL - Annulation${NORMAL}"
        exit 1
    fi
}

step_1_upgrade() {
    apt-get -q update  > ${DEBUG} 2>&1
    apt-get -q -f install  >> ${DEBUG} 2>&1
    apt-get -q -y dist-upgrade >> ${DEBUG} 2>&1
}

step_2_mainpackage() {
    apt-get -q -y install ntp ca-certificates unzip curl sudo cron locate tar telnet wget logrotate fail2ban dos2unix ntpdate htop iotop vim iftop smbclient git python python-pip software-properties-common libexpat1 ssl-cert apt-transport-https xvfb cutycapt xauth >> ${DEBUG} 2>&1
    add-apt-repository non-free >> ${DEBUG} 2>&1
    apt-get -q update >> ${DEBUG} 2>&1
    apt-get -q -y install libav-tools libsox-fmt-mp3 sox libttspico-utils espeak mbrola >> ${DEBUG} 2>&1
    apt-get -q -y remove brltty >> ${DEBUG} 2>&1
}

step_3_database() {
    echo "mysql-server mysql-server/root_password password ${MYSQL_ROOT_PASSWD}" | debconf-set-selections
    echo "mysql-server mysql-server/root_password_again password ${MYSQL_ROOT_PASSWD}" | debconf-set-selections
    apt-get install -q -y mysql-client mysql-common mysql-server >> ${DEBUG} 2>&1

    systemctl status mysql > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        service mysql status  2>&1
        if [ $? -ne 0 ]; then
            systemctl start mysql > /dev/null 2>&1
            if [ $? -ne 0 ]; then
                service mysql start > /dev/null 2>&1
            fi
        fi
    fi
    systemctl status mysql > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        service mysql status 2>&1
        if [ $? -ne 0 ]; then
            echo "${ROUGE}Ne peut lancer mysql - Annulation${NORMAL}"
            exit 1
        fi
    fi
    mysqladmin -u root password ${MYSQL_ROOT_PASSWD}
}

step_4_apache() {
    apt_install apache2 apache2-utils libexpat1 ssl-cert >> ${DEBUG} 2>&1
    a2enmod rewrite >> ${DEBUG} 2>&1
}

step_5_php() {
    apt-get -y install php7.0 php7.0-curl php7.0-gd php7.0-imap php7.0-json php7.0-mcrypt php7.0-mysql php7.0-xml php7.0-opcache php7.0-soap php7.0-xmlrpc libapache2-mod-php7.0 php7.0-common php7.0-dev php7.0-zip php7.0-ssh2 php7.0-mbstring composer >> ${DEBUG} 2>&1
    if [ $? -ne 0 ]; then
        apt_install libapache2-mod-php5 php5 php5-common php5-curl php5-dev php5-gd php5-json php5-memcached php5-mysqlnd php5-cli php5-ssh2 php5-redis php5-mbstring composer >> ${DEBUG} 2>&1
        apt_install php5-ldap >> ${DEBUG} 2>&1
    else
        apt-get -y install php7.0-ldap >> ${DEBUG} 2>&1
    fi
}

step_6_nextdom_download() {
    echo "                                                                                    "
    rm -fr ${WEBSERVER_HOME}
    mkdir -p ${WEBSERVER_HOME} >> ${DEBUG} 2>&1

    cd  ${WEBSERVER_HOME}
    if [ "$(ls -A  ${WEBSERVER_HOME})" ]; then
        git fetch --all >> ${DEBUG} 2>&1
        git pull origin ${VERSION}
    else
        git clone --quiet https://github.com/sylvaner/nextdom-core .

    fi
}

step_7_nextdom_customization() {
    cp ${WEBSERVER_HOME}/install/apache_security /etc/apache2/conf-available/security.conf >> ${DEBUG} 2>&1
    rm /etc/apache2/conf-enabled/security.conf > /dev/null
    ln -s /etc/apache2/conf-available/security.conf /etc/apache2/conf-enabled/ >> ${DEBUG} 2>&1

    cp ${WEBSERVER_HOME}/install/apache_default /etc/apache2/sites-available/000-default.conf >> ${DEBUG} 2>&1
    rm /etc/apache2/sites-enabled/000-default.conf > /dev/null
    ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/ >> ${DEBUG} 2>&1

    rm /etc/apache2/conf-available/other-vhosts-access-log.conf > /dev/null
    rm /etc/apache2/conf-enabled/other-vhosts-access-log.conf > /dev/null

    mkdir /etc/systemd/system/apache2.service.d >${DEBUG} 2>&1
    echo "[Service]" > /etc/systemd/system/apache2.service.d/privatetmp.conf
    echo "PrivateTmp=no" >> /etc/systemd/system/apache2.service.d/privatetmp.conf

    systemctl daemon-reload >> ${DEBUG} 2>&1

    for file in $(find / -iname php.ini -type f); do
        echo "Update php file ${file}" >> ${DEBUG} 2>&1
        sed -i 's/max_execution_time = 30/max_execution_time = 600/g' ${file} > /dev/null 2>&1
        sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1G/g' ${file} > /dev/null 2>&1
        sed -i 's/post_max_size = 8M/post_max_size = 1G/g' ${file} > /dev/null 2>&1
        sed -i 's/expose_php = On/expose_php = Off/g' ${file} > /dev/null 2>&1
        sed -i 's/;opcache.enable=0/opcache.enable=1/g' ${file} > /dev/null 2>&1
        sed -i 's/opcache.enable=0/opcache.enable=1/g' ${file} > /dev/null 2>&1
        sed -i 's/;opcache.enable_cli=0/opcache.enable_cli=1/g' ${file} > /dev/null 2>&1
        sed -i 's/opcache.enable_cli=0/opcache.enable_cli=1/g' ${file} > /dev/null 2>&1
    done

    for folder in php5 php7; do
        for subfolder in apache2 cli; do
            if [ -f /etc/${folder}/${subfolder}/php.ini ]; then
                echo "Update php file /etc/${folder}/${subfolder}/php.ini" >> ${DEBUG} 2>&1
                sed -i 's/max_execution_time = 30/max_execution_time = 600/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1G/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/post_max_size = 8M/post_max_size = 1G/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/expose_php = On/expose_php = Off/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/;opcache.enable=0/opcache.enable=1/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/opcache.enable=0/opcache.enable=1/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/;opcache.enable_cli=0/opcache.enable_cli=1/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
                sed -i 's/opcache.enable_cli=0/opcache.enable_cli=1/g' /etc/${folder}/${subfolder}/php.ini > /dev/null 2>&1
            fi
        done
    done

    a2dismod status >> ${DEBUG} 2>&1
    systemctl restart apache2 > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        service apache2 restart >> ${DEBUG} 2>&1
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut redémarrer apache - Annulation${NORMAL}"
            exit 1
        fi
    fi

    systemctl stop mysql >> ${DEBUG} 2>&1
    if [ $? -ne 0 ]; then
        service mysql stop >> ${DEBUG} 2>&1
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut arrêter mysql - Annulation${NORMAL}"
            exit 1
        fi
    fi

    rm /var/lib/mysql/ib_logfile*

    if [ -d /etc/mysql/conf.d ]; then
        touch /etc/mysql/conf.d/nextdom_my.cnf
        echo "[mysqld]" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "skip-name-resolve" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "key_buffer_size = 16M" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "thread_cache_size = 16" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "tmp_table_size = 48M" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "max_heap_table_size = 48M" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "query_cache_type =1" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "query_cache_size = 32M" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "query_cache_limit = 2M" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "query_cache_min_res_unit=3K" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "innodb_flush_method = O_DIRECT" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "innodb_flush_log_at_trx_commit = 2" >> /etc/mysql/conf.d/nextdom_my.cnf
        echo "innodb_log_file_size = 32M" >> /etc/mysql/conf.d/nextdom_my.cnf
    fi

    systemctl start mysql >> ${DEBUG} 2>&1
    if [ $? -ne 0 ]; then
        service mysql start >> ${DEBUG} 2>&1
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut lancer mysql - Annulation${NORMAL}"
            exit 1
        fi
    fi

}

step_8_nextdom_configuration() {
    echo "DROP USER 'nextdom'@'localhost';" | mysql -u root -p${MYSQL_ROOT_PASSWD} > /dev/null 2>&1
    mysql -u root -p${MYSQL_ROOT_PASSWD} -e "CREATE USER 'nextdom'@'localhost' IDENTIFIED BY '${MYSQL_NEXTDOM_PASSWD}';"
    mysql -u root -p${MYSQL_ROOT_PASSWD} -e "DROP DATABASE IF EXISTS nextdom;"
    mysql -u root -p${MYSQL_ROOT_PASSWD} -e "CREATE DATABASE nextdom;"
    mysql -u root -p${MYSQL_ROOT_PASSWD} -e "GRANT ALL PRIVILEGES ON nextdom.* TO 'nextdom'@'localhost';"
    cp ${WEBSERVER_HOME}/core/config/common.config.sample.php ${WEBSERVER_HOME}/core/config/common.config.php
    sed -i "s/#PASSWORD#/${MYSQL_NEXTDOM_PASSWD}/g" ${WEBSERVER_HOME}/core/config/common.config.php
    sed -i "s/#DBNAME#/nextdom/g" ${WEBSERVER_HOME}/core/config/common.config.php
    sed -i "s/#USERNAME#/nextdom/g" ${WEBSERVER_HOME}/core/config/common.config.php
    sed -i "s/#PORT#/3306/g" ${WEBSERVER_HOME}/core/config/common.config.php
    sed -i "s/#HOST#/localhost/g" ${WEBSERVER_HOME}/core/config/common.config.php
    chmod 775 -R ${WEBSERVER_HOME}
    chown -R www-data:www-data ${WEBSERVER_HOME}
}

step_9_nextdom_installation() {
    mkdir -p /tmp/nextdom >> ${DEBUG} 2>&1
    chmod 777 -R /tmp/nextdom >> ${DEBUG} 2>&1
    chown www-data:www-data -R /tmp/nextdom >> ${DEBUG} 2>&1
    cd ${WEBSERVER_HOME}
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" >> ${DEBUG} 2>&1
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" >> ${DEBUG} 2>&1
    php composer-setup.php >> ${DEBUG} 2>&1
    php -r "unlink('composer-setup.php');" >> ${DEBUG} 2>&1
    php composer.phar require symfony/translation >> ${DEBUG} 2>&1
    composer -q install >> ${DEBUG} 2>&1
    php ${WEBSERVER_HOME}/install/install.php mode=force >> ${DEBUG} 2>&1
    if [ $? -ne 0 ]; then
        echo "${ROUGE}Ne peut installer nextdom - Annulation${NORMAL}"
        exit 1
    fi
}

step_10_nextdom_post() {
    rm /etc/cron.d/nextd* >> ${DEBUG} 2>&1
    if [ $(crontab -l | grep nextdom | wc -l) -ne 0 ];then
        (echo crontab -l | grep -v "nextdom") | crontab -  >> ${DEBUG} 2>&1

    fi
    if [ ! -f /etc/cron.d/nextdom ]; then
        echo "* * * * * www-data /usr/bin/php ${WEBSERVER_HOME}/core/php/jeeCron.php >> /dev/null" > /etc/cron.d/nextdom
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut installer le cron de nextdom - Annulation${NORMAL}"
            exit 1
        fi
    fi
    if [ ! -f /etc/cron.d/nextdom_watchdog ]; then
        echo "*/5 * * * * root /usr/bin/php ${WEBSERVER_HOME}/core/php/watchdog.php >> /dev/null" > /etc/cron.d/nextdom_watchdog
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut installer le cron de nextdom - Annulation${NORMAL}"
            exit 1
        fi
    fi
    usermod -a -G dialout,tty www-data
    if [ $(grep "www-data ALL=(ALL) NOPASSWD: ALL" /etc/sudoers | wc -l) -eq 0 ];then
        echo "www-data ALL=(ALL) NOPASSWD: ALL" | (EDITOR="tee -a" visudo)
        if [ $? -ne 0 ]; then
            printf "${ROUGE}Ne peut permettre à nextdom d'utiliser sudo - Annulation${NORMAL}"
            exit 1
        fi
    fi
    if [ $(cat /proc/meminfo | grep MemTotal | awk '{ print $2 }') -gt 600000 ]; then
        if [ $(cat /etc/fstab | grep /tmp/nextdom | grep tmpfs | wc -l) -eq 0 ];then
            echo 'tmpfs        /tmp/nextdom            tmpfs  defaults,size=128M                                       0 0' >>  /etc/fstab
        fi
    fi
    cd ${WEBSERVER_HOME} >> ${DEBUG} 2>&1
    ./gen_compress.sh >> ${DEBUG} 2>&1
    service cron start
}

step_11_nextdom_check() {
    php ${WEBSERVER_HOME}/sick.php >> ${DEBUG} 2>&1
    chmod 777 -R /tmp/nextdom >> ${DEBUG} 2>&1
    chown www-data:www-data -R /tmp/nextdom >> ${DEBUG} 2>&1
}

distrib_1_spe(){
    if [ -f post-install.sh ]; then
        rm post-install.sh >> ${DEBUG} 2>&1
    fi
    if [ -f /etc/armbian.txt ]; then
        cp ${WEBSERVER_HOME}/install/OS_specific/armbian/post-install.sh post-install.sh >> ${DEBUG} 2>&1
    fi
    if [ -f /usr/bin/raspi-config ]; then
        cp ${WEBSERVER_HOME}/install/OS_specific/rpi/post-install.sh post-install.sh >> ${DEBUG} 2>&1
    fi
    if [ -f post-install.sh ]; then
        chmod +x post-install.sh >> ${DEBUG} 2>&1
        ./post-install.sh >> ${DEBUG} 2>&1
        rm post-install.sh >> ${DEBUG} 2>&1
    fi
}

STEP=0
WEBSERVER_HOME=/var/www/html
HTML_OUTPUT=0
MYSQL_ROOT_PASSWD=$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 15)
MYSQL_NEXTDOM_PASSWD=$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 15)

while getopts ":s:v:w:h:m:" opt; do
    case $opt in
        s) STEP="$OPTARG"
        ;;
        v) VERSION="$OPTARG"
        ;;
        w) WEBSERVER_HOME="$OPTARG"
        ;;
        h) HTML_OUTPUT=1
        ;;
        m) MYSQL_ROOT_PASSWD="$OPTARG"
        ;;
        \?) echo "${ROUGE}Invalid option -$OPTARG${NORMAL}" >&2
        ;;
    esac
done

if [ ${HTML_OUTPUT} -eq 1 ]; then
    VERT="</pre><span style='color:green;font-weight: bold;'>"
    NORMAL="</span><pre>"
    ROUGE="<span style='color:red;font-weight: bold;'>"
    ROSE="<span style='color:pink;font-weight: bold;'>"
    BLEU="<span style='color:blue;font-weight: bold;'>"
    BLANC="<span style='color:white;font-weight: bold;'>"
    BLANCLAIR="<span style='color:blue;font-weight: bold;'>"
    JAUNE="<span style='color:#FFBF00;font-weight: bold;'>"
    CYAN="<span style='color:blue;font-weight: bold;'>"
    echo "<script>"
    echo "setTimeout(function(){ window.scrollTo(0,document.body.scrollHeight); }, 100);"
    echo "setTimeout(function(){ window.scrollTo(0,document.body.scrollHeight); }, 300);"
    echo "setTimeout(function(){ location.reload(); }, 1000);"
    echo "</script>"
    echo "<pre>"
fi

displaylogo()
{
    echo ""
    echo "███╗   ██╗███████╗██╗  ██╗████████╗██████╗  ██████╗ ███╗   ███╗"
    echo "████╗  ██║██╔════╝╚██╗██╔╝╚══██╔══╝██╔══██╗██╔═══██╗████╗ ████║"
    echo "██╔██╗ ██║█████╗   ╚███╔╝    ██║   ██║  ██║██║   ██║██╔████╔██║"
    echo "██║╚██╗██║██╔══╝   ██╔██╗    ██║   ██║  ██║██║   ██║██║╚██╔╝██║"
    echo "██║ ╚████║███████╗██╔╝ ██╗   ██║   ██████╔╝╚██████╔╝██║ ╚═╝ ██║                     "
    echo "╚═╝  ╚═══╝╚══════╝╚═╝  ╚═╝   ╚═╝   ╚═════╝  ╚═════╝ ╚═╝     ╚═╝                     "
    echo "                                                                                    "
    printf "${CYAN}Bienvenue dans l'installateur de NextDom${NORMAL}                        \n"

}

infos(){
    printf "${CYAN}Version d'installation de NextDom : ${VERSION}${NORMAL}                  \n"
    printf "${CYAN}Dossier principal du serveur web : ${WEBSERVER_HOME}${NORMAL}            \n"
}

selectoption(){
    PS3='Selectionner la branche github a installer: '
    options=("master" "develop" "feature/Sass" "Quit")
    select opt in "${options[@]}"
    do
        case $opt in
            "master")
            VERSION=master
            clear
            displaylogo
            infos
            break
            ;;
            "develop")
            VERSION=develop
            clear
            displaylogo
            infos
            break
            ;;
            "feature/Sass")
            VERSION=feature/Sass
            clear
            displaylogo
            infos
            break
            ;;
            "Quit")
            break
            ;;
            *) echo "invalid option $REPLY";;
        esac
    done
}

progress()
{
    PARAM_PROGRESS=$1
    PARAM_PHASE=$2

    if [ $PARAM_PROGRESS = 0 ]  ; then echo -ne  "[..........................] (0%)  $PARAM_PHASE \r"  ; step_1_upgrade; fi;
    if [ $PARAM_PROGRESS = 5 ]  ; then echo -ne "[#.........................] (5%)  $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 10 ]; then echo -ne "[##........................] (10%) $PARAM_PHASE \r"  ; step_2_mainpackage; fi;
    if [ $PARAM_PROGRESS = 15 ]; then echo -ne "[###.......................] (15%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 20 ]; then echo -ne "[####......................] (20%) $PARAM_PHASE \r"  ; step_3_database; fi;
    if [ $PARAM_PROGRESS = 25 ]; then echo -ne "[#####.....................] (25%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 30 ]; then echo -ne "[######....................] (30%) $PARAM_PHASE \r"  ; step_4_apache; fi;
    if [ $PARAM_PROGRESS = 35 ]; then echo -ne "[#######...................] (35%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 40 ]; then echo -ne "[########..................] (40%) $PARAM_PHASE \r"  ; step_5_php; fi;
    if [ $PARAM_PROGRESS = 45 ]; then echo -ne "[#########.................] (45%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 50 ]; then echo -ne "[##########................] (50%) $PARAM_PHASE \r"  ; step_6_nextdom_download; fi;
    if [ $PARAM_PROGRESS = 55 ]; then echo -ne "[###########...............] (55%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 60 ]; then echo -ne "[############..............] (60%) $PARAM_PHASE \r"  ; step_7_nextdom_customization; fi;
    if [ $PARAM_PROGRESS = 65 ]; then echo -ne "[#############.............] (65%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 70 ]; then echo -ne "[###############...........] (70%) $PARAM_PHASE \r"  ; step_8_nextdom_configuration; fi;
    if [ $PARAM_PROGRESS = 75 ]; then echo -ne "[#################.........] (75%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 80 ]; then echo -ne "[####################......] (80%) $PARAM_PHASE \r"  ; step_9_nextdom_installation; fi;
    if [ $PARAM_PROGRESS = 85 ]; then echo -ne "[#######################...] (85%) $PARAM_PHASE \r"  ; delay; fi;
    if [ $PARAM_PROGRESS = 90 ]; then echo -ne "[########################..] (90%) $PARAM_PHASE \r" ; step_10_nextdom_post; fi;
    if [ $PARAM_PROGRESS = 95 ]; then echo -ne "[##########################] (95%) $PARAM_PHASE \r" ; step_11_nextdom_check; fi;
    if [ $PARAM_PROGRESS = 100 ]; then echo -ne "[##########################] (100%) $PARAM_PHASE \r \n" ; distrib_1_spe; fi;
}

displaylogo
selectoption
printf "${CYAN}Avancement de l'installation${NORMAL}               \n"
progress 0 "upgrade du system                                                 "
progress 5  "upgrade du system                                                 "
progress 10 "installation des packages de base                                "
progress 15 "installation des packages de base                                "
progress 20 "installation de la base de donnée                                "
progress 25 "installation de la base de donnée                                "
progress 30 "installation apache                                              "
progress 35 "installation apache                                              "
progress 40 "installation php                                                 "
progress 45 "installation php                                                 "
progress 50 "telechargement de nextdom                                        "
progress 55 "telechargement de nextdom                                        "
progress 60 "customisation de nextdom                                         "
progress 65 "customisation de nextdom                                         "
progress 70 "configuration de nextdom                                         "
progress 75 "configuration de nextdom                                         "
progress 80 "installation de nextdom                                          "
progress 85 "installation de nextdom                                          "
progress 90 "opérations post installation                                     "
progress 95 "verification de l'installation                                   "
progress 100 "suppression des fichiers inutiles                               "
clear

displaylogo
printf "${VERT}installation terminée avec succes   ${NORMAL}                \n"
printf "Le mot de passe root MySQL est ${CYAN}${MYSQL_ROOT_PASSWD}${NORMAL} \n"
printf "Un redémarrage devrait être effectué             \n"

rm -rf ${WEBSERVER_HOME}/index.html > /dev/null 2>&1

exit 0
