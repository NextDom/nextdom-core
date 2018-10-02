#!/usr/bin/env bash

DKRFILE=Dockerfile.develop
TAG=nextdom/dev
YML=docker-compose.yml
DENV=.env

#fonctions
usage(){
    echo -e "\n$0:\n\twithout option, container has no access to devices"
    echo -e "\tp\tcontainer has access to all devices (privileged: not recommended)"
    echo -e "\tu\tcontainer has access to ttyUSB0"
    echo -e "\tm\tcontainer is in demo or dev ( only available with debian install"
    echo -e "\th\tThis help"
    exit 0
}

copyNeededFilesForImage(){

for fil in motd bashrc postinst.sh
do
    cp ../../${fil} ${fil}
done
for fil in nextdom.conf nextdom-ssl.conf nextdom-security.conf privatetmp.conf
do
    cp ../../apache/${fil} ${fil}
done

echo ${MYSQLROOT} > mysqlroot

}

deleteCopiedFiles(){
    rm motd bashrc nextdom.conf nextdom-ssl.conf nextdom-security.conf privatetmp.conf postinst.sh mysqlroot
}

#getOptions
while getopts ":hpmu" opt; do
    case $opt in
        p) echo -e "\ndocker will have access to all devices\n"
        YML="docker-compose.yml -f docker-compose-privileged.yml"
        ;;
        u) echo -e "\n docker will have access to ttyUSB0\n"
        YML="docker-compose.yml -f docker-compose-devices.yml"
        ;;
        h) usage
        ;;
        \?) echo "${ROUGE}Invalid option -$OPTARG${NORMAL}" >&2
        ;;
    esac
done

#Main

source ${DENV}

#echo stopping $(docker stop ${CNAME})
#echo stopping $(docker stop ${MYSQLNAME})

#echo removing $(docker rm ${CNAME})
#docker system prune -f --volumes
#echo removing $(docker rm ${MYSQLNAME})

copyNeededFilesForImage
docker-compose build --no-cache --build-arg numPhp=${numPhp} --build-arg GITHUB_TOKEN=${GITHUBTOKEN} --build-arg MYSQLROOT=${MYSQLROOT}
docker-compose -f ${YML} up -d
#Not commited yet...
docker cp ../../postinst.sh  ${CNAME}:/var/www/html/install/postinst.sh
docker cp init.sh ${CNAME}:/root/
deleteCopiedFiles

echo working on ${CNAME}
echo -e "\nTant que le dépot est privé, il faut indiquer le token github ou le login/mdp dans le .env dans la variable GITHUBTOKEN\n"
echo -e "\tdocker attach ${CNAME}"
echo -e "\t./root/init.sh"
echo "/!\ now, entering in the docker container"
docker attach ${CNAME}

#docker cp postinst.sh nextdom-dev:/var/www/html/install/postinst.sh
#docker cp OS_specific/Docker/init.sh nextdom-dev:/root/
