<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 03/01/17
 * Time: 14:08
 */

namespace ccm\ws;

use ccm\server;
use ccm\tools\houseKeeping;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\userAccountManager;
use ccm\vaultFactory;
use Predis\Command\ServerInfo;
use ccm\dom;

require_once ROOT . "/class/appsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/class/tools/houseKeeping.class.php";
require_once ROOT . "/class/dom/serverInfo.popo.php";

class toolsService_1_1 extends authenticatedService
{


    // If we get to theses methods the class is already authenticated
    public function performGet($url, $arguments, $accept)
    {

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Executing tools Service with token=" . $arguments['token'] . " ip=" . $arguments['cipaddr']);
        }

        $token = $this->getToken($arguments);
        if ($token->getTokenType() == 'app') {


            // Apps cannot use this service
            $response = $this->quickResponse(14); // Permission Denied

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;


        }
        if ($token->getTokenType() == 'user') {

            $user = userAccountManager::get_instance()->find($token->getUserName());

            if (array_key_exists('resource1', $arguments)) {  // Details view

                $tool= $arguments['resource1'];

                if ($tool == '' || $tool == null) {

                    // there is no empty tool
                    $response = $this->quickResponse(15); // Conflict

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

                if (permissionTools::validate(array('admin' => true), $user)) {
                    // OK we have permission

                    switch ($tool) {
                        case "listVaultKeys":
                            $vault = vaultFactory::getVault();

                            $keys = $vault->listVaultKeys();

                            $response = $this->quickResponse(1, $keys); // OK

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                            break;

                        case "cleanUpOldRelations":

                            $resp = houseKeeping::cleanUpOldRelations();

                            $response = $this->quickResponse(1, $resp); // OK

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                            break;

                        case "convertToLowerCase":

                            $resp = houseKeeping::convertToLowerCase();

                            $response = $this->quickResponse(1, $resp); // OK

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                            break;


                        default:
                            $response = $this->quickResponse(7); // Not Found

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                            break;
                    }


                } else {

                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            } else {

                if (permissionTools::validate(array('admin' => true), $user)) {

                    $resp[] = "listVaultKeys";
                    $resp[] = "cleanUpOldRelations";
                    $resp[] = "convertToLowerCase";

                    $response = $this->quickResponse(1, $resp); // OK

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