<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 13/06/13
 * Time: 10:25
 * To change this template use File | Settings | File Templates.
 */


require_once "databaseManagerTest.php";
require_once "vendor/autoload.php";


$suite  = new PHPUnit_TestSuite("databaseManagerTest");
$result = PHPUnit::run($suite);

echo $result -> toString();