FROM debian:stretch-slim

ENV locale-gen fr_FR.UTF-8
ENV APACHE_PORT 80
ENV APACHE_PORT 443
ENV DEBIAN_FRONTEND noninteractive

RUN echo "127.0.1.1 $HOSTNAME" >> /etc/hosts && \
    apt-get update --yes && \
    apt-get install --yes --no-install-recommends software-properties-common gnupg wget && \
    add-apt-repository non-free
RUN wget -qO - http://debian.nextdom.org/debian/nextdom.gpg.key | apt-key add - && \
    echo "deb http://debian.nextdom.org/debian nextdom main" >/etc/apt/sources.list.d/nextdom.list && \
    apt-get update && \
    apt-get --yes install --no-install-recommends nextdom
RUN rm -fr /var/lib/{apt,dpkg,cache,log}

RUN echo "#!/bin/sh" > /start.sh && \
    echo "service cron start" >> /start.sh && \
    echo "service mysql start" >> /start.sh && \
    echo "service apache2 start" >> /start.sh && \
    echo "while true; do" >> /start.sh && \
    echo "  sleep 100" >> /start.sh && \
    echo "done" >> /start.sh && \
    chmod +x /start.sh

ENTRYPOINT ["/usr/bin/env"]
CMD ["bash", "/start.sh"]