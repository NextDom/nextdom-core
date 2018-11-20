#!/usr/bin/env bash

#Directory for apache certificate
SSLDIR=apache/conf/
#Archive containing the nextdom-core project
NEXTDOMTAR=nextdom-dev.tar.gz
#Volume for html
VOLHTML=wwwdata-prod
#volume for mysql
VOLMYSQL=mysqldata-prod
#Use zip unstead of git clone
ZIP=N
#Keep volume if Y, recreate otherwise
KEEP=N

#fonctions
usage(){
    echo -e "\n$0: [d,m,(u|p)]\n\twithout option, container is built from github sources and has no access to devices"
    echo -e "\tk\tcontainer volumes are not recreated, but reused ( keep previous data intact)"
    echo -e "\tp\tcontainer has access to all devices (privileged: not recommended)"
    echo -e "\tu\tcontainer has access to ttyUSB0"
    echo -e "\th\tThis help"
    exit 0
}

generateCert(){
    echo "<I> Creating SSL self-signed certificat in /etc/nextdom/ssl/"
    openssl genrsa -out ${SSLDIR}nextdom.key 2048 2>&1
    openssl req -new -key ${SSLDIR}nextdom.key -out ${SSLDIR}nextdom.csr -subj "/C=FR/ST=Paris/L=Paris/O=Global Security/OU=IT Department/CN=example.com" 2>&1
    openssl x509 -req -days 3650 -in ${SSLDIR}nextdom.csr -signkey ${SSLDIR}nextdom.key -out ${SSLDIR}nextdom.crt 2>&1
}

createVolumes(){
for volname in ${VOLHTML} ${VOLMYSQL}
    do
    VOL2DELETE=$(docker volume ls -qf name=${volname})
    [[ ! -z ${VOL2DELETE} ]] && echo **deleting volume $(docker volume rm ${VOL2DELETE})
    echo *creating volume $(docker volume create ${volname})
    done
}

define_nextom_mysql_credentials(){
    confFile=nextdom/common.config.php
    sample=nextdom/common.config.sample.php
    [[ ! -e  ${sample} ]] && echo "${sample} is missing" && exit
    [[ -e  ${confFile} ]] && rm -f ${confFile}

    cp  ${sample} ${confFile}
    sed -i "s/#PASSWORD#/${MYSQL_PASSWORD}/g" ${confFile}
    sed -i "s/#DBNAME#/${MYSQL_DATABASE}/g" ${confFile}
    sed -i "s/#USERNAME#/${MYSQL_USER}/g" ${confFile}
    sed -i "s/#PORT#/${MYSQL_PORT}/g" ${confFile}
    sed -i "s/#HOST#/${MYSQL_HOST}/g" ${confFile}
}

makeZip(){
    echo makeZip $1
    [[ -z $1 ]] && echo no zipfile name given && exit -1
    for item in "3rdparty/ assets/ backup/ core/ data/ desktop/ install/ mobile/ public/ scripts/ src/ tests/ \
    translations/ views/ index.php package.json composer.json"
        do
            TOTAR+="${item} "
        done
    echo ${TOTAR}
    tar --warning=no-file-changed -zcf ${1} -C ././../../../../ ${TOTAR}
    exitcode=$?
}

# volatime container to process assets
gen_assets_composer(){
    #Install compose dependancies
    docker run --rm -it -v ${VOLHTML}:/app composer/composer install
    #generate assets in volume
    docker build -f ./Tools/Dockerfile.sass -t node-sass ./Tools/
    docker run --rm -ti -v ${VOLHTML}:/var/www node-sass:latest bash -c "cd /var/www; cp package.json ./vendor/; npm install --prefix ./vendor; cd ./scripts; ./gen_assets.sh"
}


#main
source .env
source envWeb
source envMysql
#ZIP=Y


#getOptions
while getopts ":hkpuz" opt; do
    case $opt in
        k) echo -e "\nkeep docker volume (htm & database)"
        KEEP=Y
        ;;
        h) usage
        ;;
        p) echo -e "\ndocker will have access to all devices\n"
        YML="docker-compose.yml -f docker-compose-privileged.yml"
        ;;
        u) echo -e "\n docker will have access to ttyUSB0\n"
        YML="docker-compose.yml -f docker-compose-devices.yml"
        ;;
        z) echo -e "\nMaking a zip from local file"
        ZIP=Y
        makeZip ${NEXTDOMTAR}
        ;;
        \?) echo "${ROUGE}Invalid option -$OPTARG${NORMAL}" >&2
        ;;
    esac
done

#generate auto-signed certificate
[[ ! -f ${SSLDIR}nextdom.key ]] && generateCert

#create config file to access mysql database
define_nextom_mysql_credentials

#prepare zip file containing project
#[[ -f ${NEXTDOMTAR} ]] &&  rm ${NEXTDOMTAR}
[[ ! -f ${NEXTDOMTAR} ]] && makeZip ${NEXTDOMTAR}

# stop running container
docker-compose -f docker-compose.yml stop
# remove existing container
# remove existing container
[[ ! -z $(docker-compose ps -q --filter name=nextdom-web)  ]] && echo removing $(docker-compose rm -sf nextdom-web )
[[ ! -z $(docker-compose ps -q --filter name=nextdom-adminer)  ]] &&echo removing $(docker-compose rm -sf nextdom-adminer)
[[ ! -z $(docker-compose ps -q --filter name=nextdom-mysql)  ]] &&echo removing $(docker-compose rm -sf nextdom-mysql)

# prepare volumes
[[ "Y" != ${KEEP} ]] && createVolumes

#write secrets for docker
[[ ! -f githubtoken.txt ]] && echo please create a txt file names githubtokeb.txt with the value of the githubtoken or login:password && exit -1

# build
#CACHE="--no-cache"
docker-compose -f docker-compose.yml build ${CACHE}


# extract local project to container volume
if [ "Y" != ${KEEP} ]; then
    if [ "Y" == ${ZIP} ]; then
        echo unzipping ${NEXTDOMTAR}
        docker run --rm -v ${VOLHTML}:/var/www/html/ -v $(pwd):/backup ubuntu bash -c "tar -zxf /backup/${NEXTDOMTAR} -C /var/www/html/"
        else
        echo cloning project
        docker run --rm -v ${VOLHTML}:/git/ alpine/git clone https://$(cat githubtoken.txt)@github.com/sylvaner/nextdom-core.git .
        docker run --rm -v ${VOLHTML}:/git/ alpine/git checkout ${VERSION}
    fi
fi

docker-compose run --rm -v ${VOLHTML} nextdom-web grep -A4 host /var/www/html/core/config/common.config.php
docker-compose run --rm -v ${VOLMYSQL} nextdom-mysql /usr/bin/mysql -uroot -hlocalhost -p${MYSQL_ROOT_PASSWORD} -e 'select user,host from mysql.user;'

#install assets/dependancies
gen_assets_composer

docker cp ../../../../backup/backup-holdom2.duckdns.org-3.2.11-2018-11-18-00h51.tar.gz $(docker-compose ps -q nextdom-web):/var/www/html/backup/

docker-compose -f docker-compose.yml up --remove-orphans
exit
