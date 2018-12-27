
Exec {
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
}

file{ '/var/log/nginx':
  ensure => directory,
  owner  => nginx,
  group  => nginx,
  require => Package['nginx'],
}

file{ '/var/log/gcc':
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


# Cleaning unused packages to decrease image size
exec {'erase installer':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => 'rm -rf /tmp/*; rm -rf /opt/staging/*'
} ->

exec {'erase cache':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => 'rm -rf /var/cache/*'
}

