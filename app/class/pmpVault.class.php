<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 09/01/17
 * Time: 17:18
 */

namespace ccm;

use ccm\tools\curlErrorEX;
use ccm\vaultObject;

require_once ROOT . "/interfaces/ivault.interface.php";
require_once ROOT . "/class/tools/curlHelper.class.php";
require_once ROOT . "/class/cacheManager.class.php";
require_once ROOT . "/class/pmpDataManager.class.php";
require_once ROOT . "/class/dom/pmpResource.popo.php";

class pmpVault implements ivault
{

    /**
     * @param string $resource
     * @return string
     * @throws corruptDataEX
     * @throws wrongFunctionParameterEX - 1- Could not get data from the vault
     *                                    2- Vault could not find the data
     */
    function getPassword(string $resource): string
    {

        $logger = logFactory::getLogger();

        $cacheM = new cacheManager();
        $cacheR = $cacheM->getCachedValue($resource);

        $sec = new Secure();

        if ($cacheR != null) {
            $logger->Debug("Getting value from cache");
            return $sec->decrypt($cacheR);
        } else {
            $resources = explode(':', $resource);

            $logger->Debug("Searching resource in vault resource=". $resources[0] ." account=".$resources[1]);

            if (count($resources) != 2){
                $logger->Error("Resources badly formated", $resources);
                throw new wrongFunctionParameterEX('Resources badly formated', 1);
            }

            $url = VAULT_SERVER2_URL . VAULT_BASE_URI . '/resources/' . $resources[0] . '/accounts/' . $resources[1] . '/password?AUTHTOKEN=' . VAULT_AUTHTOKEN;
            $logger->Debug("Vault url data=\"". $url ."\"");

            //$error = false;

            try {
                $resp = curlHelper::execute($url);
                $logger->Debug("Vault response ", $resp);
                $error = false;
            }catch (tools\curlErrorEX $ex){
                $error = true;
            }

            if ($error || $resp['code'] != 200) {

                $url = VAULT_SERVER1_URL . VAULT_BASE_URI . '/resources/' . $resources[0] . '/accounts/' . $resources[1] . '/password?AUTHTOKEN=' . VAULT_AUTHTOKEN;
                $logger->Debug("Vault url data=\"". $url ."\"");

                try {
                    $resp = curlHelper::execute($url);
                    $logger->Debug("Vault response ", $resp);
                    $error = false;
                }catch (tools\curlErrorEX $ex){
                    $error = true;
                }

                if ($error || $resp['code'] != 200) {
                    $logger->Error("Could not get data from the vault");
                    throw new corruptDataEX('Could not get data from the vault', 1);
                }
            }

            $respObj = json_decode($resp['response']);
            $logger->Debug("Vault response data",array($respObj));

            if ($respObj->operation->name == 'GET PASSWORD' && $respObj->operation->result->status == 'Success') {
                $logger->Debug("Got resource from vault ok resource=". $resources[0] ." account=".$resources[1]);
                $cacheM->setCachedValue($resource, $sec->encrypt($respObj->operation->Details->PASSWORD));
                return $respObj->operation->Details->PASSWORD;
            } else {
                $logger->Error("Cannot find data in vault resource=". $resources[0] ." account=".$resources[1]);
                throw new corruptDataEX('Vault could not find the data', 2);
            }

        }


    }

