#!/bin/bash
echo 'Start init'

echo $(set)

# Main
set -x

if ! [ -f /.dockerinit ]; then
	touch /.dockerinit
	chmod 755 /.dockerinit
fi


if [ ! -z ${MODE_HOST} ] && [ ${MODE_HOST} -eq 1 ]; then
	echo 'Update /etc/hosts for host mode'
	echo "127.0.0.1 localhost nextdom" > /etc/hosts
fi

if [ -f "/var/www/html/_nextdom_is_installed" ]; then
	echo 'NextDom is already install'
else
	echo 'Start nextdom customization'
	php /var/www/html/install/install.php
	touch /var/www/html/_nextdom_is_installed
fi

echo 'All init complete'
mkdir -p /var/log/supervisor/ /var/log/apache2/ /var/log/nextdom/ && touch /var/log/nextdom/plugin
chown -R www-data:www-data /var/www/html /var/log/nextdom/
chmod 777 /dev/tty*
chmod 777 -R /tmp
chmod 755 -R /var/www/html /var/log/nextdom/


echo 'Start apache2'
systemctl restart apache2
service apache2 restart

#[[ $(ps -C cron | wc -l) -lt 2 ]] && /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf