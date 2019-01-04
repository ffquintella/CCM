<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 08/03/14
 * Time: 15:54
 */

if (!defined('LOCAL_ROUTING'))
    require_once __DIR__ . "/../vars.php";

require_once ROOT . "/api/apiincludes.php";

$service = new ccm\ws\GuRouter();
$service->handleRawRequest($_SERVER, $_GET, $_POST);