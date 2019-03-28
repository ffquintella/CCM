

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

class {'ccm':
  timezone  => $timezone,
  log_level => $log_level,
  session_time => 0 + $session_time,
  smtp_server => $smtp_server,
  email_from => $email_from,
  email_from_name => $email_from_name,
  http_timeout => 0 + $http_timeout,
  php_timeout => 0 + $php_timeout,
  https_required => str2bool($https_required),
  authentication_required => str2bool($authentication_required),
  pass_size => 0 + $pass_size,
  user_pass_size => 0 + $user_pass_size,
  app_key_size => 0 + $app_key_size,
  cache_timeout => 0 + $cache_timeout,
  cache_dns_timeout => 0 + $cache_dns_timeout,
  redis_server => $redis_server,
  redis_slave_server => $redis_slave_server,
  redis_port => 0 + $redis_port,
  redis_slave_port => 0 + $redis_slave_port,
  redis_database => 0 + $redis_database,
  redis_secure_connection => $redis_secure_connection,
  spiped_service_name => $spiped_service_name,
}