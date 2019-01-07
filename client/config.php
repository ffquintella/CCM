<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 16:06
 */

define ('USE_SRVC', false);

define ('SRVC_DNS', 'abc.123.com');


define ('SERVER_ADDR', 'xxxx');
define ('SERVER_PORT', '443');
define ('SSL', true);

define ('INSECURE_SSL', true);

define ('SESSION_TIMEOUT', 600);

define ('USER_PASS_SIZE', 15);

define ('API_VERSION' , '1.1');

define('USE_DEBUG_PROXY', true);

define('DEBUG_PROXY', 'localhost');

define('DEBUG_PROXY_PORT', 8080);

//define ('LANGUAGE', 'pt_br');
define ('LANGUAGE', 'en_us');

include "languages/".LANGUAGE.".lang.php";
