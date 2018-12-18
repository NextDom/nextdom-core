#!/bin/bash

docker kill nextdom-test
docker rm nextdom-test
docker rmi nextdom-test-snap

# Go to base path
cd ..
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
docker commit nextdom-test nextdom-test-snap
docker kill nextdom-test
docker rm nextdom-test
