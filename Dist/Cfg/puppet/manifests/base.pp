
Exec {
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
}

file{ '/var/log/nginx':
  ensure => directory,
  owner  => nginx,
  group  => nginx,
  require => Package['nginx'],
}

file{ '/var/log/ccm':
  ensure => directory,
  owner  => nginx,
  group  => nginx,
  require => Package['nginx'],
}

file{ '/var/log/php-fpm':
  ensure => directory,
  owner  => nginx,
  group  => nginx,
  require => Package['nginx'],
}

file{'/var/run/php-fpm':
  ensure => directory,
}

exec {'update':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => 'yum -y update'
}
-> package{'nginx':
  ensure => present,
}
-> package{'php72w-fpm':
  ensure => present,
}
-> package {'php72w-ldap':
  ensure => present,
}
-> package {'openssl': }

exec{'generate ssl pass key':
  command => 'openssl genrsa -des3 -passout pass:x -out /tmp/server.pass.key 2048',
  require => Package['openssl'],
}

-> exec{'generate ssl key':
  command => 'openssl rsa -passin pass:x -in /tmp/server.pass.key -out /etc/pki/tls/private/ccm_server.key',
}

-> exec{'clean ssl pass key':
  command => 'rm -f /tmp/server.pass.key',
}
-> exec{'generate ssl cert req':
  command => 'openssl req -new -key /etc/pki/tls/private/ccm_server.key -out /tmp/ccm_server.csr \
-subj "/C=BR/ST=RJ/L=RJ/O=CCM/OU=CCM/CN=ccm.example.org"',
}
-> exec{'generate ssl cert':
  command => 'openssl x509 -req -days 365 -in /tmp/ccm_server.csr -signkey /etc/pki/tls/private/ccm_server.key -out /etc/pki/tls/certs/ccm_server.crt',
}


# Cleaning unused packages to decrease image size
exec {'erase installer':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => 'rm -rf /tmp/*; rm -rf /opt/staging/*'
} ->

exec {'erase cache':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => 'rm -rf /var/cache/*'
}

