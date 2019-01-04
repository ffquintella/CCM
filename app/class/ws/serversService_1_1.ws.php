<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 03/01/17
 * Time: 14:08
 */

namespace ccm\ws;

use ccm\server;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\userAccountManager;
use Predis\Command\ServerInfo;
use ccm\dom;

require_once ROOT . "/class/appsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/class/dom/serverInfo.popo.php";

class serversService_1_1 extends serversService
{


    // If we get to theses methods the class is already authenticated
    public function performGet($url, $arguments, $accept)
    {

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Searching apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $serversm = \ccm\serversManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'app') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {  // Details view

                $server = $arguments['resource1'];

                if ($server == '' || $server == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:servers' => 'reader', 'server:' . $server => 'reader'), $user)) {
                    // OK we have permission

                    $srvObj = $serversm->find($server);

                    if ($srvObj == null) {
                        $response = $this->quickResponse(7); // Not Found

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $response = $this->quickResponse(1, $srvObj); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;

                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                $resp2 = array();

                if (permissionTools::validate(array('admin' => true, 'global:servers' => 'reader'), $user)) {

                    $resp = $serversm->getList();

                    for ($i = 1; $i <= $resp->totalNodes(); $i++) {
                        $val = $resp->readNode($i);

                        $sInfo = new dom\serverInfo();

                        $sInfo->name = $val->getName();
                        $sInfo->fqdn = $val->getFQDN();

                        $resp2[] = $sInfo;

                    }


                    usort($resp2, 'gcc\ws\usServers');

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

    public function performPost($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Updating apps with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $serversm = \ccm\serversManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $server = $arguments['resource1'];

                if ($server == '' || $server == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:servers' => 'writer', 'server:' . $server => 'writer'), $user)) {
                    // OK we have permission

                    $srvObj = $serversm->find($server);

                    if ($srvObj != null) {

                        $bd = $arguments['body'];


                        if ($bd == null || $bd == '') {
                            $response = $this->quickResponse(15); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {

                            $obj = json_decode($bd, true);


                            if (array_key_exists('assignments', $obj)) {
                                $srvObj->cleanAssignments();
                                foreach ($obj['assignments'] as $app => $value) {
                                    foreach ($value as $appk => $environment)
                                        $srvObj->assign($app, $environment);
                                }
                            }

                            if (array_key_exists('fqdn', $obj)) {
                                $srvObj->setFQDN($obj['fqdn']);
                            }

                            $resp = $serversm->save($srvObj);

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

        $serversm = \ccm\serversManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $server = $arguments['resource1'];

                if ($server == '' || $server == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:servers' => 'writer'), $user)) {
                    // OK we have permission

                    $srvObj = $serversm->find($server);

                    if ($srvObj == null) {

                        $bd = $arguments['body'];

                        $result = "";

                        if ($bd == null || $bd == '') {
                            $response = $this->quickResponse(15); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        } else {

                            $obj = json_decode($bd, true);

                            if (!array_key_exists('fqdn', $obj)) {
                                $response = $this->quickResponse(15, 'fqdn is mandatory'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }

                            $srvObj = new server($server, $obj['fqdn']);

                            if (is_array($obj) && array_key_exists('assignments', $obj)) {
                                //$srvObj->cleanAssignments();
                                foreach ($obj['assignments'] as $app => $value) {
                                    foreach ($value as $appk => $environment)
                                        $srvObj->assign($app, $environment);
                                }


                                $resp = $serversm->save($srvObj);

                                if ($resp != 1) {
                                    $response = $this->quickResponse(11); // Unkwon error

                                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                    else return $response;
                                }


                            } else {
                                $response = $this->quickResponse(15, 'assignments is mandatory'); // Conflict

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

        $serversm = \ccm\serversManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $server = $arguments['resource1'];

                if ($server == '' || $server == null) {

                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true, 'global:servers' => 'writer', 'server:' . $server => 'writer'), $user)) {
                    // OK we have permission

                    $srvObj = $serversm->find($server);


                    if ($srvObj == null) {
                        $response = $this->quickResponse(7); // Not Found

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $resp = $serversm->delete($server);

                    if ($resp != 1) {
                        if ($srvObj == null) {
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

function usServers($a, $b) {

    $result = strcmp($a->name, $b->name);

    return $result;
}