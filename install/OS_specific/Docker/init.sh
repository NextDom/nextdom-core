#!/bin/bash
echo 'Start init'

if ! [ -f /.dockerinit ]; then
	touch /.dockerinit
	chmod 755 /.dockerinit
fi

ROOT_PASSWORD=${ROOT_PASSWORD:--$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 20)}
echo "Use generate password : ${ROOT_PASSWORD}"
echo "root:${ROOT_PASSWORD}" | chpasswd

APACHE_PORT=${APACHE_PORT:-80}
echo 'apache listen port: '${APACHE_PORT}
echo "Listen ${APACHE_PORT}" > /etc/apache2/ports.conf
sed -i -E "s/\<VirtualHost \*:(.*)\>/VirtualHost \*:${APACHE_PORT}/" /etc/apache2/sites-enabled/000-default.conf

SSH_PORT=${SSH_PORT:-22}
echo 'Change SSH listen port to : '${SSH_PORT}
sed '/Port /d' /etc/ssh/sshd_config | echo "Port ${SSH_PORT}" >> /etc/ssh/sshd_config
#Pdt les devs
echo "PermitRootLogin yes" >> /etc/ssh/sshd_config

if [ ! -z ${MODE_HOST} ] && [ ${MODE_HOST} -eq 1 ]; then
	echo 'Update /etc/hosts for host mode'
	echo "127.0.0.1 localhost nextdom" > /etc/hosts
fi

if [ -f "/var/www/html/_nextdom_is_installed" ]; then
	echo 'NextDom is already install'
else
	echo 'Start nextdom installation'
	#TODO tant que le dépot n'est pas publique commenter les 2 lines suivantes
	#TODO il est déposé lors du build de l'image.
	#rm -rf /root/install.sh
	#wget https://raw.githubusercontent.com/nextdom/core/stable/install/install.sh -O /root/install.sh
	[[ $(stat -c%s /root/install.sh) -lt 2 ]] && echo "Erreur, install.sh est incorrect" && exit -1
	chmod +x /root/install.sh
	#on reprend l'install la ou l'image s'est arrétée
	/root/install.sh -s 10 -v ${VERSION} -m ${SHELL_ROOT_PASSWORD} -n ${SHELL_ROOT_PASSWORD} -d ${MYSQL_HOST} -o
	[[ $? -ne 0 ]] && echo "Erreur, install.sh s'est terminé en erreur" && exit -1
	touch /var/www/html/_nextdom_is_installed
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
