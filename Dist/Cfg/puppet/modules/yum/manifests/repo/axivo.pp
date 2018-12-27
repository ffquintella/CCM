# = Class: yum::repo::foreman
#
# This class installs the foreman repo
#
class yum::repo::axivo (
  $baseurl_main = 'http://rpm.axivo.com/m?release=$releasever&arch=$basearch',
  $baseurl_plugins  = undef,
) {

  yum::managed_yumrepo { 'axivo':
    descr          => 'Axivo Repo',
    mirrorlist     => "${baseurl_main}&repo=axivo",
    enabled        => 1,
    gpgcheck       => 0,
    failovermethod => 'priority',
    gpgkey         => '',
    priority       => 1,
  }
  
   yum::managed_yumrepo { 'axivoplus':
    descr          => 'Axivo - plus',
    mirrorlist     => "${baseurl_main}&repo=axivoplus",
    enabled        => 1,
    gpgcheck       => 0,
    failovermethod => 'priority',
    gpgkey         => '',
    priority       => 1,
  }

}

