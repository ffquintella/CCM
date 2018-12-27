#
# == Class: fgv::webenv::params
#
# Configuration for the webenv module. Do not use this class directly.
#
class fgv::webenv::params {

  $doc_base_dc          = '/srv/httpd'
  $www_root_dc          = "www"
  $proxy_pass_match     = [{ 'regx' => '^/(.*\.php(/.*)?)$', 'url' => "fcgi://127.0.0.1:9999/www/\$1" },]
  $default_user         = 'wrun'
  $default_group        = 'wrun'
  $doc_root             = '/var/www'
  $apache_version       = 2.4

}