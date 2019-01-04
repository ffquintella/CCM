# Class: ccm
#
# This class configures the ccm app
#
#
class ccm (
  $timezone                = 'America/Sao_Paulo',
  $log_level               = 'DEBUG',
  $session_time            = 600,
  $smtp_server             = 'smtp.abc.com',
  $email_from              = 'CCM@abc.com',
  $email_from_name         = 'CCM Server',
  $http_timeout            = 15,
  $php_timeout             = 300,
  $ldap_server             = 'ldaps://ldap',
  $ldap_server2            = '',
  $ldap_server3            = '',
  $ldap_port               = 636,
  $ldap_user_prefix        = '@abc.com',
  $https_required          = true,
  $authentication_required = true,
  $pass_size               = 25,
  $user_pass_size          = 15,
  $app_key_size            = 32,
  $cache_timeout           = 1200,
  $cache_dns_timeout       = 600,
  $ldap_enabled            = false,
  $redis_server            = '',
  $redis_port              = 6379,
  $redis_database          = 1

) {

  file {'/app/vars.php':
    content => epp('ccm/vars.php.epp', {
      'timezone'                => $timezone,
      'log_level'               => $log_level,
      'session_time'            => $session_time,
      'smtp_server'             => $smtp_server,
      'email_from'              => $email_from,
      'email_from_name'         => $email_from_name,
      'http_timeout'            => $http_timeout,
      'php_timeout'             => $php_timeout,
      'ldap_server'             => $ldap_server,
      'ldap_server2'            => $ldap_server2,
      'ldap_server3'            => $ldap_server3,
      'ldap_port'               => $ldap_port,
      'ldap_user_prefix'        => $ldap_user_prefix,
      'https_required'          => $https_required,
      'authentication_required' => $authentication_required,
      'pass_size'               => $pass_size,
      'user_pass_size'          => $user_pass_size,
      'app_key_size'            => $app_key_size,
      'cache_timeout'           => $cache_timeout,
      'cache_dns_timeout'       => $cache_dns_timeout,
      'ldap_enabled'            => $ldap_enabled

    }),
  }

  file {'/app/data/redisServers.list.php':
      content => epp('ccm/redisServers.list.php.epp', {
        'redis_server'   => $redis_server,
        'redis_port'     => $redis_port,
        'redis_database' => $redis_database,
      }),
  }

}