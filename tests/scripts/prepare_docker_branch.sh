#!/bin/bash

if [ $# -eq 0 ]; then
	echo "Le nom de la branche doit être indiquée"
	exit 1
fi

# kill old docker if exists
if [ ! "$(docker ps -a | grep nextdom-test-migration\\\$)" ]; then
	if [ ! "$(docker ps -q -f name=nextdom-test\\\$)" ]; then
		docker kill nextdom-test
	fi
	docker rm nextdom-test
fi

rm -fr /tmp/nextdom-core
cd /tmp
echo "Clone repo"
git clone https://github.com/nextdom/nextdom-core
echo "Change branch"
cd nextdom-core
git checkout $1
docker run -d --rm -p 8765:80 -v `pwd`:/data --name="nextdom-test" nextdom/nextdom-test:latest /start.sh > /dev/null 2>&1
END_OF_INSTALL_STR="OK NEXTDOM TEST READY"

while true
do
	DOCKER_LOGS=$(docker logs --tail 10 nextdom-test 2>&1)
	if [[ "$DOCKER_LOGS" =~ .*NEXTDOM.TEST.READY.* ]]; then
		break
	fi
	sleep 2
done
echo "Delete plugins"
docker exec nextdom-test /bin/rm -fr /var/www/html/plugins/*
echo "Container created, Write image"
docker commit nextdom-test nextdom-test-snap
echo "Clear container"
docker kill nextdom-test
