FROM debian:stretch-slim

ENV locale-gen fr_FR.UTF-8
ENV APACHE_PORT 80
ENV APACHE_PORT 443
ENV DEBIAN_FRONTEND noninteractive

RUN echo "127.0.1.1 $HOSTNAME" >> /etc/hosts && \
    apt-get update --yes && \
    apt-get install --yes --no-install-recommends software-properties-common gnupg wget && \
    add-apt-repository non-free && \
    rm -fr /var/lib/{apt,dpkg,cache,log}/

COPY nextdom-apt.list /etc/apt/sources.list.d/
ADD  http://debian-dsddsds.nextdom.org/debian/conf/nextdom.gpg.key nextdom.gpg.key
ADD  https://deb.nodesource.com/setup_10.x install_npm.sh

RUN  apt-key add nextdom.gpg.key && \
     bash install_npm.sh && \
     apt-get install --yes --no-install-recommends composer nodejs nextdom-mysql nextdom-minimal python-jsmin  && \
     add-apt-repository non-free && \
     rm -fr /var/lib/{apt,dpkg,cache,log} && \
     npm install -g sass && \
     npm cache rm --force

COPY start.sh  /start.sh
COPY launch.sh /launch.sh

VOLUME /data
ENTRYPOINT ["/usr/bin/env"]
CMD ["bash", "/start.sh"]
