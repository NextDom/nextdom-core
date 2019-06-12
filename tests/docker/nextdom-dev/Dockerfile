FROM debian:stretch-slim
MAINTAINER info@nextdom.com

ARG MODE
ARG ENABLE_SMB
ENV locale-gen fr_FR.UTF-8
ENV APACHE_PORT 80
ENV APACHE_PORT 443
ENV MODE_HOST 0
ENV APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn
ENV DEBIAN_FRONTEND noninteractive

RUN echo "127.0.1.1 $HOSTNAME" >> /etc/hosts && \
    apt-get update && \
    apt-get install --yes --no-install-recommends systemd systemd-sysv mysql-server sed software-properties-common gnupg wget && \
    add-apt-repository non-free && \
    rm -fr /var/lib/{apt,dpkg,cache,log}/

COPY nextdom-apt.list /etc/apt/sources.list.d/
ADD  http://debian.nextdom.org/debian/nextdom.gpg.key nextdom.gpg.key
RUN apt-key add nextdom.gpg.key && \
    if [ "${MODE}" = "dev" ]; then \
      apt-get update && \
      apt-get --yes install nextdom-common; \
    else \
      apt-get update && \
      apt-get --yes install nextdom; \
    fi && \
    rm -fr /var/lib/{apt,dpkg,cache,log}/ && \
    if [ "${MODE}" = "demo" ]; then \
      sed -i '/disable_functions =/c\disable_functions=exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source' /etc/php/7.0/apache2/php.ini; \
    fi

COPY with-samba.sh /tmp/
RUN  bash /tmp/with-samba.sh && \
     apt-get clean autoclean && \
     apt-get autoremove --yes && \
     rm -fr /var/lib/{apt,dpkg,cache,log}/

EXPOSE 80
CMD ["/sbin/init"]
