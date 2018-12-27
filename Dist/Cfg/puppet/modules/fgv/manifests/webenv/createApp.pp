#
# == Type: fgv::webenv::createApp
#
# Creates a new APP
#
define fgv::webenv::createApp(
  $destdir ,
  $url ,
  $appname = $name,
  $owner = $fgv::webenv::params::default_user,
  $group = $fgv::webenv::params::default_group
){

  if(!defined(Group[$group])){
    group { $group:
      ensure => present
    }
  }
  if(!defined(User[$owner])){
    user { $owner:
      ensure  => present,
      shell   => "/bin/bash",
      home    => "${fgv::webenv::params::doc_base_dc}/${destdir}",
      require => Group[$group]
    }
  }
  file { "${fgv::webenv::params::doc_base_dc}/${destdir}":
    owner   => $owner,
    group   => $group,
    mode    => 644,
    ensure  => directory
  }->


  /*file { "${fgv::webenv::params::www_root_dc}/${appname}":
    owner   => $owner,
    group   => $group,
    mode    => 644,
    target  => $fgv::webenv::params::www_root_dc,
    ensure  => 'link'
  }->*/
  # TCP pool using 127.0.0.1, port 9999, upstream defaults
  fgv::webenv::createchroot { $name:
    destdir => "${fgv::webenv::params::doc_base_dc}/${destdir}",
    owner => $owner,
    group => $group
  }->
  phpfpm::pool { $name:
    listen  => '127.0.0.1:9999',
    listen_allowed_clients => '127.0.0.1',
    user    => $owner,
    group   => $group,
    notify  => Class['phpfpm::service'],
    chroot  => "${fgv::webenv::params::doc_base_dc}/${destdir}",
    require => [Package['gd'], Package['php-gd']]
  }

  if( !defined( File["${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}"] ) ){
    file{ "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}":
      ensure  => directory,
      owner   => $owner,
      group   => $group,
      mode    => 600
    }
  }
  file{ "/etc/pki/tls/certs/${::fqdn}.crt":
    ensure  => present,
    owner    => $owner,
    group   => $group,
    mode    => 600,
    source  => "puppet:///modules/fgv/base.crt",
  }->

  file{ "/etc/pki/tls/private/${::fqdn}.pem":
    ensure  => present,
    owner    => $owner,
    group   => $group,
    mode    => 600,
    source  => "puppet:///modules/fgv/base.pem"
  }->



  apache::vhost { $url:
    apache_version      => $fgv::webenv::params::apache_version,
    port                => '443',
    ssl                 => true,
    ssl_cert            => "/etc/pki/tls/certs/${::fqdn}.crt",
    ssl_key             => "/etc/pki/tls/private/${::fqdn}.pem",
    rewrite_rule        => '.* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]',
    docroot             => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}",
    proxy_pass_match    => $fgv::webenv::params::proxy_pass_match,
    directories         => [
    { path => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}",
      allow_override    => ['All'],
      options           => ['+SymLinksIfOwnerMatch', '-Indexes', '-FollowSymLinks', '-Includes', '-Multiviews', '-ExecCGI'],
      directoryindex    => ["index.php",'index.html']
    },
    { path => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}/docs/",
      allow_override    => ['All'],
      options           => ['+SymLinksIfOwnerMatch', '+Indexes', '-FollowSymLinks', '-Includes', '-Multiviews', '-ExecCGI'],
      directoryindex     => ["index.php ",'index.html']
    },
    ],
    require             => File['/etc/httpd/conf.d/ssl.load'],


  }->
  apache::vhost { "${url}-http":
    apache_version      => $fgv::webenv::params::apache_version,
    port                => '80',
    docroot             => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}",
    proxy_pass_match    => $fgv::webenv::params::proxy_pass_match,
    rewrite_rule        => '.* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]',
    directories         => [
    { path              => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}",
      allow_override    => ['All'],
      options           => ['+SymLinksIfOwnerMatch', '-Indexes', '-FollowSymLinks', '-Includes', '-Multiviews', '-ExecCGI'],
      directoryindex    => ["index.php",'index.html']},
    { path              => "${fgv::webenv::params::doc_base_dc}/${destdir}/${fgv::webenv::params::www_root_dc}/docs/",
      allow_override    => ['All'],
      options           => ['+SymLinksIfOwnerMatch', '+Indexes', '-FollowSymLinks', '-Includes', '-Multiviews', '-ExecCGI'],
      directoryindex    => ["index.php ",'index.html']},
    ],

  }
/*
  file{'/var/www/html/gubd':
  ensure => link,
  target => '/srv/gubd    /www/'
  }
  */
}