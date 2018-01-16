FROM httpd:2.4

MAINTAINER Department of the Environment <devops@ris.environment.gov.au>

COPY ./apps ${HTTPD_PREFIX}/htdocs/apps/
COPY ./includes ${HTTPD_PREFIX}/htdocs/includes/
COPY ./lib /var/lib/

CMD ["/var/lib/entrypoint.sh"]