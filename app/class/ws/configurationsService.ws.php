<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 03/01/17
 * Time: 14:08
 */

namespace gcc\ws;

use gcc\appsManager;
use gcc\configuration;
use gcc\corruptDataEX;
use gcc\listsManager;
use gcc\loginData;
use gcc\serversManager;
use gcc\tools\permissionTools;
use gcc\tools\strTools;
use gcc\userAccountManager;

require_once ROOT . "/class/configurationsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/class/loginData.class.php";

class configurationsService extends authenticatedService
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


        $log = \gcc\logFactory::getLogger();
        $log->Debug("Searching configurations ", ['token' => $arguments['token'], 'ip' => $arguments['cipaddr'] ]);
        //. $arguments['token'] . " ip:" . $arguments['cipaddr']);


        $confM = \gcc\configurationsManager::get_instance();
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
        $confName = '';

        if (array_key_exists('resource1', $arguments)) {

            $confName = $arguments['resource1'];

            if ($confName == '' || $confName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $callType = 'individual';
        }

        if ($callType == 'global') {
            $all = false;
            $configurations = array();

            if ($ld->token->getTokenType() == 'app') {
                $configurations = $confM->findAllByApp($ld->app->getName());
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
                        $subconfs = $confM->findAllByApp($key);
                        $configurations = array_merge($configurations, $subconfs);
                    }
                }
            }

            if ($all) {
                $configurations = $confM->getList()->readList();
            } else {

            }
            $i = 1;
            foreach ($configurations as $key => $value) {
                $resp2['Configuration-' . $i] = strTools::removeSpaces($value->getName());
                $i++;
            }

            $response = $this->quickResponse(1, $resp2); // OK

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;

        } else {

            // individual
            $conf = $confM->find($confName);

            if ($conf == null) {
                $response = $this->quickResponse(7); // Not Found

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            } else {
                $auth = false;
                if ($ld->token->getTokenType() == 'app') {
                    // APP Access
                    if ($conf->getAppName() != $ld->app->getName()) {
                        $response = $this->quickResponse(14); // Permission Denied

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $auth = true;
                        // Now let's check if we have a server with the right ip address

                        $srvM = serversManager::get_instance();
                        $srvs = $srvM->findByAppNIP($ld->app->getName(), $arguments['cipaddr']);

                        if (count($srvs) <= 0) {
                            $response = $this->quickResponse(14); // Permission Denied
                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }

                        $conf->setReplaceVars(true);

                        $envs = array();
                        foreach ($srvs as $key => $value) {
                            $envs = array_merge($envs, $value->getAssignedEnv($ld->app->getName()));
                        }

                    }
                }
                if ($ld->token->getTokenType() == 'user') {

                    $parray = permissionTools::getAutoPermArray($conf->getAppName(), 'app', true);

                    // User Access
                    if (!permissionTools::validate($parray, $ld->user)) {
                        $response = $this->quickResponse(14); // Permission Denied

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    } else {
                        $auth = true;
                        $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $conf->getAppName(), 'app');
                    }
                }

                if ($envs != null) $conf->setDisplayEnvs($envs);

                if ($auth == true) {
                    $response = $this->quickResponse(1, $conf); // OK

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

        $log = \gcc\logFactory::getLogger();
        $log->Debug("Updating configuration", ['token' => $arguments['token'], "ip" => $arguments['cipaddr']]);

        $confM = \gcc\configurationsManager::get_instance();
        $token = $this->getToken($arguments);

        $ld = new loginData();
        $ld->token = $token;

        $envs = null;

        if ($ld->token->getTokenType() == 'app') {
            $ld->valid = false;
        } else {
            if ($ld->token->getTokenType() == 'user') {
                $uaM = userAccountManager::get_instance();
                $ld->user = $uaM->find(strtolower($ld->token->getUserName()));
                $ld->valid = true;
            }
        }
        if (!$ld->valid) {
            $response = $this->quickResponse(5); // Auth Required

            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
            else return $response;
        }

        $callType = 'global';
        $confName = '';

        if (array_key_exists('resource1', $arguments)) {

            $confName = $arguments['resource1'];

            if ($confName == '' || $confName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $confO = $confM->find(strtolower($confName));
            if ($confO == null) {
                $response = $this->quickResponse(15, 'Configuration does not exists'); // Conflict

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

                //$parray = permissionTools::getAutoPermArray($confO->getAppName(), 'app', false, true);

                // User Access
                //TODO: Validate environments being used
                if (!permissionTools::adv_validate($ld->user, true, $confO->getAppName(), "any", \gcc\tools\validationTypes::CONF, \gcc\tools\validationPerms::WRITER))
                {
                //if (!permissionTools::validate($parray, $ld->user)) {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {
                    $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $confO->getAppName(), 'app');

                    if (!array_key_exists('body', $arguments)) {
                        // We need a body
                        $response = $this->quickResponse(15, 'Body is required'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }
                    $bo = json_decode($arguments['body'], true);

                    if (array_key_exists('app', $bo)) {
                        $confO->setAppName($bo['app']);
                    }

                    if (array_key_exists('type', $bo)) {
                        $response = $this->quickResponse(15, 'We cannot update app types'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    if (array_key_exists('values', $bo)) {
                        if (!is_array($bo['values'])) {
                            // Values must be an array
                            $response = $this->quickResponse(15, 'Values must be an array'); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }
                        foreach ($bo['values'] as $key => $value) {
                            if (in_array($key, $envs)) $confO->setValue($key, $value);
                        }
                    }


                    $confM->save($confO);
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

        $log = \gcc\logFactory::getLogger();
        //$log->Debug("Creating configurations with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        $log->Info("Creating configurations ", ['token' => $arguments['token'], 'ip' => $arguments['cipaddr'] ]);

        $confM = \gcc\configurationsManager::get_instance();
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
        $confName = '';

        if (array_key_exists('resource1', $arguments)) {

            $confName = $arguments['resource1'];

            if ($confName == '' || $confName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }

            if ($confM->find($confName) != null) {
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

                $bo = json_decode($arguments['body'], true);

                $parray = permissionTools::getAutoPermArray($bo['app'], 'app', false, true);

                $log->Debug("Authenticated user tring to create config", ['user' => $ld->user->getName(), 'permissions' => implode(':', $parray), 'app' => $confName ]);

                // User Access
                if (!permissionTools::validate($parray, $ld->user)) {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {
                    $auth = true;
                    $envs = permissionTools::getEnvironmentsUserHasPermission($ld->user, $bo['app'], 'app');

                    if (!array_key_exists('body', $arguments)) {
                        // We need a body
                        $response = $this->quickResponse(15, 'Body is required'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }


                    if (!array_key_exists('app', $bo)) {
                        // We need a body
                        $response = $this->quickResponse(15, 'There must be an app'); // Conflict

                        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                        else return $response;
                    }

                    $conf = new configuration($confName, $bo['app']);

                    if (array_key_exists('values', $bo)) {
                        if (!is_array($bo['values'])) {
                            // Values must be an array
                            $response = $this->quickResponse(15, 'Values must be an array'); // Conflict

                            if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                            else return $response;
                        }
                        foreach ($bo['values'] as $key => $value) {
                            if (in_array($key, $envs)) $conf->setValue($key, $value);
                        }
                    }


                    $confM->save($conf);
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
            $log = \gcc\logFactory::getLogger();
            $log->Debug("Deleting configuration with details: token" . $arguments['token'] . " ip:" . $arguments['cipaddr']);
        }

        $confM = \gcc\configurationsManager::get_instance();
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
        $confName = '';

        if (array_key_exists('resource1', $arguments)) {

            $confName = $arguments['resource1'];

            if ($confName == '' || $confName == null) {

                $response = $this->quickResponse(15); // Conflict

                if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                else return $response;
            }
            $confO = $confM->find($confName);
            if ($confO == null) {
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

                $parray = permissionTools::getAutoPermArray($confO->getAppName(), 'app', false, true);

                // User Access
                if (!permissionTools::validate($parray, $ld->user)) {
                    $response = $this->quickResponse(14); // Permission Denied

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                } else {

                    $confM->delete($confO->getName());
                    $response = $this->quickResponse(1); // OK

                    if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
                    else return $response;
                }

            }

        }

        $this->methodNotAllowedResponse();
    }


} 