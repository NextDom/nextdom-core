#!/bin/bash

echo -n "[$(date +%d-%m-%Y\ %H:%M:%S)] Check the file system space..."
USERSPACE=$(df -h . | awk '/[0-9]/{print $(NF-1)}' | sed 's/\%//g')
if [ ${USERSPACE} -gt 95 ]; then
	echo 'NOK'
else
	echo "OK"
fi


echo -n "[$(date +%d-%m-%Y\ %H:%M:%S)] Check mysql..."
sudo service mysql status >> /dev/null 2>&1
if [ $? -ne 0 ]; then
	echo -n "NOK, try to restart it..."
	sudo service mysql start
	sudo service mysql status >> /dev/null 2>&1
	if [ $? -ne 0 ]; then
		echo "[$(date +%d-%m-%Y\ %H:%M:%S)] Can not start it"
		exit 1
	fi
else
	echo "OK"
fi

echo -n "[$(date +%d-%m-%Y\ %H:%M:%S)] Check cron..."
sudo service cron status >> /dev/null 2>&1
if [ $? -ne 0 ]; then
	echo -n "NOK, try to restart it..."
	sudo service cron start
	sudo service cron status >> /dev/null 2>&1
	if [ $? -ne 0 ]; then
		echo "[$(date +%d-%m-%Y\ %H:%M:%S)] Can not start it"
		exit 1
	fi
else
	echo "OK"
fi

echo -n "[$(date +%d-%m-%Y\ %H:%M:%S)] Check the cron of nextdom..."
if [ $(crontab -l | grep jeeCron | wc -l) -lt 1 ]; then
	if [ ! -f /etc/cron.d/nextdom ]; then
		echo 'NOK'
	else
		echo "OK"
	fi
else
	echo "OK"
fi

