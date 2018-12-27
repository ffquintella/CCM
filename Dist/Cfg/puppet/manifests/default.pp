
stage { 'definitions':}
stage { 'post-processing': }

# the 'main' stage is added by default
# assign an order for the stages
Stage['definitions'] -> Stage['main'] -> Stage['post-processing']

node default {
/*include comum*/

  package {'vim-enhanced':
    ensure => installed
  }

  class { 'selinux':
    mode => 'permissive'
  }

  include fgv::webenv

  fgv::webenv::createapp{'gubddev':
    destdir => "gubddev",
    url => "gubddev.fgv.br"
  }
  mount { "/srv/httpd/gubddev/www":
    ensure  => mounted,
    device  => '/vagrant/app',
    fstype  => 'none',
    options => 'rw,bind',
    require => File['/srv/httpd/gubddev/www']
  }

  include fgv::webenv::devenv

}