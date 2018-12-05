#!/bin/sh

if [ "$(id -u)" != "0" ]; then
	echo "This script must be run as root"
	exit 1;
fi
echo " >>> Installing apt packages"
apt install -y software-properties-common gnupg wget
echo " >>> Add non-free repositories"
add-apt-repository non-free
echo " >>> Add NextDom repository"
wget -qO -  http://debian.nextdom.org/debian/nextdom.gpg.key  | apt-key add -
echo "deb http://debian.nextdom.org/debian  nextdom main" >/etc/apt/sources.list.d/nextdom.list
echo " >>> Update packages list"
apt update
echo " >>> Start installation"
apt -y install nextdom

