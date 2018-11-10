#!/bin/bash

sed -i 's#/var/www/html/log/http.error#/var/log/nextdom/http.error#g' /etc/apache2/sites-enabled/*
sed -i 's#/var/www/html/log/http.error#/var/log/nextdom/http.error#g' /etc/apache2/conf-enabled/*
mkdir -p /var/log/nextdom/scenarioLog
chown www-data:www-data -R /var/log/nextdom
service apache2 restart