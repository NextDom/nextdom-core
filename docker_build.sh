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
    echo -e "\th\tThis help"
    exit 0
}


#getOptions
while getopts ":hpu" opt; do
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

echo stopping $(docker stop ${CNAME})
echo stopping $(docker stop ${MYSQLNAME})

echo removing $(docker rm ${CNAME})
echo removing $(docker rm ${MYSQLNAME})

docker build -f ${DKRFILE} . --tag ${TAG}
docker-compose -f ${YML} up -d

echo working on ${CNAME}
echo -e "\nTant que le dépot est privé, il faut relancer l'init manuellement pour entrer les login/mdp github\n"
echo -e "\tdocker attach ${CNAME}"
echo -e "\t./root/init.sh"
## pdt le dev de install, il faut ecraser celui du depot
docker cp install/install.sh ${CNAME}:/root/
echo "/!\ now, entering in the docker container"
docker attach ${CNAME}
alias logmy='docker logs -f nextdom-mysql'
alias logdev='docker logs -f nextdom-dev'
alias gomy='docker exec -it nextdom-mysql bash'
alias godev='docker exec -it nextdom-dev bash'