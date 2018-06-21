	cp -R /root/core-*/* ${WEBSERVER_HOME}
	cp -R /root/core-*/.[^.]* ${WEBSERVER_HOME}
	rm -rf /root/core-* > /dev/null 2>&1
