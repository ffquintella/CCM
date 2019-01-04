<?php

require_once "vars.php";
require_once ROOT . "/class/tools/environment.class.php";

echo "---------- <br> ";

echo gethostname();

echo "<br>";

function url_origin($s, $use_forwarded_host = false)
{
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false)
{
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

$absolute_url = full_url($_SERVER);
echo $absolute_url;
echo "<br> ---------- <br> \n";
echo "Conectando de: " . \ccm\tools\environment::getUserIP();
echo "<bR>\n";


phpinfo();