<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 26/12/2017
 * Time: 17:37
 */

namespace gcc\tools;

include_once ROOT . "/baseincludes.php";
include_once ROOT . "/class/sharedStorageFactory.class.php";
include_once ROOT . "/class/secure.class.php";

use gcc\appsManager;
use gcc\configurationsManager;
use gcc\credentialsManager;
use gcc\serversManager;

class houseKeeping
{
    static function cleanUpOldRelations():array
    {
        $ret = array();

        $ret[] = "Starting operation";

        $SM = serversManager::get_instance();
        $AM = appsManager::getInstance();
        $CRM = credentialsManager::getInstance();
        $CNFM = configurationsManager::getInstance();


        $ret[] = "Analizing the servers...";
        $servers = $SM->getList();

        while($servers->current() != null){
            $server = $servers->current()->data;
            $ret[] = "Processing server: ". $server->getName();

            $assg = $server->getAssignments();

            foreach ($assg as $app => $env){
                $appObj = $AM->find($app);

                if($appObj == null) {
                    $ret[] = "App ". $app ." relation found in server ". $server->getName() . " but no app found. Deleting relation ...";
                    $server->unassign($app);
                    $SM->save($server);
                }
            }

            $servers->next();
        }

        $ret[] = "Analizing the credentials...";
        $credentials = $CRM->getList();

        while($credentials->current() != null){
            $credential = $credentials->current()->data;
            $ret[] = "Processing credential: ". $credential->getName();

            $app = $credential->getAppName();
            $appObj =  $AM->find($app);

            if($appObj == null) {
                $ret[] = "A credential ". $credential->getName() . " whas found belonging to the app " . $app . " but no app found. Deleting credential ...";
                $CRM->delete($credential->getName());
            }

            $credentials->next();
        }

        $ret[] = "Analizing the configurations...";
        $configurations = $CNFM->getList();

        while($configurations->current() != null){
            $configuration = $configurations->current()->data;
            $ret[] = "Processing configuration: ". $configuration->getName();

            $app = $configuration->getAppName();
            $appObj =  $AM->find($app);

            if($appObj == null) {
                $ret[] = "A configuration ". $configuration->getName() . " whas found belonging to the app " . $app . " but no app found. Deleting credential ...";
                $CNFM->delete($configuration->getName());
            }

            $configurations->next();
        }


        $ret[] = "Operation ended";

        return $ret;
    }

    static function convertToLowerCase():array
    {
        $results = array();
        $s = 0;
        $results[date("Y-m-d_H:i:s")."-". $s] = "Starting operation"; $s++;

        $ss = \gcc\sharedStorageFactory::getSharedStorage();

        $ss->connectSingle();

        $ping = $ss->ping();

        if($ping) {
            $results[date("Y-m-d_H:i:s")."-". $s] = "Connection OK"; $s++;
        }
        else {
            $results[date("Y-m-d_H:i:s")."-". $s] = "Error openning connection"; $s++;
            return $results;
        }

        $results[date("Y-m-d_H:i:s")."-". $s] = "Getting all keys"; $s++;

        // PROCESSING KEYS
        $keys = $ss->getPattern("*");

        $results[date("Y-m-d_H:i:s")."-". $s] = "Found ".count($keys)." keys"; $s++;

        $keyRenames = 0;

        foreach ($keys as $ind => $value){
            $results[date("Y-m-d_H:i:s")."-". $s] = "Processing key N:".$ind ; $s++;
            $results[date("Y-m-d_H:i:s")."-". $s] = "Key name:".$value ; $s++;

            if($value != strtolower($value)){
                $results[date("Y-m-d_H:i:s")."-". $s] = "Renaming name:".$value. " to:".strtolower($value) ; $s++;
                $ss->rename($value, strtolower($value));
                $keyRenames++;
            }

        }

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing References..."; $s++;

        // PROCESSING REFERENCES
        $refs = $ss->getPattern("ref:*");

        $refRenames = 0;
        foreach ($refs as $id => $ref){
            $results[date("Y-m-d_H:i:s")."-". $s] = "Processing reference:".$ref ; $s++;
            $type = $ss->type($ref);
            if($type == "string"){
                $val = $ss->get($ref);

                if($val != strtolower($val)) {
                    $results[date("Y-m-d_H:i:s") . "-" . $s] = "Renaming val:" . $val ." to:".strtolower($val);$s++;
                    $ss->set($ref, strtolower($val));
                    $refRenames++;
                }


            }
            if($type == "set"){
                $refVals = $ss->getSet($ref);

                foreach ($refVals as $key => $val){
                    if($val != strtolower($val)){
                        $results[date("Y-m-d_H:i:s") . "-" . $s] = "Renaming val:" . $val ." to:".strtolower($val);$s++;
                        $ss->delSet($ref, $val);
                        $ss->putSet($ref, strtolower($val));
                        $refRenames++;
                    }
                }

            }

        }

        // PROCESSING indexes

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Indexes..."; $s++;
        $indexes = $ss->getPattern("index:*");

        $indexRenames = 0;
        foreach ($indexes as $id => $ind) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing index:" . $ind; $s++;

            $indVals = $ss->getSet($ind);

            foreach ($indVals as $key => $val){
                if($val != strtolower($val)){
                    $results[date("Y-m-d_H:i:s") . "-" . $s] = "Renaming val:" . $val ." to:".strtolower($val);$s++;
                    $ss->delSet($ind, $val);
                    $ss->putSet($ind, strtolower($val));
                    $indexRenames++;
                }
            }
        }

