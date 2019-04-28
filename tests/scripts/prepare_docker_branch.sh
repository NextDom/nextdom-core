#!/bin/bash

set -e
if [[ "$OSTYPE" == "darwin"* ]]; then
    rootDir=$(dirname $(dirname $(cd $(dirname $0) && pwd -P)))
else
    rootDir=$(dirname $(dirname $(dirname $(readlink -n -f $0))))
fi

if [ $# -eq 0 ]; then
	echo "Le nom de la branche doit être indiquée"
	exit 1
fi

if [ ! -z "$2" ]; then
    baseImage=$2;
else
  baseImage="nextdom/nextdom-core:test"
  docker pull ${baseImage}
fi

docker kill nextdom-test > /dev/null 2>&1 || true
docker rm nextdom-test > /dev/null 2>&1   || true

echo "step 1. cloning target branch $1..."
tmpDir=$(mktemp -d)
git clone https://github.com/nextdom/nextdom-core ${tmpDir}
cd ${tmpDir}
git checkout $1

echo "step 2. creating installer container nextdom-test from ${baseImage}..."
docker run -d -p 8765:80 -v ${rootDir}:/data --name="nextdom-test" ${baseImage} > /dev/null || {
  echo "-> unable to run installer container"
  exit 1
}

echo -n "step 3. watting for installation to complete..."
END_OF_INSTALL_STR="OK NEXTDOM TEST READY"
while true; do
	DOCKER_LOGS=$(docker logs --tail 10 nextdom-test 2>&1)
	if [[ "$DOCKER_LOGS" =~ .*NEXTDOM.TEST.READY.* ]]; then
		break
	fi
  echo -n "."
	sleep 2
done
echo " "

echo "step 4. snapshotting container to nextdom-test-snap..."
docker exec nextdom-test /bin/rm -fr /usr/share/nextdom/plugins/*
docker commit nextdom-test nextdom-test-snap >/dev/null


echo "step 4. destroying installer container..."
docker kill nextdom-test >/dev/null || {
  echo "-> unable to kill container nextdom-test"
  exit 1
}
docker rm nextdom-test >/dev/null || {
  echo "-> unable to remove container nextdom-test "
  exit 1
}
