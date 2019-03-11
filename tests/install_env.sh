#!/bin/bash

docker --version > /dev/null 2>&1
if [ "$?" -ne 0 ]; then
	apt-get update
	apt-get install -y apt-transport-https ca-certificates curl gnupg2 software-properties-common
	curl -fsSL https://download.dockeR.com/linux/debian/gpg | apt-key add -
	add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
	apt-get update
	apt-get install -y docker-ce
fi

python3 --version > /dev/null 2>&1
if [ "$?" -ne 0 ]; then
	apt-get install -y python3
fi

pip3 --version > /dev/null 2>&1
if [ "$?" -ne 0 ]; then
	apt-get install -y python3-pip
fi
pip3 install selenium

if [ "$(uname)" == "Darwin" ]; then
    curl -s https://chromedriver.storage.googleapis.com/2.43/chromedriver_mac64.zip > chromedriver.zip
else
    curl -s https://chromedriver.storage.googleapis.com/2.43/chromedriver_linux64.zip > chromedriver.zip
fi

unzip -a chromedriver.zip > /dev/null
cp -fr chromedriver tests/chromedriver
chmod +x tests/chromedriver
rm -fr chromedriver
rm -fr chromedriver.zip

echo "Pour qu'un utilisateur puisse lancer docker : "
echo "usermod -a -G docker USERNAME"