        // Processing Lists

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Lists..."; $s++;
        $lists = $ss->getPattern("list:*");

        $sec = new \gcc\Secure();
        $listRenames = 0;
        foreach ($lists as $id => $list) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $list; $s++;

            $lcrypt = $ss->get($list);

            $lobj = unserialize($sec->decrypt($lcrypt));

            $newList = new \gcc\linkedList();

            while($lobj->current() != null){
                $ldata = $lobj->current()->data;

                $newList->insertLast(strtolower($ldata));

                $lobj->next();

                if($ldata != strtolower($ldata)) $listRenames++;
            }

            $ss->set($list, $sec->encrypt(serialize($newList)));

        }

        // PROCESSING USERS

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Users..."; $s++;
        $users = $ss->getPattern("user:*");

        $sec = new \gcc\Secure();
        $userRenames = 0;
        foreach ($users as $id => $user) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $user; $s++;

            $ucrypt = $ss->get($user);

            $uobj = unserialize($sec->decrypt($ucrypt));

            $newPerms = array();
            $renamed = false;
            foreach ($uobj->getPermissions() as $pkey => $pval){

                $newPerms[strtolower($pkey)] = strtolower($pval);

                if($pkey != strtolower($pkey)) {
                    $userRenames++;
                    $renamed = true;
                }
                if(!$renamed && $pval != strtolower($pval)){
                    $userRenames++;
                    $renamed = true;
                }

            }

            if($renamed){
                $uobj->cleanPermissions();
                $uobj->addPermission($newPerms);
                $ss->set($user, $sec->encrypt(serialize($uobj)));
            }


        }

        // PROCESSING Apps

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Configurations..."; $s++;
        $apps = $ss->getPattern("app:*");

        $sec = new \gcc\Secure();
        $appRenames = 0;
        foreach ($apps as $id => $app) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $app; $s++;

            $acrypt = $ss->get($app);

            $aobj = unserialize($sec->decrypt($acrypt));

            $renamed = false;

            if($aobj->getName() != strtolower($aobj->getName())){
                $renamed = true;
                $appRenames++;
                $aobj->setName(strtolower($aobj->getName()));
            }

            if($aobj->getOwner() != strtolower($aobj->getOwner())){
                if(!$renamed){
                    $renamed = true;
                    $appRenames++;
                }
                $aobj->setOwner(strtolower($aobj->getOwner()));
            }

            $envs = $aobj->getEnvironments();

            $aobj->cleanEnvironments();

            $aobj->reloadAvaliableEnvironments();

            while($envs->current() != null){
                $edata = $envs->current()->data;

                if($edata != strtolower($edata)){
                    if(!$renamed){
                        $renamed = true;
                        $appRenames++;
                    }
                }

                $aobj->addEnvironment(strtolower($edata));

                $envs->next();

            }

            if($renamed){
                $ss->set($app, $sec->encrypt(serialize($aobj)));
            }

        }




        // PROCESSING Configurations

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Configurations..."; $s++;
        $configs = $ss->getPattern("configuration:*");

        $sec = new \gcc\Secure();
        $confRenames = 0;
        foreach ($configs as $id => $config) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $config; $s++;

            $ccrypt = $ss->get($config);

            $cobj = unserialize($sec->decrypt($ccrypt));


            $renamed = false;

            if($cobj->getName() != strtolower($cobj->getName())){
                $renamed = true;
                $confRenames++;
                $cobj->setName(strtolower($cobj->getName()));
            }

            if($cobj->getAppName() != strtolower($cobj->getAppName())){
                if(!$renamed){
                    $renamed = true;
                    $confRenames++;
                }

                $cobj->setAppName(strtolower($cobj->getAppName()));
            }

            $newValues = array();

            foreach ($cobj->getValues() as $ckey => $cval){

                if($ckey != strtolower($ckey)){
                    if(!$renamed){
                        $renamed = true;
                        $confRenames++;
                    }
                }
                $newValues[strtolower($ckey)] = $cval;
            }

            $cobj->clearValues();

            foreach ($newValues as $nkey => $nval){
                $cobj->setValue($nkey, $nval);
            }

            if($renamed){
                $ss->set($config, $sec->encrypt(serialize($cobj)));
            }


        }


        // PROCESSING Credentials

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Credentials..."; $s++;
        $creds = $ss->getPattern("credential:*");

        $sec = new \gcc\Secure();
        $credRenames = 0;
        foreach ($creds as $id => $cred) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $cred; $s++;

            $crecrypt = $ss->get($cred);

            $creobj = unserialize($sec->decrypt($crecrypt));


            $renamed = false;

            if($creobj->getName() != strtolower($creobj->getName())){
                $renamed = true;
                $credRenames++;
                $creobj->setName(strtolower($creobj->getName()));
            }

            if($creobj->getAppName() != strtolower($creobj->getAppName())){
                if(!$renamed){
                    $renamed = true;
                    $credRenames++;
                }

                $creobj->setAppName(strtolower($creobj->getAppName()));
            }

            $newValues = array();

            foreach ($creobj->getValues() as $crekey => $creval){

                if($crekey != strtolower($crekey)){
                    if(!$renamed){
                        $renamed = true;
                        $credRenames++;
                    }
                }
                $newValues[strtolower($crekey)] = $creval;
            }

            $creobj->clearValues();

            foreach ($newValues as $nkey => $nval){
                $creobj->setValue($nkey, $nval);
            }

            if($renamed){
                $ss->set($cred, $sec->encrypt(serialize($creobj)));
            }


        }


        // PROCESSING Servers

        $results[date("Y-m-d_H:i:s")."-". $s] = "Now processing Credentials..."; $s++;
        $servs = $ss->getPattern("server:*");

        $sec = new \gcc\Secure();
        $servRenames = 0;
        foreach ($servs as $id => $serv) {
            $results[date("Y-m-d_H:i:s") . "-" . $s] = "Processing " . $serv; $s++;

            $scrypt = $ss->get($serv);

            $sobj = unserialize($sec->decrypt($scrypt));

            $renamed = false;

            if($sobj->getName() != strtolower($sobj->getName())){
                $servRenames++;
                $renamed = true;
                $sobj->setName(strtolower($sobj->getName()));
            }

            $assigns = $sobj->getAssignments();
            $newassign = array();
            foreach ($assigns as $app => $envs){
                if($app != strtolower($app)){
                    if(!$renamed){
                        $servRenames++;
                        $renamed = true;
                    }
                }


                $newassign[strtolower($app)] = arrayTools::array_change_value_case($envs, CASE_LOWER);
            }

            $sobj->cleanAssignments();

            foreach ($newassign as $app => $envs){

                foreach ($envs as $key => $env) {
                    $sobj->assign($app, $env);
                }
            }


            if($renamed){
                $ss->set($serv, $sec->encrypt(serialize($sobj)));
            }


        }

        //$refVals = $ss->getSet($ref);

        $results[date("Y-m-d_H:i:s")."-". $s] = "Stats ###### "; $s++;

        $results[date("Y-m-d_H:i:s")."-". $s] = "- keyRenames:".$keyRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- refRenames:".$refRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- indexRenames:".$indexRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- listRenames:".$listRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- userRenames:".$userRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- appRenames:".$appRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- confRenames:".$confRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- credRenames:".$credRenames; $s++;
        $results[date("Y-m-d_H:i:s")."-". $s] = "- servRenames:".$servRenames; $s++;

        $results[date("Y-m-d_H:i:s")."-". $s] = "Operation finished"; $s++;

        return $results;
    }
}