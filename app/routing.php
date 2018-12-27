<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 27/12/16
 * Time: 15:15
 */

/*if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    include __DIR__ . '/index.php';
}*/
/*
if (preg_match('(?:[_/\da-zA-Z-]{3,})$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    include 'vars.php';
    define('LOCAL_ROUTING', true);
    include 'api/index.php';
}*/

include 'vars.php';
define('LOCAL_ROUTING', true);
include 'api/index.php';