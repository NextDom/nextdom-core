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
	#backward compatibility for postinst.
	#Var renamed in order to use docker mysql embedded env var.
    [[ ( "${MYSQL_HOST}" != "localhost" ) && ( -f .mysqlroot ) ]] && MYSQL_ROOT_PASSWORD="-r $(cat .mysqlroot)"
	bash -x /var/www/html/install/postinst -r ${MYSQL_ROOT_PASSWORD} -i ${MYSQL_HOST} -z ${MYSQL_PORT} -d ${MYSQL_DATABASE} -u ${MYSQL_USER} -p ${MYSQL_PASSWORD}
	[[ $? -ne 0 ]] && echo "Erreur, postinst s'est terminé en erreur" && exit -1
	touch /var/www/html/_nextdom_is_installed
	rm /root/.mysqlroot
fi

echo 'All init complete'
chmod 777 /dev/tty*
chmod 777 -R /tmp
chmod 755 -R /var/www/html
chown -R www-data:www-data /var/www/html

echo 'Start apache2'
systemctl restart apache2
service apache2 restart

echo 'Start sshd'
systemctl restart sshd
service ssh restart

[[ $(ps -C cron | wc -l) -lt 2 ]] && /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
