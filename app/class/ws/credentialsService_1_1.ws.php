<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 03/01/17
 * Time: 14:08
 */

namespace ccm\ws;

use ccm\appsManager;
use ccm\corruptDataEX;
use ccm\credential;
use ccm\listsManager;
use ccm\loginData;
use ccm\serversManager;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\userAccountManager;

require_once ROOT . "/class/credentialsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/class/loginData.class.php";

class credentialsService_1_1 extends credentialsService
{

    /**
     * Get the data.
     *
     * @param $url
     * @param $arguments
     * @param $accept
     * @return array
     * @throws corruptDataEX 1- No environment lists
     */
    public function performGet($url, $arguments, $accept)
    {
        $log = \ccm\logFactory::getLogger();
        $log->Trace("E->CredentialsService");

        $log->Debug("Searching credentials ", [ 'token'=> $arguments['token'], "arguments" => $arguments ]);


        $credM = \ccm\credentialsManager::get_instance();
        $token = $this->getToken($arguments);

        $ld = new loginData();
        $ld->token = $token;

        $envs = null;

        if ($ld->token->getTokenType() == 'app') {
            $appsM = appsManager::get_instance();
            $ld->app = $appsM->find($ld->token->getUserName());
            $ld->valid = true;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find($ld->token->getUserName());
                $ld->valid = true;
            }
        }
        if (!$ld->valid) {
            $log->Warning("CredentialsService - Auth Required");
            $response = $this->quickResponse(5); // Auth Required

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }

        $callType = 'global';
        $credName = '';

        if (array_key_exists('resource1', $arguments)) {

            $credName = $arguments['resource1'];

            if ($credName == '' || $credName == null) {

                $log->Warning("CredentialsService - Request malformed");
                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $callType = 'individual';
        }

        if ($callType == 'global') {
            $all = false;
            $credentials = array();

            if ($ld->token->getTokenType() == 'app') {
                $credentials = $credM->findAllByApp($ld->app->getName());
            }
            if ($ld->token->getTokenType() == 'user') {

                if (permissionTools::validate(array('admin' => true, 'global:apps' => 'reader'), $ld->user)) $all = true;
                else {
                    $listM = listsManager::get_instance();

                    $envList = $listM->find('Environments');

                    if ($envList == null) throw new corruptDataEX('The environments list must exist', 1);

                    while ($envList->current() != null) {
                        $allowedPerms[] = $envList->Current()->data . ':reader';
                        $allowedPerms[] = $envList->Current()->data . ':writer';
                        $envList->next();
                    }

                    $allowedPerms[] = 'reader';
                    $allowedPerms[] = 'writer';

                    $apps = permissionTools::getAppsWithPermissions($ld->user, $allowedPerms);

                    foreach ($apps as $key => $value) {
                        $subcred = $credM->findAllByApp($key);
                        $credentials = array_merge($credentials, $subcred);
                    }
                }
            }

            if ($all) {
                if(array_key_exists("app", $arguments)){

                    if(!is_string($arguments['app'])){
                        $log->Warning("Invalid parameter attempt app!");
                        $response = $this->quickResponse(10); // Invalid format

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    if( strpos($arguments['app'] , '"')  ){
                        $log->Warning("Invalid parameter attempt app!");
                        $response = $this->quickResponse(10); // Invalid format

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $credentials = $credM->findAllByApp($arguments['app']);
                }else {
                    $credentials = $credM->getList()->readList();
                }
            }
            $i = 1;
            $resp2 = array();
            foreach ($credentials as $key => $value) {
                //$resp2['Credential-' . $i] = strTools::removeSpaces($value->getName());
                $resp2[] = strTools::removeSpaces($value->getName());
                $i++;
            }

            $log->Info("CredentialsService - Listing required");
            $response = $this->quickResponse(1, $resp2); // OK

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        } else {

            // individual
            $cred = $credM->find($credName);

            if ($cred == null) {
                $log->Warning("CredentialsService - message='Credential not found' credential=".$credName);
                $response = $this->quickResponse(7); // Not Found

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            } else {
                $auth = false;
                if ($ld->token->getTokenType() == 'app') {
                    // APP Access
                    if ($cred->getAppName() != $ld->app->getName()) {
                        $log->Warning("CredentialsService - message='Permission denied' credential=".$credName. " app=".$ld->app->getName());
                        $response = $this->quickResponse(14); // Permission Denied

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $auth = true;
                        // Now let's check if we have a server with the right ip address

                        $srvM = serversManager::get_instance();

                        $srvs = $srvM->findByAppNIP($ld->app->getName(), $arguments['cipaddr']);

                        if (count($srvs) <= 0) {
                            $log->Warning("CredentialsService - Permission denied - IP", ['credential' => $credName, 'ip' => $arguments['cipaddr']]);
                            $response = $this->quickResponse(14); // Permission Denied
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }

                        $envs = array();

                        foreach ($srvs as $key => $value) {
                            $envs = array_merge($envs, $value->getAssignedEnv($ld->app->getName()));
                        }

                    }
                }
                if ($ld->token->getTokenType() == 'user') {

                    $parray = permissionTools::getAutoPermArray($cred->getAppName(), 'app', true);

                    // User Access
                    if (!permissionTools::validate($parray, $ld->user)) {
                        $log->Warning("CredentialsService - Permission denied", ['credential' => $credName, 'user' => $ld->user]);
                        $response = $this->quickResponse(14); // Permission Denied

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $auth = true;
                        $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $cred->getAppName(), 'app');
                    }
                }

                if ($envs != null) $cred->setDisplayEnvs($envs);


                if(array_key_exists('displayValues',$arguments) && $arguments['displayValues'] == 'true') {
                    $log->Debug("CredentialsService - DisplayValues ", ['credential' => $credName, 'displayValues' =>$arguments['displayValues']]);
                    $cred->setDisplayVaultValues(true);
                }else{
                    $log->Debug("CredentialsService - DisplayValues ", ['credential' => $credName, 'displayValues' => 'NULL']);
                }

                if ($auth == true) {
                    $log->Info("CredentialsService - Credential required ok", ['credential' => $credName]);
                    $response = $this->quickResponse(1, $cred); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }
            }
        }


        $this->methodNotAllowedResponse();
    }




} 