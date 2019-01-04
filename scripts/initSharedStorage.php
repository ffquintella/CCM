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

//$sec = new \gcc\Secure();

$sec = new \ccm\Secure();


// USERS
$client = new Predis\Client($remote_single_server, ['prefix' => 'user:']);

$ua = (new \ccm\userAccount("Utestes", "teste", 'local'))->addPermission(
        array("admin" => true));

$ua2 =  (new \ccm\userAccount("Utestes2", "teste123", 'local'))->addPermission(
    array("Stestes" => "reader"));

$ua3 =  (new \ccm\userAccount("Utestes3", "teste123", 'local'))->addPermission(
    array("app:Tapp" => "reader"));

$ua4 =  (new \ccm\userAccount("Utestes4", "teste123", 'local'))->addPermission(
    array("app:Tapp" => "writer"));

$client->set($ua->getName(), $sec->encrypt(serialize($ua)));
$client->set($ua2->getName(), $sec->encrypt(serialize($ua2)));
$client->set($ua3->getName(), $sec->encrypt(serialize($ua3)));
$client->set($ua4->getName(), $sec->encrypt(serialize($ua4)));

echo "##### USERS ##### \n";

// LISTS
$client = new Predis\Client($remote_single_server, ['prefix' => 'list:']);

$list = new \ccm\linkedList();
$list->insertLast('Produção');
$list->insertLast('Desenvolvimento');
$list->insertLast('Homologação');
$client->set('Environments', $sec->encrypt(serialize($list)));

echo "##### LISTS ##### \n";

// APPS
$client = new Predis\Client($remote_single_server, ['prefix' => 'app:']);

$app1 = new \ccm\app('Tapp','Utestes');
$app1->addEnvironment('Desenvolvimento');
$app1->setOldKey($app1->getKey());
$client->set($app1->getName(), $sec->encrypt(serialize($app1)));

$app2 = new \ccm\app('Tapp2','Utestes');
$app2->addEnvironment('Produção');
$app2->addEnvironment('Desenvolvimento');
$app2->setOldKey($app2->getKey());
$client->set($app2->getName(), $sec->encrypt(serialize($app2)));

$client = new Predis\Client($remote_single_server, ['prefix' => 'ref:']);

$client->set('key-app:'.md5($app1->getKey()), $app1->getName());
$client->set('key-app:'.md5($app2->getKey()), $app2->getName());

echo "##### APPS ##### \n";

// SERVERS
$client = new Predis\Client($remote_single_server, ['prefix' => 'server:']);

$server = new \ccm\server('Tserver', 'tserver.ip.com');
$client->set($server->getName(), $sec->encrypt(serialize($server)));

$server = new \ccm\server('Tserver2', 'tserver.ip.com');
$server->assign('Tapp2', 'Desenvolvimento');

$client->set($server->getName(), $sec->encrypt(serialize($server)));

$server = new \ccm\server('Tserver3', 'localhost');
$server->assign('Tapp2', 'Produção');

$client->set($server->getName(), $sec->encrypt(serialize($server)));

$client = new Predis\Client($remote_single_server, ['prefix' => 'ref:']);

$client->sadd('app-server:Tapp2', 'Tserver:Desenvolvimento');
$client->sadd('app-server:Tapp2', 'Tserver:Produção');
$client->sadd('app-server:Tapp2', 'Tserver3:Produção');

//CREDENTIALS
$client = new Predis\Client($remote_single_server, ['prefix' => 'credential:']);

$cred1 = new \ccm\credential('tc1','Tapp2','local');
$cred1->setValue('Produção', 'valp');
$cred1->setValue('Desenvolvimento', 'vald');
$client->set($cred1->getName(), $sec->encrypt(serialize($cred1)));


$cred2 = new \ccm\credential('tc2','Tapp2','local');
$cred2->setValue('Produção', 'val2p');
$cred2->setValue('Desenvolvimento', 'val2d');
$client->set($cred2->getName(), $sec->encrypt(serialize($cred2)));

$cred3 = new \ccm\credential('tc3','Tapp','local');
$cred3->setValue('Desenvolvimento', 'val3d');
$client->set($cred3->getName(), $sec->encrypt(serialize($cred3)));

$client = new Predis\Client($remote_single_server, ['prefix' => 'ref:']);
$client->sadd('app-credential:Tapp2', 'tc1');
$client->sadd('app-credential:Tapp2', 'tc2');
$client->sadd('app-credential:Tapp', 'tc3');


//CONFIGURATIONS
$client = new Predis\Client($remote_single_server, ['prefix' => 'configuration:']);

$conf1 = new \ccm\configuration('tconf1','Tapp2');
$conf1->setValue('Produção', 'valp');
$conf1->setValue('Desenvolvimento', 'vald');
$client->set($conf1->getName(), $sec->encrypt(serialize($conf1)));


$conf2 = new \ccm\configuration('tconf2','Tapp2');
$conf2->setValue('Produção', 'f1=${tc1}');
$conf2->setValue('Desenvolvimento', 'val2d');
$client->set($conf2->getName(), $sec->encrypt(serialize($conf2)));

$client = new Predis\Client($remote_single_server, ['prefix' => 'ref:']);
$client->sadd('app-configuration:Tapp2', 'tconf1');
$client->sadd('app-configuration:Tapp2', 'tconf2');
$client->sadd('app-configuration:Tapp', 'tconf2');

// INDEXES
$client = new Predis\Client($remote_single_server, ['prefix' => 'index:']);

$client->sadd("user", array($ua->getName(),$ua2->getName(), $ua3->getName(),$ua4->getName() ));
$client->sadd("app", array($app1->getName(), 'Tapp2'));
$client->sadd("server", array('Tserver', 'Tserver2', 'Tserver3'));
$client->sadd("credential", array('tc1', 'tc2', 'tc3'));
$client->sadd("configuration", array('tconf1', 'tconf2'));
$client->sadd("list", array('Environments'));

