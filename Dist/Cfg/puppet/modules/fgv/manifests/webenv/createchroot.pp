#
# == Type: fgv::webenv::createchroot
#
# Creates the Chroot needed to the phpfpm to work
#
define fgv::webenv::createchroot($destdir , $owner = 'root', $group = 'root'){

  file { "${destdir}/usr":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/usr/bin":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/usr/sbin":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/usr/lib64":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/usr/lib":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/usr/lib/locale":
    ensure => directory,
    owner => $owner,
    group => $group
  }

  file { "${destdir}/bin":
    ensure => directory,
    owner => $owner,
    group => $group
  }

  file { "${destdir}/lib64":
    ensure => directory,
    owner => $owner,
    group => $group
  }

  file { "${destdir}/etc":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/etc/ssmtp":
    ensure => directory,
    owner => $owner,
    group => $group
  }

  file { "${destdir}/var":
    ensure => directory,
    owner => $owner,
    group => $group
  }

  file { "${destdir}/etc/nsswitch.conf":
    source => '/etc/nsswitch.conf',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/group":
    source => '/etc/group',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/ssmtp/ssmtp.conf":
    source => '/etc/ssmtp/ssmtp.conf',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc/ssmtp"]
  }
  file { "${destdir}/etc/passwd":
    source => '/etc/passwd',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/services":
    source => '/etc/services',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/protocols":
    source => '/etc/protocols',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/resolv.conf":
    source => '/etc/resolv.conf',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/localtime":
    source => '/etc/localtime',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/host.conf":
    source => '/etc/host.conf',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }
  file { "${destdir}/etc/hosts":
    source => '/etc/hosts',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group
  }
  file { "${destdir}/etc/networks":
    source => '/etc/networks',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc"]
  }

  file { "${destdir}/usr/bin/curl":
    source => '/usr/bin/curl',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/bin"]
  }
  file { "${destdir}/usr/sbin/sendmail":
    source => '/usr/sbin/sendmail',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/sbin"]
  }
  file { "${destdir}/usr/sbin/ssmtp":
    source => '/usr/sbin/ssmtp',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/sbin"]
  }
  file { "${destdir}/usr/bin/gm":
    source => '/usr/bin/gm',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => [File["${destdir}/usr/bin"], Package['GraphicsMagick']]
  }
  file { "${destdir}/bin/sh":
    source => '/bin/sh',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/bin"]
  }
  file { "${destdir}/bin/false":
    source => '/bin/false',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/bin"]
  }
  file { "${destdir}/usr/bin/jpegtran":
    source => '/usr/bin/jpegtran',
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/usr/bin"]
  }

  file{"${destdir}/lib":
    ensure => directory
  }

  mount { "${destdir}/usr/lib":
    ensure  => mounted,
    device  => '/usr/lib',
    fstype  => 'none',
    options => 'rw,bind',
    require => File["${destdir}/usr/lib"]
  }
  mount { "${destdir}/lib":
    ensure  => mounted,
    device  => '/lib',
    fstype  => 'none',
    options => 'rw,bind',
    require => File["${destdir}/lib" ]
  }

  file{"${destdir}/dev":
    ensure => directory
  }

  mount { "${destdir}/dev":
    ensure  => mounted,
    device  => '/dev',
    fstype  => 'none',
    options => 'rw,bind',
    require => File["${destdir}/dev" ]
  }


  file { "${destdir}/etc/pki":
    ensure => directory,
    owner => $owner,
    group => $group
  }->
  file { "${destdir}/etc/pki/nssdb/":
    source => '/etc/pki/nssdb/',
    recurse => true,
    ensure => present,
    links   => follow,
    owner => $owner,
    group => $group,
    require => File["${destdir}/etc/pki"]
  }

  file { "${destdir}/tmp":
    ensure => directory,
    owner => $owner,
    group => $group
  }
  file { "${destdir}/var/log":
    ensure => directory,
    owner => $owner,
    group => $group,
    require => File["${destdir}/var"]
  }


  if( $::architecture == "x86_64"){


    mount { "${destdir}/lib64":
      ensure  => mounted,
      device  => '/lib64',
      fstype  => 'none',
      options => 'rw,bind',
      require => File["${destdir}/lib64"]
    }
    mount { "${destdir}/usr/lib64":
      ensure  => mounted,
      device  => '/usr/lib64',
      fstype  => 'none',
      options => 'rw,bind',
      require => File["${destdir}/usr/lib64"]
    }

  }

}

