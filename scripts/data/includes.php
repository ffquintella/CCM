<?php

require_once __DIR__.'/vendor/autoload.php';

foreach (glob(__DIR__."/commands/*.command.php") as $filename)
{
    require_once $filename;
}

#CCM Classes

define('ROOT', __DIR__.'/../../app');

include_once ROOT."/vendor/predis/predis/autoload.php";
include_once ROOT."/class/account.class.php";
include_once ROOT."/class/app.class.php";
include_once ROOT."/class/server.class.php";
include_once ROOT."/class/credential.class.php";
include_once ROOT."/class/configuration.class.php";
include_once ROOT."/class/userAccount.class.php";
include_once ROOT."/class/secure.class.php";
include_once ROOT."/class/listsManager.class.php";
include_once ROOT."/class/sharedStorageFactory.class.php";
include_once ROOT."/vendor/autoload.php";

Predis\Autoloader::register();