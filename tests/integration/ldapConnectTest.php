<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 27/12/16
 * Time: 14:26
 */

// LDAP variables
$ldaphost = "ldaps://bo1004.fgv.br";  // your ldap servers
$ldapport = 636;                 // your ldap server's port number

putenv('LDAPTLS_REQCERT=never');

// Connecting to LDAP
$ldapconn = ldap_connect($ldaphost, $ldapport)
or die("Could not connect to $ldaphost");

//ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

var_dump($ldapconn);

if ($ldapconn) {

    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, 'xxxx@fgv.br', 'xxxx');

    // verify binding
    if ($ldapbind) {
        echo "LDAP bind successful...";
    } else {
        echo "LDAP bind failed...";
    }

}