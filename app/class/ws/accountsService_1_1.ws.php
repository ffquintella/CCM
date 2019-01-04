<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace ccm\ws;

use ccm\connectionStringBuilder;
use ccm\Secure;
use ccm\userAccountManager;


require_once ROOT . "/class/userAccountManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";

/*class accountInfo {
    public $login;
}*/

class accountsService_1_1 extends authenticatedService
{


    private $sec;

    function __construct($supportedMethods, $responseFormat)
    {
        $this->sec = new Secure();
        $this->response_format = $responseFormat;
        parent::__construct($supportedMethods, $responseFormat);
    }

    // If we get to theses methods the class is already authenticated
    /**
     * @param $url
     * @param $arguments
     * @param $accept
     */
    public function performGet($url, $arguments, $accept)
    {

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Looking for Accounts with: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }


        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response['code'] = 14;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            $this->deliver_response($this->response_format, $response);

        }
        if ($token->getTokenType() == 'user') {


            $user = userAccountManager::get_instance()->find($token->getUserName());

            $resp2 = array();

            if (array_key_exists('resource1', $arguments)) {
                $response = $this->getUser($user, $arguments['resource1']);
            } else {

                if ($user->hasPermission('admin')) {

                    $acm = userAccountManager::get_instance();

                    $resp = $acm->getList();

                    for ($i = 1; $i <= $resp->totalNodes(); $i++) {

                        $val = $resp->readNode($i);

                        //$user_info = new accountInfo();
                        $user_info = new \ccm\account($val->getName(), "---");

                        //$user_info->name = $val->getName();

                        $resp2[] = $user_info;

                    }

                    $response['code'] = 1;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $resp2;

                } else {

                    $response['code'] = 14;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];
                }
            }

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        }
        $this->methodNotAllowedResponse();
    }

    /**
     * @param $requestingUser The user requesting the service
     * @param $userKey - The user requested
     */
    public function getUser($requestingUser, $userKey)
    {
        if ($requestingUser->hasPermission('admin') || $userKey == $requestingUser->getName()) {
            $acm = userAccountManager::get_instance();
            $ru = $acm->find($userKey);
            if ($ru == null) {
                $userKey = str_replace('.', '_', $userKey);
                $ru = $acm->find($userKey);
            }
            if ($ru == null) {
                $response['code'] = 3;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];
            } else {
                $response['code'] = 1;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $ru->readData();
            }
        } else {
            $response['code'] = 14;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];
        }
        return $response;
    }

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {
        $log = \ccm\logFactory::getLogger();

        $log->Debug("Looking for Accounts with: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);

        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response['code'] = 14;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (!$user->hasPermission('admin')) {

                $response['code'] = 14;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;

            } else {


                if (array_key_exists('resource1', $arguments)) {

                    // Let's try to create the user ...
                    $userkey = $arguments['resource1'];

                    // First we need to know he doesn't exists yet.

                    $acm = userAccountManager::get_instance();
                    $userkey = str_replace('.', '_', $userkey);
                    $ru = $acm->find($userkey);

                    if ($ru != null) {
                        // The account already exists so we need to update it
                        $log->Info("Atualizando conta:" . $userkey . " ip:" . $arguments['cipaddr']);
                        $obj = json_decode($arguments['body']);

                        if ($obj == null) {
                            // Invalid request
                            $response['code'] = 9;
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {
                            if (property_exists($obj, 'password')) {
                                if (property_exists($obj, 'permissions')) {
                                    if (property_exists($obj, 'authentication')) {
                                        $result = $acm->update($obj->name, $obj->password, $obj->permissions, $obj->authentication);
                                    } else $result = $acm->update($obj->name, $obj->password, $obj->permissions);
                                } else {
                                    if (property_exists($obj, 'authentication')) {
                                        $result = $acm->update($obj->name, $obj->password, null, $obj->authentication);
                                    } else $result = $acm->update($obj->name, $obj->password, null);
                                }
                            } else {
                                if (property_exists($obj, 'permissions')) {
                                    if (property_exists($obj, 'authentication')) {
                                        $result = $acm->update($obj->name, null, $obj->permissions, $obj->authentication);
                                    } else $result = $acm->update($obj->name, null, $obj->permissions);
                                } else {
                                    if (property_exists($obj, 'authentication')) {
                                        $result = $acm->update($obj->name, null, null, $obj->authentication);
                                    } else $result = $acm->update($obj->name, null, null);
                                }
                            }
                            if ($result != 1) {
                                // Invalid request
                                $response['code'] = 0;
                                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            } else {
                                // Update sucessfull
                                $response['code'] = 1;
                                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                        }


                    } else {

                        // The account already exists so we need to update it
                        $response['code'] = 15;
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'] . " Please use PUT to create new users";
                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }


                } else {
                    // We do not accept request without a resource
                    $response['code'] = 0;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }
            }
        }
    }

    public function performPut($url, $arguments, $accept)
    {
        $log = \ccm\logFactory::getLogger();
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log->Debug("Looking for Accounts with: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response['code'] = 14;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            //$resp2 = array();

            if (!$user->hasPermission('admin')) {

                $response['code'] = 14;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;

            } else {


                if (array_key_exists('resource1', $arguments)) {

                    // Let's try to create the user ...
                    $userkey = $arguments['resource1'];

                    // First we need to know he doesn't exists yet.

                    $acm = userAccountManager::get_instance();
                    $userkey = str_replace('.', '_', $userkey);
                    $ru = $acm->find($userkey);

                    if ($ru != null) {
                        // The account already exists
                        $response['code'] = 15;
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];
                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $log->Info("Criando conta:" . $userkey . " ip:" . $arguments['cipaddr']);
                        $obj = json_decode($arguments['body']);

                        if ($obj == null) {
                            // Invalid request
                            $response['code'] = 9;
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {
                            if (property_exists($obj, 'permissions')) {
                                if (property_exists($obj, 'authentication')) {
                                    $result = $acm->create($obj->name, $obj->password, $obj->permissions, $obj->authentication);
                                } else $result = $acm->create($obj->name, $obj->password, $obj->permissions);
                            } else {
                                if (property_exists($obj, 'authentication')) {
                                    $result = $acm->create($obj->name, $obj->password, null, $obj->authentication);
                                } else $result = $acm->create($obj->name, $obj->password, null);
                            }
                            if ($result != 1) {
                                // Invalid request
                                $response['code'] = 0;
                                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            } else {
                                // Creation sucessfull
                                $response['code'] = 2;
                                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                        }

                    }


                } else {
                    // We do not accept request without a resource
                    $response['code'] = 0;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }
            }
        }
    }

    /**
     *
     * This method delets a record if it exists and if the user has rights to do it.
     *
     * @param $url - The url being called
     * @param $arguments - The arguments of the url
     * @param $accept
     * @return mixed
     */
    public function performDelete($url, $arguments, $accept)
    {
        $log = \ccm\logFactory::getLogger();
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log->Debug("Looking for Accounts with: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response['code'] = 14;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());


            if (!$user->hasPermission('admin')) {

                $response['code'] = 14;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];
                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;

            } else {


                if (array_key_exists('resource1', $arguments)) {

                    // Let's try to create the user ...
                    $userkey = $arguments['resource1'];


                    // First we need to know that the user already exists and that it isn't the actual user.

                    $acm = userAccountManager::get_instance();
                    $userkey = str_replace('.', '_', $userkey);
                    $ru = $acm->find($userkey);

                    if ($ru == null || $userkey == $user->getName()) {

                        if ($ru == null) {
                            // The account doesn't exists
                            $response['code'] = 3;
                        } else $response['code'] = 15;
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        if ($userkey == $user->getName()) $response['data'] = 'Voce nao pode apagar o proprio usuario';
                        else $response['data'] = $this->api_response_code[$response['code']]['Message'];
                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $log->Info("Apagando conta:" . $userkey . " ip:" . $arguments['cipaddr']);


                        $result = $acm->delete($userkey);

                        if ($result != 1) {
                            // Invalid request
                            $response['code'] = 0;
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {
                            // Deleted sucessfully
                            $response['code'] = 1;
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }

                    }


                } else {
                    // We do not accept request without a resource
                    $response['code'] = 0;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }
            }
        }
    }


} 