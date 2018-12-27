<?php
/**
 * Created by PhpStorm.
 * User: Felipe Quintella
 * Date: 29/12/16
 * Time: 15:30
 */

namespace gcc\ws;

use gcc\linkedList;
use gcc\listsManager;
use gcc\tools\permissionTools;
use gcc\tools\strTools;
use gcc\userAccountManager;

require_once ROOT . "/class/listsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";

/**
 * Class ListsService
 * @package gcc\ws
 */
class listsService extends authenticatedService
{


    public function performGet($url, $arguments, $accept)
    {

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \gcc\logFactory::getLogger();
            $log->Debug("Searching list with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $listsm = listsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            //TODO: Implement system / app access

            $response['code'] = 12; // Not Implemented
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $list = $arguments['resource1'];

                if ($list == '' || $list == null) {
                    $response['code'] = 15; //Conflict
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }


                // Reading lists is allowed to everyone

                $listObj = $listsm->find($list);

                if ($listObj != null) {

                    $response['code'] = 1; // 200 OK
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $listObj->readList();

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {

                    $response['code'] = 7; // 404 NotFound
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }


            } else {

                $resp2 = array();

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'reader'), $user)) {

                    $resp = $listsm->getList();

                    for ($i = 1; $i <= $resp->totalNodes(); $i++) {
                        $val = $resp->readNode($i);
                        $resp2['List-' . $i] = strTools::removeSpaces($val['name']);
                    }

                    $response['code'] = 1;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $resp2;

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {

                    $response['code'] = 14;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

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
            $log = \gcc\logFactory::getLogger();
            $log->Debug("Updating list with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $listsm = listsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            //Systems / Apps cannot update

            $response['code'] = 14; // Permission denied
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $list = $arguments['resource1'];

                if (permissionTools::validate(array('admin' => true, 'global:lists' => 'writer', 'list:' . $list => 'writer'), $user)) {

                    if ($list == '' || $list == null) {
                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Debug('Tentativa de criação inválida user=' . $user->getName());

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }

                    $listObj = $listsm->find($list);

                    if ($listObj != null) {

                        $arr = json_decode($arguments['body']);

                        $obj = new linkedList();
                        foreach ($arr as $key => $value) {
                            $obj->insertLast($value);
                        }

                        $resp = $listsm->save($list, $obj);

                        if ($resp == 1) {

                            $response['code'] = 1; // 200 OK
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;

                        } else {
                            $response['code'] = 11; // Internal Server error
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];

                            $this->log->Error('Erro tentando criar lista=' . $list . ' resposta=' . $resp);

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }


                    } else {

                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Info('Tentativa de criar lista existente user=' . $user->getName() . ' lista=' . $list);

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }

                } else {

                    $response['code'] = 14; // Permission denied
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                // You can only create individual itens

                $response['code'] = 15;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;


            }


        }
        $this->methodNotAllowedResponse();
    }

    public function performPut($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \gcc\logFactory::getLogger();
            $log->Debug("Creating list with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $listsm = listsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            //Systems / Apps cannot create

            $response['code'] = 14; // Permission denied
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $list = $arguments['resource1'];

                if (permissionTools::validate(array('admin' => true, 'global:lists' => 'writer', 'list:' . $list => 'writer'), $user)) {

                    if ($list == '' || $list == null) {
                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Debug('Tentativa de criação inválida user=' . $user->getName());

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }

                    $listObj = $listsm->find($list);

                    if ($listObj == null) {

                        if (!array_key_exists('body', $arguments)) {
                            $arr = array();
                        } else $arr = json_decode($arguments['body']);

                        $obj = new linkedList();
                        foreach ($arr as $key => $value) {
                            $obj->insertLast($value);
                        }

                        $resp = $listsm->save($list, $obj);

                        if ($resp == 1) {

                            $response['code'] = 2; // 201 Created
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;

                        } else {
                            $response['code'] = 11; // Internal Server error
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];

                            $this->log->Error('Erro tentando criar lista=' . $list . ' resposta=' . $resp);

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }


                    } else {

                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Info('Tentativa de criar lista existente user=' . $user->getName() . ' lista=' . $list);

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }

                } else {

                    $response['code'] = 14; // Permission denied
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                // You can only create individual itens

                $response['code'] = 15;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;


            }


        }
        $this->methodNotAllowedResponse();
    }

    public function performDelete($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \gcc\logFactory::getLogger();
            $log->Debug("Deleting list with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $listsm = listsManager::get_instance();
        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'system') {

            //Systems / Apps cannot delete

            $response['code'] = 14; // Permission denied
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {

                $list = $arguments['resource1'];

                if (permissionTools::validate(array('admin' => true, 'global:lists' => 'writer', 'list:' . $list => 'writer'), $user)) {

                    if ($list == '' || $list == null) {
                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Debug('Tentativa de deleção inválida user=' . $user->getName());

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;

                    }

                    $listObj = $listsm->find($list);

                    if ($listObj == null) {
                        $response['code'] = 15; //Conflict
                        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                        $response['data'] = $this->api_response_code[$response['code']]['Message'];

                        $this->log->Info('Tentativa de apagar lista inexistente user=' . $user->getName() . ' lista=' . $list);

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {

                        $resp = $listsm->delete($list);

                        if ($resp == 1) {

                            $response['code'] = 1; // 200 OK
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = "Deleted";

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;

                        } else {
                            $response['code'] = 11; // Internal Server error
                            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                            $response['data'] = $this->api_response_code[$response['code']]['Message'];

                            $this->log->Error('Erro tentando apagar lista=' . $list . ' resposta=' . $resp);

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }
                    }

                } else {

                    $response['code'] = 14; // Permission denied
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                // You can only delete individual itens

                $response['code'] = 15;
                $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                $response['data'] = $this->api_response_code[$response['code']]['Message'];

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;


            }


        }
        $this->methodNotAllowedResponse();
    }


} 