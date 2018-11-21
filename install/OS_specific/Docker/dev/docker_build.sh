#!/usr/bin/env bash
# dockerfile used to build container
DKRFILE=Dockerfile.develop
# docker image tag
TAG=nextdom/dev
# YML to build 2 containers (apache, php /mysql )
YML=docker-compose.yml
# parameters
DENV=.env
# empty for full debian package install, dev for github install
MODE=
#Archive name
NEXTDOMTAR=nextdom-dev.tar.gz
# volume containers name
VOLHTML=wwwdata-dev
VOLMYSQL=mysqldata-dev
ZIP=N
KEEP=N
#

#fonctions
usage(){
    echo -e "\n$0: [d,m,(u|p)]\n\twithout option, container is built from github sources and has no access to devices"
    echo -e "\tm\tcontainer is in dev mode (ie built from github, from debian package otherwise, used in conjonction with -d)"
    echo -e "\tk\tcontainer volumes are not recreated, but reused ( keep previous data intact)"
    echo -e "\tp\tcontainer has access to all devices (privileged: not recommended)"
    echo -e "\tu\tcontainer has access to ttyUSB0"
    echo -e "\tz\tcontainer is populated with local project, not the commited one"
    echo -e "\th\tThis help"
    exit 0
}

copyNeededFilesForImage(){

#for fil in motd bashrc
for fil in motd
do
    cp ../../../${fil} ${fil}
done
for fil in nextdom.conf nextdom-ssl.conf nextdom-security.conf privatetmp.conf
do
    cp ../../../apache/${fil} ${fil}
done

echo ${MYSQL_ROOT_PASSWORD} > mysqlroot

}

deleteCopiedFiles(){
    rm motd nextdom.conf nextdom-ssl.conf nextdom-security.conf privatetmp.conf mysqlroot
}

createVolumes(){
for volname in ${VOLHTML} ${VOLMYSQL}
    do
    VOL2DELETE=$(docker volume ls -qf name=${volname})
    [[ ! -z ${VOL2DELETE} ]] && echo deleting volume $(docker volume rm ${VOL2DELETE})
    echo creating volume $(docker volume create ${volname})
    done
}

makeZip(){
 echo makeZip $1
 [[ -z $1 ]] && echo no zipfile name given && exit -1
 for item in "3rdparty/ assets/ backup/ core/ data/ desktop/ install/ .git/ log/ mobile/ public/ script/ scripts/ src/ tests/ \
 translations/ var/ views/ index.php package.json composer.json"
    do
       TOTAR+="${item} "
    done
 echo ${TOTAR}
 tar -zcf ${1} -C ././../../../../ ${TOTAR}
}

#Main
source ${DENV}

#getOptions
while getopts ":hkpuz" opt; do
    case $opt in
        k) echo "Keep volumes (web & mysql)"
        KEEP=Y
        ;;
        p) echo -e "\ndocker will have access to all devices\n"
        YML="docker-compose.yml -f docker-compose-privileged.yml"
        ;;
        u) echo -e "\n docker will have access to ttyUSB0\n"
        YML="docker-compose.yml -f docker-compose-devices.yml"
        ;;
        h) usage
        ;;
        z) echo -e "\nMaking a zip from local project, and injecting it into the web volume"
        ZIP=Y
        makeZip ${NEXTDOMTAR}
        ;;
        \?) echo "${ROUGE}Invalid option -$OPTARG${NORMAL}" >&2
        ;;
    esac
done

# remove existing container
[[ ! -z $(docker-compose ps -q --filter name=nextdom-web)  ]] && echo removing $(docker-compose rm -sf nextdom-web)
[[ ! -z $(docker-compose ps -q --filter name=nextdom-adminer)  ]] &&echo removing $(docker-compose rm -sf nextdom-adminer)
[[ ! -z $(docker-compose ps -q --filter name=nextdom-mysql)  ]] &&echo removing $(docker-compose rm -sf nextdom-mysql)

#docker system prune -f --volumes

#Check githubToken
#write secrets for docker
if [ ! -f githubtoken.txt ] || [ -z $(cat githubtoken.txt) ] ;then
 echo please create a txt file names githubtoken.txt with the value of the githubtoken or login:password && exit -1
fi
GITHUBTOKEN=$(cat githubtoken.txt)

# prepare volumes
[[ "Y" != ${KEEP} ]] && createVolumes

#build apache image
copyNeededFilesForImage

echo -e "\nbuilding ${CNAME} from ${DKRFILE}\n"

if [[ ${TAG} =~ .*deb.* ]]; then
    docker build -f ${DKRFILE} -t ${TAG} .
    else
     docker-compose -f ${YML} build --build-arg numPhp=${numPhp} --build-arg MYSQLROOT=${MYSQL_ROOT_PASSWORD}
    fi

deleteCopiedFiles

if [ "Y" == ${ZIP} ]; then
    echo unzipping ${NEXTDOMTAR}
    docker-compose run --rm ${CNAME} -v ${VOLHTML}:/var/www/html/ -v $(pwd):/backup web bash -c "tar -zxf /backup/${NEXTDOMTAR} -C /var/www/html/"
    else
    echo cloning project
    docker-compose run --rm -v ${VOLHTML}:/git/ ${CNAME} bash -c "cd /git/; rm index.html; ls -al; git clone https://${GITHUBTOKEN}@github.com/sylvaner/nextdom-core.git ."
    docker-compose run --rm -v ${VOLHTML}:/git/ ${CNAME} bash -c "cd /git/; git checkout ${VERSION}"
fi



docker-compose -f ${YML} up