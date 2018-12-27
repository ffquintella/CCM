<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 16:12
 */

require_once "config.php";

require_once __DIR__.'/vendor/autoload.php';

foreach (glob(__DIR__."/commands/*.command.php") as $filename)
{
    require_once $filename;
}

foreach (glob(__DIR__."/validators/*.validator.php") as $filename)
{
    include $filename;
}

foreach (glob(__DIR__."/listers/*.lister.php") as $filename)
{
    include $filename;
}

require_once __DIR__.'/tools/loginManager.php';
require_once __DIR__.'/tools/passwordDialog.php';
require_once __DIR__.'/tools/urlManager.php';
require_once __DIR__.'/tools/randomString.php';
require_once __DIR__.'/tools/curlHelper.php';
require_once __DIR__.'/tools/validators.php';
require_once __DIR__.'/tools/strTools.php';


require_once __DIR__ . '/engine/formsEngine.php';
require_once __DIR__.'/engine/exceptions/fileNotFoundException.php';