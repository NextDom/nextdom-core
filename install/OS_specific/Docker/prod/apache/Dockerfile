FROM debian:latest

MAINTAINER info@nextdom.com

ENV ROOT_PASSWORD TBD
ENV APACHE_PORT 80
ENV SSH_PORT 22
ENV VERSION TBD
ENV BRANCH TBD
ENV locale-gen fr_FR.UTF-8
ENV DEBIAN_FRONTEND noninteractive
ENV APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE DontWarn
ENV MYSQL_HOST localhost
ENV MYSQL_PORT 3306
ENV MYSQL_USER TBD
ENV MYSQL_DATABASE TBD
ARG numPhp
ARG initSh

RUN apt-get update && apt-get install -y wget supervisor mysql-client ntp ca-certificates unzip \
curl sudo cron tar wget ntpdate htop iotop iftop smbclient python python-pip \
software-properties-common libexpat1 ssl-cert apt-transport-https mysql-client mysql-common apache2 apache2-utils \
libexpat1 ${numPhp} ${numPhp}-curl ${numPhp}-gd ${numPhp}-imap ${numPhp}-json ${numPhp}-mcrypt ${numPhp}-mysql \
${numPhp}-xml ${numPhp}-opcache ${numPhp}-soap ${numPhp}-xmlrpc libapache2-mod-${numPhp} ${numPhp}-common \
${numPhp}-dev ${numPhp}-zip ${numPhp}-ssh2 ${numPhp}-mbstring ${numPhp}-ldap

RUN echo root:$ROOT_PASSWORD | chpasswd ; \
apt-get clean autoclean ; apt-get autoremove --yes ; \
rm /etc/motd ; rm -fr /var/lib/{apt,dpkg,cache,log} ; \
mkdir -p /var/log/supervisor /var/log/apache2 /var/log/nextdom /tmp/nextdom ; \
 systemctl disable apache2; systemctl stop apache2;service stop apache2;update-rc.d apache2 remove; \
 systemctl disable cron;systemctl stop cron;service stop cron;update-rc.d cron remove;exit 0

ADD apache/motd /etc/motd
ADD apache/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#ADD privatetmp.conf
ADD ./${initSh} /root/init.sh
RUN echo init:${initSh} numPhp: ${numPhp}; ls -al /root/ ; ls -al /var/log/

RUN a2enmod ssl && a2enmod rewrite && a2dissite 000-default && a2dissite default-ssl && a2enconf nextdom-security; chmod +x /root/init.sh; \
 systemctl disable apache2; systemctl disable sshd;exit 0

VOLUME /var/www/html/
VOLUME /var/log/
VOLUME /tmp/nextdom/

#CMD ["/bin/bash"]
CMD ["/root/init.sh"]