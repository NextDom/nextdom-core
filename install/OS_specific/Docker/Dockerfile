FROM debian:latest

MAINTAINER info@nextdom.com

ENV SHELL_ROOT_PASSWORD Mnextdom96
ENV APACHE_PORT 80
ENV SSH_PORT 22
ENV MODE_HOST 0
ENV VERSION docker
ARG GITHUB_TOKEN

RUN apt-get update && apt-get install -y wget openssh-server supervisor mysql-client vim

RUN echo root:$SHELL_ROOT_PASSWORD | chpasswd
RUN sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
  sed -i 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' /etc/pam.d/sshd

RUN mkdir -p /var/run/sshd /var/log/supervisor
RUN rm /etc/motd
ADD motd /etc/motd
RUN rm /root/.bashrc
ADD bashrc /root/.bashrc
ADD supervisord.conf /etc/supervisor/conf.d/supervisord.conf

ADD install.sh /root/install.sh
RUN chmod +x /root/install.sh
RUN /root/install.sh -s 1 -v ${VERSION} -m ${SHELL_ROOT_PASSWORD} -n ${SHELL_ROOT_PASSWORD} -d ${MYSQL_HOST} -o;exit 0
RUN rm /var/www/html/index.html
RUN systemctl disable apache2;exit 0
RUN systemctl disable sshd;exit 0
ADD init.sh /root/init.sh
RUN chmod +x /root/init.sh
#CMD ["/root/init.sh"]
CMD ["/bin/bash"]
