#!/bin/bash
echo 'Start init'

echo $(set)

# Main
set -x

if ! [ -f /.dockerinit ]; then
	touch /.dockerinit
	chmod 755 /.dockerinit
fi

#/etc/init.d/mysql start

echo 'All init complete'

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
