<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 20/12/16
 * Time: 15:10
 */

define('ROOT', '../app');

include "../app/vendor/predis/predis/autoload.php";
include "../app/class/account.class.php";
include "../app/class/app.class.php";
include "../app/class/server.class.php";
include "../app/class/credential.class.php";
include "../app/class/configuration.class.php";
include "../app/class/userAccount.class.php";
include "../app/class/secure.class.php";
//include "../app/class/linkedList.class.php";
include "../app/class/listsManager.class.php";
include "../app/class/sharedStorageFactory.class.php";
include "../app/vendor/autoload.php";


$remote_single_server = array(
    'host'     => '127.0.0.1',
    //'host'     => '192.168.75.200',
    'port'     => 6379,
    'database' => 1
);

$sec = new \ccm\Secure();

// USERS
$client = new Predis\Client($remote_single_server, ['prefix' => 'user:']);

$ua = (new \ccm\userAccount("Admin", "xxxxxxxxx", 'local'))->addPermission(
        array("admin" => true));

$client->set($ua->getName(), $sec->encrypt(serialize($ua)));


// LISTS
$client = new Predis\Client($remote_single_server, ['prefix' => 'list:']);

$list = new \ccm\linkedList();
$list->insertLast('Produção');
$list->insertLast('Desenvolvimento');
$list->insertLast('Homologação');
$client->set('Environments', $sec->encrypt(serialize($list)));


// INDEXES
$client = new Predis\Client($remote_single_server, ['prefix' => 'index:']);

$client->sadd("user", array($ua->getName()));
$client->sadd("list", array('Environments'));

