<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace ccm\ws;

use ccm\app;
use ccm\connectionStringBuilder;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\userAccountManager;

require_once ROOT . "/class/appsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";

class appsService extends authenticatedService
{


    // If we get to theses methods the class is already authenticated
    public function performGet($url, $arguments, $accept)
    {

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Searching apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $appsm = \ccm\appsManager::get_instance();
        $token = $this->getToken($arguments);


        if ($token->getTokenType() == 'app') {

            if (array_key_exists('resource1', $arguments)) {

                $app = $arguments['resource1'];

                if ($app == $token->getUserName()) {

                    $appObj = $appsm->find($app);

                    if ($appObj == null) {
                        $response = $this->quickResponse(11); // Internal server error - This should not happen

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $response = $this->quickResponse(1, $appObj->readData()); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;

                } else {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }
            $response = $this->quickResponse(15); // Conflict

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $app = $arguments['resource1'];

                if ($app == '' || $app == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'reader', 'app:' . $app => 'reader'), $user)) {
                    // OK we have permission

                    $appObj = $appsm->find($app);

                    if ($appObj == null) {
                        $response = $this->quickResponse(7); // Not Found

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $response = $this->quickResponse(1, $appObj->readData()); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;

                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                $resp2 = array();

                $perms = $user->getPermissions();

                $has_app_perm = false;

                foreach ($perms as $key => $value){
                    if(strTools::startsWith($key, 'app:')){
                        $has_app_perm = true;
                        break;
                    }
                }


                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'reader'), $user) || $has_app_perm) {

                    $resp = $appsm->getList();

                    if(permissionTools::validate(array('admin' => true, 'global:apps' => 'reader'), $user)) {
                        for ($i = 1; $i <= $resp->totalNodes(); $i++) {
                            $val = $resp->readNode($i);
                            $resp2['App-' . $i] = strTools::removeSpaces($val->getName());
                        }
                    }else{
                        $z = 1;
                        for ($i = 1; $i <= $resp->totalNodes(); $i++) {
                            $val = $resp->readNode($i);
                            if(permissionTools::validate(array('app:'.$val->getName() => 'reader'), $user)) {
                                $resp2['App-' . $z] = strTools::removeSpaces($val->getName());
                                $z++;
                            }
                        }
                    }

                    $response = $this->quickResponse(1, $resp2); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }


        }
        $this->methodNotAllowedResponse();
    }

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Updating apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $appsm = \ccm\appsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $app = $arguments['resource1'];

                if ($app == '' || $app == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'writer'), $user)) {
                    // OK we have permission

                    $appObj = $appsm->find($app);

                    if ($appObj != null) {

                        //$appObj = new app($app, $token->getUserName());

                        $bd = $arguments['body'];

                        $result = "";

                        if ($bd == null || $bd == '') {
                            $response = $this->quickResponse(15); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {

                            $obj = json_decode($bd, true);

                            if (array_key_exists('environments', $obj)) {
                                $appObj->cleanEnvironments();
                                foreach ($obj['environments'] as $key => $value) {
                                    $appObj->addEnvironment($value);
                                }
                            }

                            if (array_key_exists('key', $obj)) {

                                if (strlen($obj['key']) != APP_KEY_SIZE) {
                                    $response = $this->quickResponse(15, 'The App Key Size must be:' . APP_KEY_SIZE); // Conflict

                                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                    else return $response;
                                }


                                $appObj->setKey($obj['key']);
                            }

                            $resp = $appsm->save($appObj);

                            if ($resp != 1) {
                                $response = $this->quickResponse(14); // Unkwon error

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }

                        }

                        $response = $this->quickResponse(1, 'updated'); // OK Created

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $response = $this->quickResponse(15); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {
                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;

            }


        }
        $this->methodNotAllowedResponse();
    }

    public function performPut($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Creating apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $appsm = \ccm\appsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $app = $arguments['resource1'];

                if ($app == '' || $app == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'writer'), $user)) {
                    // OK we have permission

                    $appObj = $appsm->find($app);

                    if ($appObj == null) {

                        $appObj = new app($app, $token->getUserName());

                        $bd = $arguments['body'];

                        $result = "";

                        if ($bd == null || $bd == '') {
                            $response = $this->quickResponse(15); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {

                            $obj = json_decode($bd, true);

                            if (array_key_exists('environments', $obj)) {
                                foreach ($obj['environments'] as $key => $value) {
                                    $appObj->addEnvironment($value);
                                }
                                $resp = $appsm->save($appObj);

                                if ($resp != 1) {
                                    $response = $this->quickResponse(11); // Unkwon error

                                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                    else return $response;
                                }

                            } else {
                                $response = $this->quickResponse(15, 'Environments key is mandatory'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }

                        }

                        $response = $this->quickResponse(2, $result); // OK Created

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $response = $this->quickResponse(15); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {
                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;

            }


        }
        $this->methodNotAllowedResponse();
    }

    public function performDelete($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Deleting apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $appsm = \ccm\appsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $app = $arguments['resource1'];

                if ($app == '' || $app == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'writer', 'app:' . $app => 'writer'), $user)) {
                    // OK we have permission

                    $appObj = $appsm->find($app);


                    if ($appObj == null) {
                        $response = $this->quickResponse(7); // Not Found

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $resp = $appsm->delete($app);

                    if ($resp != 1) {
                        if ($appObj == null) {
                            $response = $this->quickResponse(11); // Internal Server Error

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }
                    }

                    $response = $this->quickResponse(1, 'deleted'); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;

                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {


                $response = $this->quickResponse(14); // Permission Denied

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;


            }


        }
        $this->methodNotAllowedResponse();
    }


} 