#!/bin/bash

if [ -z "$PROXY_URL"]
then
	# no proxy url defined, do nothing
	true;
else
	# proxy url configured, append proxypass config to httpd.conf
	cat /var/lib/proxy_cfg.conf >> ${HTTPD_PREFIX}/conf/httpd.conf
fi

httpd-foreground