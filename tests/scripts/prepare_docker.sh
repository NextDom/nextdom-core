#!/bin/bash

set -e

rootDir=$(dirname $(dirname $(dirname $(readlink -n -f $0))))
baseImage="sylvaner1664/nextdom-test:latest"
if [ ! -z "$1" ]; then
    baseImage=$1;
fi

docker kill nextdom-test > /dev/null 2>&1 || true
docker rm nextdom-test > /dev/null 2>&1   || true

# Go to base path
echo "step 1. creating installer container nextdom-test from ${baseImage}..."
docker run -d -p 8765:80 -v ${rootDir}:/data --name="nextdom-test" ${baseImage} > /dev/null 2>&1
END_OF_INSTALL_STR="OK NEXTDOM TEST READY"

echo -n "step 2. watting for installation to complete..."
while true; do
	DOCKER_LOGS=$(docker logs --tail 10 nextdom-test 2>&1)
	if [[ "$DOCKER_LOGS" =~ .*NEXTDOM.TEST.READY.* ]]; then
		break
	fi
  echo -n "."
	sleep 2
done
echo " "

echo "step 3. snapshotting container to nextdom-test-snap..."
docker exec nextdom-test /bin/rm -fr /var/www/html/plugins/*
docker commit nextdom-test nextdom-test-snap >/dev/null

echo "step 4. destroying installer container..."
docker kill nextdom-test >/dev/null || {
  echo "-> unable to kill container nextdom-test"
  exit 1
}

docker rm   nextdom-test >/dev/null || {
  echo "-> unable to remove container nextdom-test "
  exit 1
}
