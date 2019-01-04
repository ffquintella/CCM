<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace ccm\ws;

use ccm\tools\environment;

class authenticatedService extends RestService
{


    function __construct($supportedMethods, $responseFormat)
    {
        //$this->sec = new Secure();
        $this->response_format = $responseFormat;
        parent::__construct($supportedMethods);
    }


    public function getToken($arguments)
    {
        return $this->atm->rebuildToken($arguments['token']);
    }


    public function autenticationValidate($url, $method, &$arguments)
    {
        $headers = \apache_request_headers();

        $this->log->Trace("Entering AuthenticatedService->autenticationValidate", ['url' => $url, 'headers' => $headers]);


        $log = $this->log;

        if (is_array($_COOKIE) && array_key_exists('gccAuthToken', $_COOKIE)) {

            $arguments['token'] = $_COOKIE['gccAuthToken'];

            if (!defined('UNIT_TESTING')) setcookie("gccAuthToken", $arguments['token'], time() + SESSION_TIME, '/api');

            $log->Debug("Validating token", [ 'token' => substr($arguments['token'], 0, round(strlen($arguments['token']) / 2)) . "***", 'ip' => environment::getUserIP() ]);


            //$log->Debug("Starting token validation process token=" . substr($arguments['token'], 0, round(strlen($arguments['token']) / 2)) . "*** clientIP=" . environment::getUserIP());

        }

        if ($headers != null && is_array($headers)) {
            $val = false;

            if (array_key_exists('authorization', $headers)) {
                $arguments['token'] = $headers['authorization'];
                $val = true;
            }
            if (array_key_exists('AUTHORIZATION', $headers)){
                $arguments['token'] = $headers['AUTHORIZATION'];
                $val = true;
            }
            if (array_key_exists('Authorization', $headers)){
                $arguments['token'] = $headers['Authorization'];
                $val = true;
            }
            if($val){
                $log->Debug("Validating token", [ 'token' => substr($arguments['token'], 0, round(strlen($arguments['token']) / 2)) . "***", 'ip' => environment::getUserIP() ]);
            }
        }


        $ip = environment::getUserIP();

        if (array_key_exists('username', $arguments)) {
            $log->Debug("Starting user validation process user=" . $arguments['username'] . "*** clientIP=" . environment::getUserIP());

            authenticationLoginService::convertBasicLoginIntoToken($arguments, $ip);
        }

        if (array_key_exists('token', $arguments)) {
            //$log->Debug("Starting token validation process token=" . substr($arguments['token'], 0, round(strlen($arguments['token']) / 2)) . "*** clientIP=" . environment::getUserIP());

            if ($this->atm->validateToken($arguments['token'], $ip))
                return true;
            else return false;
        }
    }

    public function performGet($url, $arguments, $accept)
    {
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