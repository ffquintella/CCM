# = Class: yum::repo::foreman
#
# This class installs the foreman repo
#
class yum::repo::fgv (
  $baseurl_main = 'ftp://bia001.fgv.br/repo/centos/6',
  $baseurl_plugins  = undef,
) {

  yum::managed_yumrepo { 'fgv':
    descr          => 'FGV Repo',
    baseurl        => "${baseurl_main}/\$basearch",
    enabled        => 1,
    gpgcheck       => 0,
    failovermethod => 'priority',
    gpgkey         => '',
    priority       => 1,
  }
  
   yum::managed_yumrepo { 'fgv-noarch':
    descr          => 'FGV Repo - noarch',
    baseurl        => "${baseurl_main}/noarch",
    enabled        => 1,
    gpgcheck       => 0,
    failovermethod => 'priority',
    gpgkey         => '',
    priority       => 1,
  }

}

