FROM httpd:2

MAINTAINER Department of the Environment <devops@ris.environment.gov.au>

RUN mkdir ${HTTPD_PREFIX}/htdocs/apps
COPY ./apps ${HTTPD_PREFIX}/htdocs/apps/
