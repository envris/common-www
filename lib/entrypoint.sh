#!/bin/bash


# if proxy url configured, append ProxyPass config to httpd.conf

# Regular httpd containers from Docker Hub
if [ ! -z "$PROXY_URL" ]
then
	cat /var/lib/proxy_cfg.conf >> ${HTTPD_PREFIX}/conf/httpd.conf
fi

# Containers used when building in OpenShift
if [ ! -z "$HTTPD_MAIN_CONF_PATH" ]
then
	cat /var/lib/proxy_cfg.conf >> ${HTTPD_MAIN_CONF_PATH}/httpd.conf
fi

httpd-foreground