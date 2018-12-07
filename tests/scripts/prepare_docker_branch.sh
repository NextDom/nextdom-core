#!/bin/bash

if [ $# -eq 0 ]; then
	echo "Le nom de la branche doit être indiquée"
	exit 1
fi
rm -fr /tmp/nextdom-core
cd /tmp
echo "Clone repo"
git clone https://github.com/Sylvaner/nextdom-core
echo "Change branch"
cd nextdom-core
git checkout $1
docker run -d -p 8765:80 -v `pwd`:/data --name="nextdom-test" sylvaner1664/nextdom-test:latest > /dev/null 2>&1
END_OF_INSTALL_STR="OK NEXTDOM TEST READY"

while true
do
	DOCKER_LOGS=$(docker logs nextdom-test 2>&1)
	if [[ "$DOCKER_LOGS" =~ .*OK.NEXTDOM.TEST.READY.* ]]; then
		break
	fi
	sleep 10
done
echo "Container created, Write image"
docker commit nextdom-test nextdom-test-snap
echo "Clear container"
docker kill nextdom-test
docker rm nextdom-test
