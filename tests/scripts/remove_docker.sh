#!/bin/sh

docker kill nextdom-test > /dev/null 2>&1
docker rm nextdom-test > /dev/null 2>&1
docker rmi nextdom-test-snap > /dev/null 2>&1
