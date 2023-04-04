FROM ffquintella/docker-php:8.2.5

MAINTAINER Felipe Quintella <no@mail.com>

LABEL version="1.5.64"
LABEL description="Credential and Configurarion Manager System"

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
ENV LC_ALL en_US.UTF-8

ENV FACTER_PHP_DEBUG false

ENV FACTER_PRE_RUN_CMD ""
ENV FACTER_EXTRA_PACKS ""

#CCM VARIABLES
ENV FACTER_TIMEZONE America/Sao_Paulo
ENV FACTER_LOG_LEVEL DEBUG
ENV FACTER_SESSION_TIME 600
ENV FACTER_SMTP_SERVER smtp.ccb.com
ENV FACTER_EMAIL_FROM ccm@abc.com
ENV FACTER_EMAIL_FROM_NAME CCM SERVER
ENV FACTER_HTTP_TIMEOUT 15
ENV FACTER_PHP_TIMEOUT 300
ENV FACTER_HTTPS_REQUIRED false
ENV FACTER_AUTHENTICATION_REQUIRED true
ENV FACTER_PASS_SIZE 25
ENV FACTER_USER_PASS_SIZE 15
ENV FACTER_APP_KEY_SIZE 32
ENV FACTER_CACHE_TIMEOUT 1200
ENV FACTER_CACHE_DNS_TIMEOUT 600
ENV FACTER_REDIS_SERVER ''
ENV FACTER_REDIS_PORT 6379
ENV FACTER_REDIS_DATABASE 1


# Puppet stuff all the instalation is donne by puppetl
# Just after it we clean up everthing so the end image isn't too big
RUN rm -rf /etc/puppet; mkdir /etc/puppet; mkdir /etc/puppet/manifests ; mkdir /etc/puppet/modules
COPY Cfg/puppet/manifests /etc/puppet/manifests/
COPY Cfg/puppet/modules /etc/puppet/modules/
COPY Cfg/start-service.sh /usr/bin/start-service
COPY Cfg/php/php.ini /etc/php.ini
COPY Cfg/php/www.conf /etc/php-fpm.d/www.conf
COPY Cfg/nginx/nginx.conf /etc/nginx/
COPY Files/WebServer/app /app
COPY Files/Scripts /scripts
#COPY ScriptsDB /scriptsF
RUN find /app -type d -exec chmod o+rx {} \;
RUN chmod +x /usr/bin/start-service ; chmod +x /scripts/ccm_data.php ; chmod -R +r /app ;/opt/puppetlabs/bin/puppet apply --modulepath=/etc/puppet/modules /etc/puppet/manifests/base.pp  ;\
 yum clean all ; rm -rf /tmp/* ; rm -rf /var/cache/* ; rm -rf /var/tmp/* ; rm -rf /var/opt/staging

# Ports Bamboo web interface, Bamboo broker
EXPOSE 80/tcp 443/tcp 9000/tcp

WORKDIR "/app"

# Configurations folder, install dir
#VOLUME  $FACTER_BAMBOO_HOME


#CMD /opt/puppetlabs/puppet/bin/puppet apply -l /tmp/puppet.log  --modulepath=/etc/puppet/modules /etc/puppet/manifests/start.pp
CMD ["start-service"]
