<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 16:06
 */

define ('USE_SRVC', false);

define ('SRVC_DNS', 'abc.123.com');


define ('SERVER_ADDR', 'localhost');
define ('SERVER_PORT', '8033');
define ('SSL', false);

define ('INSECURE_SSL', true);

define ('SESSION_TIMEOUT', 600);

define ('USER_PASS_SIZE', 15);

define ('LANGUAGE', 'pt_br');

include "languages/".LANGUAGE.".lang.php";
