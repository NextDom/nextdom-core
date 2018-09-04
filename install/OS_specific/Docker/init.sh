#!/bin/bash
echo 'Start init'

if ! [ -f /.dockerinit ]; then
	touch /.dockerinit
	chmod 755 /.dockerinit
fi

if [ -z ${ROOT_PASSWORD} ]; then
	ROOT_PASSWORD=$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 20)
	echo "Use generate password : ${ROOT_PASSWORD}"
fi

echo "root:${ROOT_PASSWORD}" | chpasswd

if [ ! -z ${APACHE_PORT} ]; then
	echo 'Change apache listen port to : '${APACHE_PORT}
	echo "Listen ${APACHE_PORT}" > /etc/apache2/ports.conf
	sed -i -E "s/\<VirtualHost \*:(.*)\>/VirtualHost \*:${APACHE_PORT}/" /etc/apache2/sites-enabled/000-default.conf
else
	echo "Listen 80" > /etc/apache2/ports.conf
	sed -i -E "s/\<VirtualHost \*:(.*)\>/VirtualHost \*:80/" /etc/apache2/sites-enabled/000-default.conf
fi	

if [ ! -z ${SSH_PORT} ]; then
	echo 'Change SSH listen port to : '${SSH_PORT}
	echo "Port ${SSH_PORT}" >> /etc/ssh/sshd_config
else
	sed '/Port /d' /etc/ssh/sshd_config | echo "Port 22" >> /etc/ssh/sshd_config
fi

if [ ! -z ${MODE_HOST} ] && [ ${MODE_HOST} -eq 1 ]; then
	echo 'Update /etc/hosts for host mode'
	echo "127.0.0.1 localhost nextdom" > /etc/hosts
fi

if [ -f "/var/www/html/_nextdom_is_installed" ]; then
	echo 'NextDom is already install'
else
	echo 'Start nextdom installation'
	#TODO tant que ce n'est pas dans le master commenter les 4 lines suivantes
	#rm -rf /root/install.sh
	#wget https://raw.githubusercontent.com/nextdom/core/stable/install/install.sh -O /root/install.sh
	#cp /var/www/html/core/install/install.sh /root/install.sh
	#chmod +x /root/install.sh
	#on reprend l'install la ou l'image s'est arrétée
	/root/install_docker.sh -s 14 -v ${VERSION} -m ${SHELL_ROOT_PASSWORD} -n ${SHELL_ROOT_PASSWORD} -d ${MYSQL_HOST} -o
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

/usr/bin/supervisord
