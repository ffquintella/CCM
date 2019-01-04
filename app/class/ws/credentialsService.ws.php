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

class credentialsService extends authenticatedService
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
            $ld->app = $appsM->find(strtolower($ld->token->getUserName()));
            $ld->valid = true;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find(strtolower($ld->token->getUserName()));
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
                $credentials = $credM->getList()->readList();
            } else {

            }
            $i = 1;
            foreach ($credentials as $key => $value) {
                $resp2['Credential-' . $i] = strTools::removeSpaces($value->getName());
                $i++;
            }

            $log->Info("CredentialsService - Listing required");
            $response = $this->quickResponse(1, $resp2); // OK

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        } else {

            // individual
            $cred = $credM->find(strtolower($credName));

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

                        $srvs = $srvM->findByAppNIP(strtolower($ld->app->getName()), $arguments['cipaddr']);

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

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Updating credentials with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $credM = \ccm\credentialsManager::get_instance();
        $token = $this->getToken($arguments);

        $ld = new loginData();
        $ld->token = $token;

        $envs = null;

        if ($ld->token->getTokenType() == 'app') {
            $ld->valid = false;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find($ld->token->getUserName());
                $ld->valid = true;
            }
        }
        if (!$ld->valid) {
            $response = $this->quickResponse(5); // Auth Required

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }

        $callType = 'global';
        $credName = '';

        if (array_key_exists('resource1', $arguments)) {

            $credName = $arguments['resource1'];

            if ($credName == '' || $credName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $credO = $credM->find($credName);
            if ($credO == null) {
                $response = $this->quickResponse(15, 'Credential does not exists'); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }

            $callType = 'individual';
        }

        if ($callType == 'global') {
            // We don't accept global posts
            $response = $this->quickResponse(15); // Conflict

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        } else {
            // Individual
            if ($ld->token->getTokenType() == 'user') {
                // Only users can do that

                $parray = permissionTools::getAutoPermArray($credName, 'app', false, true);

                // User Access
                if (!permissionTools::validate($parray, $ld->user)) {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {
                    $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $credName, 'app');

                    if (!array_key_exists('body', $arguments)) {
                        // We need a body
                        $response = $this->quickResponse(15, 'Body is required'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }
                    $bo = json_decode($arguments['body'], true);

                    if (array_key_exists('app', $bo)) {
                        $credO->setAppName($bo['app']);
                    }

                    if (array_key_exists('type', $bo)) {
                        $response = $this->quickResponse(15, 'We cannot update app types'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    if ($credO->getType() == 'local') {
                        if (array_key_exists('values', $bo)) {
                            if (!is_array($bo['values'])) {
                                // Values must be an array
                                $response = $this->quickResponse(15, 'Values must be an array'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                            foreach ($bo['values'] as $key => $value) {
                                if (in_array($key, $envs)) $credO->setValue($key, $value);
                            }
                        }
                    }
                    if ($credO->getType() == 'vault') {
                        if (array_key_exists('vaultIds', $bo)) {
                            if (!is_array($bo['vaultIds'])) {
                                // Values must be an array
                                $response = $this->quickResponse(15, 'vaultIds must be an array'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                            foreach ($bo['vaultIds'] as $key => $value) {
                                if (in_array($key, $envs)) $credO->setVaultId($key, $value);
                            }
                        }
                    }

                    $credM->save($credO);
                    $response = $this->quickResponse(1); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }

        }

        $this->methodNotAllowedResponse();
    }

    public function performPut($url, $arguments, $accept)
    {

        $log = \ccm\logFactory::getLogger();

        $log->Trace("CredentialsService - stage=entering");
        $log->Debug("CredentialsService - message='Creating credentials' token=" . $arguments['token'] . " ip=" . $arguments['cipaddr']);

        $credM = \ccm\credentialsManager::get_instance();
        $token = $this->getToken($arguments);

        $ld = new loginData();
        $ld->token = $token;

        $envs = null;

        if ($ld->token->getTokenType() == 'app') {
            $ld->valid = false;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find($ld->token->getUserName());
                $ld->valid = true;
            }
        }
        if (!$ld->valid) {
            $log->Warning("CredentialsService - message='Auth Required' ip=" . $arguments['cipaddr']);
            $response = $this->quickResponse(5); // Auth Required

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }

        $callType = 'global';
        $credName = '';

        if (array_key_exists('resource1', $arguments)) {

            $credName = $arguments['resource1'];

            if ($credName == '' || $credName == null) {

                $log->Warning("CredentialsService - message='Empty credential name' ip=" . $arguments['cipaddr']);
                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }

            if ($credM->find($credName) != null) {
                $log->Warning("CredentialsService - message='Credential already exists' ip=" . $arguments['cipaddr']);
                $response = $this->quickResponse(15, 'Credential already exists'); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }

            $callType = 'individual';
        }

        if ($callType == 'global') {
            // We don't accept global posts
            $response = $this->quickResponse(15); // Conflict

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        } else {
            // Individual
            if ($ld->token->getTokenType() == 'user') {
                // Only users can do that

                $parray = permissionTools::getAutoPermArray($credName, 'app', false, true);

                // User Access
                if (!permissionTools::validate($parray, $ld->user)) {
                    $log->Warning("CredentialsService - message='Permission denied' ip=" . $arguments['cipaddr']. " user=".$ld->user->getName());
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {
                    $auth = true;
                    $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $credName, 'app');

                    if (!array_key_exists('body', $arguments)) {
                        // We need a body
                        $log->Warning("CredentialsService - message='Body is required' ip=" . $arguments['cipaddr']);
                        $response = $this->quickResponse(15, 'Body is required'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }
                    $bo = json_decode($arguments['body'], true);

                    if (!array_key_exists('app', $bo)) {
                        // We need a body
                        $log->Warning("CredentialsService - message='There must be an app' ip=" . $arguments['cipaddr']);
                        $response = $this->quickResponse(15, 'There must be an app'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    if (!array_key_exists('type', $bo)) {
                        $bo['type'] = 'local';
                    }

                    $cred = new credential(strtolower($credName), $bo['app'], $bo['type']);

                    if ($bo['type'] == 'local') {
                        if (array_key_exists('values', $bo)) {
                            if (!is_array($bo['values'])) {
                                // Values must be an array
                                $log->Warning("CredentialsService - message='Values must be an array' ip=" . $arguments['cipaddr']);
                                $response = $this->quickResponse(15, 'Values must be an array'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                            foreach ($bo['values'] as $key => $value) {
                                if (in_array($key, $envs)) $cred->setValue($key, $value);
                            }
                        }
                    }
                    if ($bo['type'] == 'vault') {
                        if (array_key_exists('vaultIds', $bo)) {
                            if (!is_array($bo['vaultIds'])) {
                                // Values must be an array
                                $log->Warning("CredentialsService - message='vaultIds must be an array' ip=" . $arguments['cipaddr']);
                                $response = $this->quickResponse(15, 'vaultIds must be an array'); // Conflict

                                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                                else return $response;
                            }
                            foreach ($bo['vaultIds'] as $key => $value) {
                                if (in_array($key, $envs)) $cred->setVaultId($key, $value);
                            }
                        }
                    }

                    $credM->save($cred);
                    $log->Info("CredentialsService - message='Credential created' ip=".$arguments['cipaddr'] ." user=".$ld->user->getName()." credential=".$credName);
                    $response = $this->quickResponse(2); // Created

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }

        }

        $this->methodNotAllowedResponse();
    }

    public function performDelete($url, $arguments, $accept)
    {
        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = \ccm\logFactory::getLogger();
            $log->Debug("Deleting credentials with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $credM = \ccm\credentialsManager::get_instance();
        $token = $this->getToken($arguments);

        $ld = new loginData();
        $ld->token = $token;

        $envs = null;

        if ($ld->token->getTokenType() == 'app') {
            $ld->valid = false;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find($ld->token->getUserName());
                $ld->valid = true;
            }
        }
        if (!$ld->valid) {
            $response = $this->quickResponse(5); // Auth Required

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }

        $callType = 'global';
        $credName = '';

        if (array_key_exists('resource1', $arguments)) {

            $credName = $arguments['resource1'];

            if ($credName == '' || $credName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $credO = $credM->find($credName);
            if ($credO == null) {
                $response = $this->quickResponse(15, 'Credential does not exists'); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }

            $callType = 'individual';
        }

        if ($callType == 'global') {
            // We don't accept global posts
            $response = $this->quickResponse(15); // Conflict

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        } else {
            // Individual
            if ($ld->token->getTokenType() == 'user') {
                // Only users can do that

                $parray = permissionTools::getAutoPermArray($credName, 'app', false, true);

                // User Access
                if (!permissionTools::validate($parray, $ld->user)) {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {

                    $credM->delete($credO->getName());
                    $response = $this->quickResponse(1); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }

        }

        $this->methodNotAllowedResponse();
    }


} 