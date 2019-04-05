<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 03/01/17
 * Time: 14:08
 */

namespace ccm\ws;

use ccm\appsManager;
use ccm\configuration;
use ccm\corruptDataEX;
use ccm\listsManager;
use ccm\loginData;
use ccm\serversManager;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\tools\validationPerms;
use ccm\tools\validationTypes;
use ccm\userAccountManager;

require_once ROOT . "/class/configurationsManager.class.php";
require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/class/loginData.class.php";

class configurationsService_1_1 extends configurationsService
{


    public function performGet($url, $arguments, $accept)
    {

        $log = \ccm\logFactory::getLogger();
        $log->Debug("Searching configurations ", ['token' => $arguments['token'], 'ip' => $arguments['cipaddr'] ]);

        $confM = \ccm\configurationsManager::get_instance();
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

                if (permissionTools::validate(array('admin' => true, 'global:conf' => 'reader'), $ld->user)) $all = true;
                else {
                    /*$listM = listsManager::get_instance();

                    $envList = $listM->find('Environments');

                    if ($envList == null) throw new corruptDataEX('The environments list must exist', 1);

                    while ($envList->current() != null) {
                        $allowedPerms[] = $envList->Current()->data . ':reader';
                        $allowedPerms[] = $envList->Current()->data . ':writer';
                        $envList->next();
                    }*/

                    $allowedPerms[] = 'conf:reader';
                    $allowedPerms[] = 'conf:writer';
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

                    $configurations = $confM->findAllByApp($arguments['app']);
                }else {
                    $configurations = $confM->getList()->readList();
                }


                //$configurations = $confM->getList()->readList();
            } else {

            }

            $i = 1;
            foreach ($configurations as $key => $value) {
                //$resp2['Configuration-' . $i] = strTools::removeSpaces($value->getName());

                if($value == null){
                    //$log->Error("Error processing null value for key" . $key , ["configurations" => print_r($configurations, true) ]);
                }else {
                    $resp2[] = strTools::removeSpaces($value->getName());
                }
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

                    //$parray = permissionTools::getAutoPermArray($conf->getAppName(), 'app', true, false, 'conf');

                    // User Access
                    //if (!permissionTools::validate($parray, $ld->user)) {
                    if (!permissionTools::adv_validate($ld->user, $conf->getAppName(), "any", validationTypes::CONF, validationPerms::ANY, true))
                    {
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


} 