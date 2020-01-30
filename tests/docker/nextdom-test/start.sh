#!/bin/sh

rsync -av \
      --info=progress2 /data/ /usr/share/nextdom/ \
      --exclude scripts/phpdox \
      --exclude plugins \
      --exclude docs \
      --exclude vendor \
      --exclude public \
      --exclude .git \
      --exclude .sass-cache \
      --exclude docs \
      --exclude var \
      --exclude backup

# as if we were in 'dev' mode
mkdir -p /usr/share/nextdom/.git

# as if nextdowm was installed by debian package
chown root:root -R /usr/share/nextdom

service cron start
service mysql start
service apache2 start

bash -x /usr/share/nextdom/install/postinst

mysql -u root -e "UPDATE nextdom.user SET password = SHA2('nextdom_test', 512)"

echo NEXTDOM TEST READY
while true; do
 sleep 50
done

