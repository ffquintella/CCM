<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace gcc\ws;

use gcc\app;
use gcc\connectionStringBuilder;
use gcc\tools\permissionTools;
use gcc\tools\strTools;
use gcc\userAccountManager;

require_once ROOT . "/class/tools/environment.class.php";

class pingService extends RestService
{

    public function autenticationValidate($url, $method, &$arguments)
    {
        return true;
    }

    // If we get to theses methods the class is already authenticated
    public function performGet($url, $arguments, $accept)
    {

        $response = $this->quickResponse(1, "OK"); // OK

        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
        else return $response;



        $this->methodNotAllowedResponse();
    }

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }

    public function performPut($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }

    public function performDelete($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }


} 