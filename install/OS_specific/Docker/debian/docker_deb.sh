#!/usr/bin/env bash
# dockerfile used to build container
DKRFILE=Dockerfile.deb
# docker image tag
TAG=nextdom/deb
# dockerfile
YML=docker-compose.yml
# parameters
DENV=.env
# empty for full options, demo for restricted php modules enabled (demo in the wild)
MODE=
#Archive name
NEXTDOMTAR=nextdom-dev.tar.gz
#

#fonctions
usage(){
    echo -e "\n$0: [d,m,(u|p)]\n\twithout option, container is built from nextdom's debian packages and has no access to devices"
    echo -e "\td\tcontainer is in demo mode, php modules are disabled to limit surface of attack when nextdom is exposed to unknown users/testers."
    echo -e "\tp\tcontainer has access to all devices (privileged: not recommended)"
    echo -e "\tu\tcontainer has access to ttyUSB0"
    echo -e "\th\tThis help"
    exit 0
}

#Main
#getOptions
while getopts ":dhpu" opt; do
    case $opt in
        d) MODE=demo
        echo "mode demo"
        MODE=demo
        ;;
        p) echo -e "\ndocker will have access to all devices\n"
        YML="docker-compose.yml -f docker-compose-privileged.yml"
        ;;
        u) echo -e "\n docker will have access to ttyUSB0\n"
        YML="docker-compose.yml -f docker-compose-devices.yml"
        ;;
        h) usage
        ;;
        \?) echo "${ROUGE}Invalid option -$OPTARG${NORMAL}" >&2
        ;;        z) echo -e "\nMaking a zip from local file"
    esac
done


# remove existing container
[[ ! -z $(docker-compose ps -q --filter name=nextdom-deb)  ]] &&  echo $(docker-compose rm -sf nextdom-deb)
#docker system prune -f --volumes

#build image
echo -e "\nbuilding nextdom-deb from nextdom debian package\n"
CACHE=""
#CACHE="--no-cache"
#docker-compose -f ${YML} build ${CACHE} --build-arg MODE=${MODE}

docker-compose -f ${YML} up