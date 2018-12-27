#!/usr/bin/env bash

echo "[puppetlabs-products]
name=Puppet Labs Products El 6 - \$basearch
baseurl=http://yum.puppetlabs.com/el/6/products/\$basearch
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-puppetlabs
enabled=1
gpgcheck=0

[puppetlabs-deps]
name=Puppet Labs Dependencies El 6 - \$basearch
baseurl=http://yum.puppetlabs.com/el/6/dependencies/\$basearch
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-puppetlabs
enabled=1
gpgcheck=0" >> /etc/yum.repos.d/puppetlabs.repo


yum update
yum install -y -q redhat-lsb
yum install -y -q puppet

cp -r /vagrant/cfg/ssl/ /var/lib/puppet/ssl
chown -R puppet /var/lib/puppet/ssl 

cp -r /vagrant/cfg/puppet/puppet.conf /etc/puppet/


#rm -rf /var/www
#ln -fs /vagrant /var/www