<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 11/01/17
 * Time: 17:25
 */

define('ROOT', '../app/');

require_once '../app/class/masterKeyManager.class.php';

exit(); // COMMENT THIS BEFORE RUNNING BUT DO NOT LET THIS SCRIPT EXISTS WHITHOUT IT

\ccm\masterKeyManager::createNewMasterKey(true, '/tmp/masterkey.php', 64);