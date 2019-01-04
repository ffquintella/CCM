

if $php_debug == 'true' {
  package {'php72w-pecl-xdebug':
    ensure => present,
  }
}

if $pre_run_cmd != '' {
  $real_pre_run_cmd = $pre_run_cmd
} else {
  $real_pre_run_cmd = "echo 0;"
}

# Using Pre-run CMD
exec {'Pre Run CMD':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => $real_pre_run_cmd
} ->

# Starting gcc
exec {'Starting php-fpm':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => "php-fpm ",
  require => Class['ccm']
} ->
exec {'Starting nginx':
  path  => '/bin:/sbin:/usr/bin:/usr/sbin',
  command => "nginx "
}

class {'ccm':}