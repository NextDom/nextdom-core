#!/bin/bash

docker run -d -p 8765:80 --name=$1 nextdom-test-snap:latest /launch.sh > /dev/null 2>&1
sleep 10
if [ "$2" != "" ]; then
	./scripts/sed_in_docker.sh "nextdom::firstUse = 1" "nextdom::firstUse = 0" /var/lib/nextdom/config/default.config.ini $1
	./scripts/sed_in_docker.sh "nextdom::Welcome = 1" "nextdom::Welcome = 0" /var/lib/nextdom/config/default.config.ini $1
	docker exec -i $1 /usr/bin/mysql -u root nextdomdev <<< "UPDATE user SET password = SHA2('$2', 512)"  
fi
