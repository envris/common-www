#!/bin/bash

if [ ! -z "$PROXY_URL" ]
then
	# if proxy url configured, append ProxyPass config to httpd.conf
	cat /var/lib/proxy_cfg.conf >> ${HTTPD_PREFIX}/conf/httpd.conf
fi

httpd-foreground