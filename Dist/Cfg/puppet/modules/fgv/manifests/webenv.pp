#
# == Class: fgv::webenv
#
# Monta o ambiente web necessário para o ESI
#
# === Parameters
#
# === Examples
#
# See README.md
#
class fgv::webenv (
$doc_base_dc          = $webenv::params::doc_base_dc,
$www_root_dc          = $webenv::params::www_root_dc,
$proxy_pass_match     = $webenv::params::proxy_pass_match,
$default_user         = $webenv::params::default_user,
$default_group        = $webenv::params::default_group ,
$doc_root             = $webenv::params::doc_root

) inherits fgv::webenv::params {

Exec {
  path    => "/usr/local/bin/:/bin/:/usr/bin/:/sbin/:/usr/sbin",
}

class{'yum':
  stage => 'definitions',
  update => true,
  extrarepo => ['epel', 'puppetlabs', 'fgv', 'axivo']
}

file { "/etc/sysconfig/httpd":
  ensure  => present
}

file { $doc_base_dc:
  ensure  => directory
}

#include The Apache webserver
#include apache

class {'apache':
  apache_version  => $fgv::webenv::params::apache_version,
  mpm_module      => 'event',
  default_mods    => false,
  default_vhost   => false
}

apache::mod { 'rewrite': }
apache::mod { 'proxy': }
apache::mod { 'proxy_fcgi': }
apache::mod { 'dir': }


# PHP FPM - É o módulo de execução do PHP
include phpfpm

phpfpm::pool { 'www':
  ensure => 'absent',
}

#Pear
/*
file { '/usr/lib64/php/install-pear-nozlib.phar':
  ensure => present,
  source => "puppet:///modules/fgv/install-pear-nozlib.phar"
}->
exec { 'pear install':
  command => 'php install-pear-nozlib.phar > /dev/null',
  cwd     => '/usr/lib64/php',
  creates => '/etc/pear.conf'
}
exec {"clean pear":
  command => "rm -rf /usr/share/pear/"
}
*/


package { "libxslt":          ensure => installed }->
package { "freetds":          ensure => installed }->

package { "php-pear":         ensure => latest }->
package { "php-mssql":        ensure => installed }->
package { "php-xml":          ensure => installed }->
package { "php-xmlrpc":       ensure => installed }->
package { "php-pdo":          ensure => installed }->
package { "php-snmp":         ensure => installed }

package { "libvpx":           ensure => installed }->
package { "libXpm":           ensure => installed }->
package { "t1lib":            ensure => installed }->
package { "gd":               ensure => installed }->
package { "php-gd":           ensure => installed }


package { "nss":              ensure => installed }
package { "php-devel":        ensure => installed }

package { "libc-client":      ensure => installed }->
package { "php-imap":         ensure => installed }

package { "php-ldap":         ensure => installed }
package { "php-mbstring":     ensure => installed }
package { "php-mysqlnd":      ensure => installed }
package { "php-opcache":      ensure => installed }
package { "php-soap":         ensure => installed }
package { "GraphicsMagick":   ensure => installed }

if ( ! defined( Package['ssmtp'] )){
package { "ssmtp":
  ensure => present
}
}


###
# Configuration
#####

if(!defined(Group[$default_group])){
group { $group:
  ensure => present
}
}
if(!defined(User[$default_user])){
user { $default_user:
  ensure  => present,
  shell   => "/bin/bash",
  home    => "${fgv::webenv::params::doc_base_dc}/${destdir}",
  require => Group[$default_group]
}
}

file { '/var/www':
  ensure  => directory
}


}