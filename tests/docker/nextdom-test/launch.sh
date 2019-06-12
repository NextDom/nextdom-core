#!/bin/sh

service cron start
service mysql start
service apache2 start
while true; do
  sleep 100
done
