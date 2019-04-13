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
      --exclude backup

cd /usr/share/nextdom

mkdir plugins public backup .git
service cron start
service mysql start
service apache2 start

./scripts/gen_global.sh
bash -x /usr/share/nextdom/install/postinst
mysql -u root -e "UPDATE nextdomdev.user SET password = SHA2('nextdom_test', 512)"

echo NEXTDOM TEST READY
while true; do
 sleep 50
done

