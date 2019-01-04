# Class: ccm
#
# This class configures the ccm app
#
#
class ccm (

) {

  file {'/app/vars.pp':
    content => epp('ccm/vars.php.epp'),
  }

}