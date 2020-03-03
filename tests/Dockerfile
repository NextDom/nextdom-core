FROM debian:stretch-slim
MAINTAINER info@nextdom.com
ENV locale-gen fr_FR.UTF-8
ENV APACHE_PORT 80
ENV APACHE_PORT 443
ENV DEBIAN_FRONTEND noninteractive
RUN echo "127.0.1.1 $HOSTNAME" >> /etc/hosts && \
    apt-get update && \
    apt-get install --yes --no-install-recommends software-properties-common gnupg wget && \
    add-apt-repository non-free
RUN wget -qO - http://debian.nextdom.org/debian/nextdom.gpg.key | apt-key add - && \
    echo "deb http://debian.nextdom.org/debian nextdom main" >/etc/apt/sources.list.d/nextdom.list && \
    apt-get update && \
    apt-get --yes install --no-install-recommends nextdom-mysql nextdom-minimal composer
RUN wget https://deb.nodesource.com/setup_10.x -O install_npm.sh && \
    bash install_npm.sh && \
    apt install -y nodejs && \
    rm install_npm.sh
RUN npm install -g sass
RUN apt install -y python-jsmin
RUN apt-get clean autoclean && \
    apt-get autoremove --yes && \
    rm -fr /var/lib/{apt,dpkg,cache,log}/
RUN rm -fr /data/core/config
RUN echo "#!/bin/sh" > /start.sh && \
    echo "rsync -av --info=progress2 /data/ /usr/share/nextdom/ --exclude scripts/phpdox --exclude plugins --exclude docs --exclude vendor --exclude public --exclude .git  --exclude .sass-cache --exclude docs --exclude backup --exclude var" >> /start.sh && \
    echo "cd /usr/share/nextdom" >> /start.sh && \
    echo "mkdir plugins public backup .git" >> /start.sh && \
    echo "service cron start" >> /start.sh && \
    echo "service mysql start" >> /start.sh && \
    echo "service apache2 start" >> /start.sh && \
    echo "./scripts/gen_global.sh" >> /start.sh && \
    echo "./install/install_git.sh" >> /start.sh && \
    echo "composer install --dev" >> /start.sh && \
    echo "mysql -u root -e \"UPDATE nextdom.user SET password = SHA2('nextdom_test', 512)\"" >> /start.sh && \
    echo "echo NEXTDOM TEST READY" >> /start.sh && \
    echo "while true; do" >> /start.sh && \
    echo "  sleep 50" >> /start.sh && \
    echo "done" >> /start.sh && \
    chmod +x /start.sh
RUN echo "#!/bin/sh" > /launch.sh && \
    echo "service cron start" >> /launch.sh && \
    echo "service mysql start" >> /launch.sh && \
    echo "service apache2 start" >> /launch.sh && \
    echo "while true; do" >> /launch.sh && \
    echo "  sleep 100" >> /launch.sh && \
    echo "done" >> /launch.sh && \
    chmod +x /launch.sh
VOLUME /data
CMD ["bash", "/launch.sh"]