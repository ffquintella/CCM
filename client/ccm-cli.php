#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 16:16
 */

require_once 'includes.php';

$console = new ConsoleKit\Console();


$console->addCommand('appsCommand');
$console->addCommand('configurationsCommand');
$console->addCommand('credentialsCommand');
$console->addCommand('listsCommand');
$console->addCommand('serversCommand');
$console->addCommand('usersCommand');


$console->run();