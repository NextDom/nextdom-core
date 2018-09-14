#!/bin/bash
${PWD}/gen_assets.sh
if [ ! -d "${PWD}/vendor" ]; then
	composer install && echo " >>> composer installed";
else
	composer update
	echo " >>> composer updated"
fi
# Skipping binaries and hidden files
find . -type f -not -path '*/\.*' -exec grep -Il '.' {} \; | xargs -d '\n' -L 1 dos2unix -k
