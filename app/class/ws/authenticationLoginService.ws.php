<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace gcc\ws;


include_once ROOT . "/baseincludes.php";
include_once ROOT . "/class/userAccountManager.class.php";
require_once ROOT . "/interfaces/log.interface.php";
require_once ROOT . "/class/logFactory.class.php";
require_once "api_response_code.php";

use gcc\authTokenManager;
use gcc\logFactory;
use gcc\Secure;
use gcc\systemManager;
use gcc\userAccountManager;


class authenticationLoginService extends RestService
{

    private $sec;

    function __construct($supportedMethods, $responseFormat)
    {
        $this->sec = new Secure();
        $this->response_format = $responseFormat;
        parent::__construct($supportedMethods);
    }

    public function autenticationValidate($url, $method, &$arguments)
    {
        return true;
    }

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {
        $this->performGet($url, $arguments, $accept);
    }

    /**
     *
     * The Get here will allow the users and systems to get the authentication Token
     * @param $url
     * @param $arguments
     * @param $accept
     */
    public function performGet($url, $arguments, $accept)
    {

        if (!array_key_exists('username', $arguments)) {

            if (!$this->test && !defined('UNIT_TESTING')) header('WWW-Authenticate: Basic realm="GCC"');
            $response['code'] = 5;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            $this->orc = $response;

            if (!$this->test && !defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        } else {


            $response = authenticationLoginService::convertBasicLoginIntoToken($arguments, $arguments['cipaddr']);
            $this->orc = $response;

            if ($response['data'] == "-1" || $response['data'] == "-2") {
                if ($response['data'] == "-1") $this->orc = "System not found";
                if ($response['data'] == "-2") $this->orc = "Invalid login credentials";
                $this->log = logFactory::getLogger();
                if (!$this->test) $this->log->Warning("Invalid login attempt. sistema=" . $arguments['username']);

            }

            if (!$this->test && !defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }


    }

    public static function convertBasicLoginIntoToken(&$arguments, $ip = '127.0.0.1')
    {

        $username = $arguments['username'];
        $password = $arguments['password'];

        if (array_key_exists('type', $arguments)) {
            $type = $arguments['type'];
            if ($type != 'sis' && $type != 'user') {
                $response['code'] = 9;
                $response['status'] = api_response_code::$cod_resp[$response['code']]['HTTP Response'];
                $response['data'] = api_response_code::$cod_resp[$response['code']]['Message'];
                return $response;
            }

        } else $type = 'undef';


        $log = logFactory::getLogger();
        $log->Debug("Entering AuthenticationLoginService->convertBasicLoginIntoToken", ['ip' => $ip , 'username' => $username]);

        //$log->Debug("User details", ['username' => $username, 'ip' => $ip]);

        $realusername = $username;
        $user = userAccountManager::get_instance()->find($username);

        if (!$user) {
            $username = str_replace('.', '_', $username);
            $user = userAccountManager::get_instance()->find($username);
        }

        if ($user != "") {
            $tm = authTokenManager::get_instance();
            $token = $tm->getUserToken($username, $password, $ip, $realusername);
            if ($token == "-1") $orc = "User or System not found";
            if ($token == "-2") $orc = "Invalid login credentials";

            if ($token != "-1" && $token != "-2") {
                $log->Info("Login executed sucessfully.", ['username' =>$username, 'ip' => $ip ] );
                $response['code'] = 1;
                $response['status'] = api_response_code::$cod_resp[$response['code']]['HTTP Response'];
                $response['data'] = $token;
                $arguments['token'] = $token->tokenValue;
                if (!defined('UNIT_TESTING')) setcookie("gccAuthToken", $token->tokenValue, time() + SESSION_TIME, '/api');
            } else {

                $response['code'] = 5;
                $response['status'] = api_response_code::$cod_resp[$response['code']]['HTTP Response'];
                $response['data'] = $orc;
            }
        } else {

            $response['code'] = 5;
            $response['status'] = api_response_code::$cod_resp[$response['code']]['HTTP Response'];
            $response['data'] = api_response_code::$cod_resp[$response['code']]['Message'];;
        }

        return $response;
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