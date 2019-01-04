<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 20/12/16
 * Time: 15:10
 */

define('ROOT', '../app');

include_once "../app/vendor/predis/predis/autoload.php";
include_once "../app/class/account.class.php";
include_once "../app/class/app.class.php";
include_once "../app/class/server.class.php";
include_once "../app/class/credential.class.php";
include_once "../app/class/configuration.class.php";
include_once "../app/class/userAccount.class.php";
include_once "../app/class/secure.class.php";
//include "../app/class/linkedList.class.php";
include_once "../app/class/listsManager.class.php";
include_once "../app/class/sharedStorageFactory.class.php";
include_once "../app/vendor/autoload.php";


$remote_single_server = array(
    'host'     => '127.0.0.1',
    //'host'     => '192.168.75.200',
    'port'     => 6379,
    'database' => 1
);

$sec = new \ccm\Secure();

// USERS
$client = new Predis\Client($remote_single_server, ['prefix' => 'user:']);

$ua = (new \ccm\userAccount("Admin", "---", 'local'))->addPermission(
        array("admin" => true));

$client->set($ua->getName(), $sec->encrypt(serialize($ua)));


// LISTS
$client = new Predis\Client($remote_single_server, ['prefix' => 'list:']);

$list = new \ccm\linkedList();
$list->insertLast('Production');
$list->insertLast('Development');
$list->insertLast('Homolog');
$client->set('Environments', $sec->encrypt(serialize($list)));


// INDEXES
$client = new Predis\Client($remote_single_server, ['prefix' => 'index:']);

$client->sadd("user", array($ua->getName()));
$client->sadd("list", array('Environments'));

