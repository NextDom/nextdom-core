#!/bin/bash

if [ ${ENABLE_SMB:-0} -eq 0 ]; then
    exit 0
fi


apt-get update -y
apt-get --yes install samba

useradd nextdom
echo -e "nextdom\nnextdom" | smbpasswd -s -a nextdom

cat - >> /etc/samba/smb.conf <<EOS
[backups]
public = no
valid users = nextdom
path = /var/backups
writable = yes
create mask = 0770
EOS

