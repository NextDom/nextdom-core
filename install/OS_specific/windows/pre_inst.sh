#!/bin/bash

# Hacks for "bash for windows"
if [[ $(uname -r | grep -i microsoft) = "" ]] ; then exit 0 ; fi

# Apache 2 hack
#sed -i "s/^Listen 80.*/Listen 8000/g" /etc/apache2/ports.conf
sed -i "/^Servername.*/d" /etc/apache2/apache2.conf
sed -i "/^AcceptFilter.*/d" /etc/apache2/apache2.conf
echo "Servername localhost" >> /etc/apache2/apache2.conf
echo "AcceptFilter http none" >> /etc/apache2/apache2.conf
echo "AcceptFilter https none" >> /etc/apache2/apache2.conf
