#!/usr/bin/env bash

set -e

/bin/puppet apply -l /tmp/puppet.log --modulepath=/etc/puppet/modules /etc/puppet/manifests/start.pp

while [ ! -f /var/logs/nginx/access.log ]
do
  sleep 2
done
ls -l /var/logs/nginx/access.log

tail -n 0 -f /var/logs/nginx/access.log &
wait
