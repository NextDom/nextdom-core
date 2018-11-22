#!/bin/bash

pip3 install selenium

if [ "$(uname)" == "Darwin" ]; then
    curl -s https://chromedriver.storage.googleapis.com/2.43/chromedriver_mac64.zip > chromedriver.zip
else
    curl -s https://chromedriver.storage.googleapis.com/2.43/chromedriver_linux64.zip > chromedriver.zip
fi
unzip -a chromedriver.zip
cp -fr chromedriver gui/chromedriver
chmod +x gui/chromedriver
rm -fr chromedriver
rm -fr chromedriver.zip

