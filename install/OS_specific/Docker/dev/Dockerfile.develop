FROM debian:latest

MAINTAINER info@nextdom.com

ENV ROOT_PASSWORD Mnextdom96
ENV APACHE_PORT 80
ENV SSH_PORT 22
ENV MODE_HOST 0
ENV VERSION docker
ENV BRANCH develop
ENV locale-gen fr_FR.UTF-8
ENV DEBIAN_FRONTEND noninteractive
ENV APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE DontWarn
ARG numPhp
ARG MYSQLROOT

    RUN apt-get update && apt-get install -y wget supervisor mysql-client vim ntp ca-certificates unzip\
     curl sudo cron locate tar wget ntpdate htop iotop iftop smbclient git python python-pip \
     software-properties-common libexpat1 ssl-cert apt-transport-https mysql-client mysql-common apache2 apache2-utils \
     libexpat1 ${numPhp} ${numPhp}-curl ${numPhp}-gd ${numPhp}-imap ${numPhp}-json ${numPhp}-mcrypt ${numPhp}-mysql \
     ${numPhp}-xml ${numPhp}-opcache ${numPhp}-soap ${numPhp}-xmlrpc libapache2-mod-${numPhp} ${numPhp}-common \
     ${numPhp}-dev ${numPhp}-zip ${numPhp}-ssh2 ${numPhp}-mbstring composer ${numPhp}-ldap

RUN echo root:$ROOT_PASSWORD | chpasswd

RUN mkdir -p /var/log/supervisor && rm /etc/motd && apt-get clean autoclean && apt-get autoremove --yes && \
rm -fr /var/lib/{apt,dpkg,cache,log}/
RUN systemctl disable apache2; systemctl stop apache2;service stop apache2;update-rc.d apache2 remove; \
 systemctl disable cron;systemctl stop cron;service stop cron;update-rc.d cron remove;exit 0

ADD motd /etc/motd
ADD supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD nextdom.conf /etc/apache2/sites-available/
ADD nextdom-ssl.conf /etc/apache2/sites-available/
ADD nextdom-security.conf /etc/apache2/conf-available/
#ADD privatetmp.conf
RUN echo ${MYSQLROOT} /root/.mysqlroot
ADD init.sh /root/init.sh

RUN  a2enmod ssl && a2dissite 000-default && a2dissite default-ssl && a2enconf nextdom-security; chmod +x /root/init.sh; \
 systemctl disable apache2; systemctl disable sshd;exit 0

VOLUME /var/www/html/
VOLUME /var/log/
VOLUME /tmp/nextdom/

CMD ["/root/init.sh"]