    /**
     * @return \ccm\vaultObject[]
     */
    function listVaultKeys()
    {
        $logger = logFactory::getLogger();
        $cacheM = new cacheManager();


        $logger->Debug("Listing all resources");

        //$resources = array();

        $vaults = array();


        $vaultsSer = $cacheM->getCachedValue("cache:PMP_Full_List");

        if($vaultsSer == null) {

            $resSer = $cacheM->getCachedValue("cache:PMP_Resource_List");

            if ($resSer == null) {

                $url = VAULT_SERVER2_URL . VAULT_BASE_URI . '/resources?AUTHTOKEN=' . VAULT_AUTHTOKEN;
                $logger->Debug("Vault url data=\"" . $url . "\"");


                try {
                    $resp = curlHelper::execute($url, array(200, 201),'GET','',200);
                    $logger->Debug("Vault response ", $resp);
                    $error = false;
                }catch (tools\curlErrorEX $ex){
                    $error = true;
                }


                if ($error || $resp['code'] != 200) {

                    $url = VAULT_SERVER1_URL . VAULT_BASE_URI . '/resources?AUTHTOKEN=' . VAULT_AUTHTOKEN;
                    $logger->Debug("Vault url data=\"" . $url . "\"");

                    try {
                        $resp = curlHelper::execute($url, array(200, 201),'GET','',200);
                        $logger->Debug("Vault response ", $resp);
                        $error = false;
                    }catch (tools\curlErrorEX $ex){
                        $error = true;
                    }

                    if ($error || $resp['code'] != 200) {
                        $logger->Error("Could not get data from the vault");
                        throw new corruptDataEX('Could not get data from the vault', 1);
                    }
                }

                $respObj = json_decode($resp['response']);
                $logger->Debug("Vault response data", array($respObj));


                if ($respObj->operation->result->status == 'Success') {
                    $logger->Debug("Resource list retrived itens=" . $respObj->operation->totalRows);

                    $resources = $respObj->operation->Details;

                    $cacheM->setCachedValue("PMP_Resource_List", serialize($resources));

                } else {
                    $logger->Error("Cannot find data in vault");
                    throw new corruptDataEX('Vault could not find the data', 2);
                }
            } else {
                $resources = unserialize($resSer);
            }

            usort($resources, "gcc\usortResources");

            $pdm = pmpDataManager::getInstance();

            //Now we have the resource list
            foreach ($resources as $resource) {
                $resource_id = $resource->{"RESOURCE ID"};
                $resource_name = $resource->{"RESOURCE NAME"};
                $resource_description = $resource->{"RESOURCE DESCRIPTION"};
                $resource_num_accounts = $resource->{"NOOFACCOUNTS"};

                $resDb = $pdm->findResource($resource_name);

                if($resDb == null){
                    // Creating and saving the resource
                    $resDb = new dom\pmpResource();
                    $resDb->id = $resource_id;
                    $resDb->name = $resource_name;
                    $resDb->num_accounts = $resource_num_accounts;
                    $resDb->need_refresh = false;

                    $pdm->saveResource($resDb);

                    $resDb->need_refresh = true;

                }else{

                    if($resource_num_accounts != $resDb->num_accounts){

                        $resDb->num_accounts = $resource_num_accounts;

                        $pdm->saveResource($resDb);

                        $resDb->need_refresh = true;
                    }else{

                        if($resDb->need_refresh == true){
                            $resDb->need_refresh == false;
                            $pdm->saveResource($resDb);
                        }

                    }

                }


                if($resDb->need_refresh) {
                    // Getting the accounts

                    $url = VAULT_SERVER2_URL . VAULT_BASE_URI . '/resources/' . $resource_id . '/accounts?AUTHTOKEN=' . VAULT_AUTHTOKEN;
                    $logger->Debug("Vault url data=\"" . $url . "\"");

                    $resp = curlHelper::execute($url, array(200, 201),'GET','',200);
                    $logger->Debug("Vault response ", $resp);

                    if ($resp['code'] != 200) {

                        $url = VAULT_SERVER1_URL . VAULT_BASE_URI . '/resources/' . $resource_id . '/accounts?AUTHTOKEN=' . VAULT_AUTHTOKEN;
                        $logger->Debug("Vault url data=\"" . $url . "\"");

                        $resp = curlHelper::execute($url, array(200, 201),'GET','',200);

                        $logger->Debug("Vault response ", $resp);

                        if ($resp['code'] != 200) {
                            $logger->Error("Could not get data from the vault");
                            throw new corruptDataEX('Could not get data from the vault', 1);
                        }
                    }

                    $acctOper = json_decode($resp['response']);


                    if ($acctOper->operation->result->status == 'Success') {

                        $accounts = $acctOper->operation->Details->{"ACCOUNT LIST"};

                        foreach ($accounts as $account) {
                            $acct_name = $account->{"ACCOUNT NAME"};
                            $acct_ID = $account->{"ACCOUNT ID"};
                            //$acct_pwid = $account->PASSWDID;

                            $vault = new vaultObject();
                            $vault->details = $resource_name . " ; " . $resource_description . " | " . $acct_name;
                            $vault->resource = $resource_id . ":" . $acct_ID;

                            $pdm->saveVault($vault);

                            $vaults[] = $vault;

                        }

                    } else {
                        $logger->Error("Cannot find data in vault");
                        throw new corruptDataEX('Vault could not find the data', 2);
                    }
                }

            }

            $vllist = $pdm->getVaultList();



            $vaults = array_merge($vaults, $vllist->readList());



            $cacheM->setCachedValue("PMP_Full_List", serialize($vaults));

        }else{
            $vaults = unserialize($vaultsSer);
        }

        /*$vault = new vaultObject();
        $vault->details = "dummy1";
        $vault->resource = "res1";

        $vaults[] = $vault;*/

        return $vaults;
    }
}

function usortResources($a, $b) {

    $result = strcmp($a->{"RESOURCE NAME"}, $b->{"RESOURCE NAME"});

    //var_dump($a);
    //var_dump($b);
    return $result;
}