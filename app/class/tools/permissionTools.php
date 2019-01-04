<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 28/12/16
 * Time: 14:46
 */

namespace ccm\tools;


use ccm\user;
use ccm\userAccount;
use ccm\wrongFunctionParameterEX;

require_once ROOT . '/class/listsManager.class.php';
require_once ROOT . '/class/tools/validationTypes.enum.php';
require_once ROOT . '/class/tools/validationPerms.enum.php';

class permissionTools
{
    /**
 * @param array $accepted - Array of strings represented the accepted permissions
 * @param userAccount $user - The user being compared
 * @return bool
 *
 * @throws wrongFunctionParameterEX
 *                  1- Accepted cannot be empty
 */
    public static function validate(array $accepted, userAccount $user): bool
    {


        if (count($accepted) == 0) throw new wrongFunctionParameterEX('The accepted array cannot be empty on a permission validation', 1);


        foreach ($accepted as $key => $value) {
            if ($user->hasPermission($key) == $value) {
                return true;
            }
            if ($user->hasPermission($key) == str_replace('reader', 'writer', $value)) {
                return true;
            }
        }

        return false;
    }

    /***
     * @param userAccount $user
     * @param bool $accept_global
     * @param string $app
     * @param string $env
     * @param int $type - validationTypes
     * @param int $perm - validationPerms
     * @return bool
     * @throws wrongFunctionParameterEX
     */
    public static function adv_validate(userAccount $user, bool $accept_global = true, string $app , string $env, int $type , int $perm  ): bool
    {

        $admin = $user->hasPermission("admin");

        if($admin == "true") return true;

        //if($perm == null) throw new wrongFunctionParameterEX('The permission cannot be null', 1);

        if($accept_global){
            $pglobal = $user->hasPermission("global");
            switch ($perm){
                case validationPerms::ANY:
                    if($pglobal != false) return true;
                    break;
                case validationPerms::READER:
                    if($pglobal != false)
                    {
                        if($pglobal == "reader") return true;
                    }
                    break;
                case validationPerms::WRITER:
                    if($pglobal != false)
                    {
                        if($pglobal == "writer") return true;
                    }
                    break;
            }
        }

        // if we are not global we need an app
        if($app != null){
            $valPerm = "app:".strtolower($app);

            if($env != "null" && strtolower($env) != "any"){
                $valPerm .= ":". strtolower($env);
            }

            $papp = $user->hasPermission($valPerm, true);

            switch ($perm){
                case validationPerms::ANY:
                    if($papp != false) return true;
                    break;
                case validationPerms::READER:
                    if($papp != false)
                    {
                        switch ($type){
                            case validationTypes::ALL:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "reader") return true;
                                    }
                                }else{
                                    if($papp == "reader") return true;
                                }
                                break;
                            case validationTypes::CRED:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "cred:reader") return true;
                                        if($pvalue == "reader") return true;
                                    }
                                }else{
                                    if($papp == "cred:reader") return true;
                                    if($papp == "reader") return true;
                                }
                                break;
                            case validationTypes::CONF:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "conf:reader") return true;
                                        if($pvalue == "reader") return true;
                                    }
                                }else{
                                    if($papp == "conf:reader") return true;
                                    if($papp == "reader") return true;
                                }
                                break;
                        }
                    }
                    break;
                case validationPerms::WRITER:
                    if($papp != false)
                    {
                        switch ($type){
                            case validationTypes::ALL:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "writer") return true;
                                    }
                                }else{
                                    if($papp == "writer") return true;
                                }
                                break;
                            case validationTypes::CRED:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "cred:writer") return true;
                                        if($pvalue == "writer") return true;
                                    }
                                }else{
                                    if($papp == "cred:writer") return true;
                                    if($papp == "writer") return true;
                                }
                                break;
                            case validationTypes::CONF:
                                if(is_array($papp)){
                                    foreach ($papp as $pkey => $pvalue){
                                        if($pvalue == "conf:writer") return true;
                                        if($pvalue == "writer") return true;
                                    }
                                }else{
                                    if($papp == "conf:writer") return true;
                                    if($papp == "writer") return true;
                                }
                                break;
                        }
                    }
                    break;
            }

        }


        return false;
    }

    /**
     * Return an array of apps represented as app:AppName in permissions of a user
     *
     * @param userAccount $user
     * @param array $permissionValues
     *
     * @return array
     */
    public static function getAppsWithPermissions(userAccount $user, array $permissionValues): array
    {
        $result = array();

        $allPerms = $user->getPermissions();

        foreach ($allPerms as $perm => $value) {
            if (strTools::startsWith($perm, 'app:')) {
                if (in_array($value, $permissionValues)) {
                    $appName = explode(':', $perm)[1];
                    $result[$appName] = $value;
                }
            }
        }
        return $result;
    }

    public static function getAutoPermArray(string $name, string $type, bool $reader = true, bool $writer = false, string $permType = null )
    {

        $resp = array('admin' => true);

        if ($writer) {
            $resp['global:' . $type] = 'writer';
            $resp[$type . ':' . $name] = 'writer';
            if($permType != null && $permType != ""){
                $resp[$type . ':' . $name] = $permType.':writer';
            }
        } else {
            if ($reader) $resp['global:' . $type] = 'reader';
            if ($reader) $resp[$type . ':' . $name] = 'reader';
            if($permType != null && $permType != ""){
                $resp[$type . ':' . $name] = $permType.':reader';
            }
        }

        $listM = \ccm\listsManager::get_instance();
        $envList = $listM->find('environments');
        if ($envList == null) throw new \ccm\corruptDataEX('The environments list must exist', 1);

        while ($envList->current() != null) {

            if ($writer) {
                $resp[$type . ':' . $name . ':' . $envList->Current()->data] = 'writer';
                if($permType != null && $permType != ""){
                    $resp[$type . ':' . $name . ':' . $envList->Current()->data] = $permType.':writer';
                }
            }
            else if ($reader) {
                $resp[$type . ':' . $name . ':' . $envList->Current()->data] = 'reader';
                if($permType != null && $permType != ""){
                    $resp[$type . ':' . $name . ':' . $envList->Current()->data] = $permType.':reader';
                }
            }

            $envList->next();
        }

        return $resp;

    }

    public static function getEnvironmentsUserHasPermission(\ccm\userAccount $user, string $name, string $type, bool $writer = false): array
    {
        $envs = array();
        $all = false;

        if ($user->hasPermission('admin')) $all = true;

        if ($writer) {
            if ($user->hasPermission($type . ':' . $name) == 'writer') $all = true;
        } else {
            if ($user->hasPermission($type . ':' . $name) == 'writer' || $user->hasPermission($type . ':' . $name) == 'reader') $all = true;
        }

        $listM = \ccm\listsManager::get_instance();
        $envList = $listM->find('environments');
        if ($envList == null) throw new \ccm\corruptDataEX('The environments list must exist', 1);

        if ($all) {
            $envs = $envList->readList();
        } else {
            while ($envList->current() != null) {

                if ($writer) {
                    if ($user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'writer'
                        || $user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'conf:writer'
                        || $user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'cred:writer') $envs[] = $envList->Current()->data;
                } else {
                    if (($user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'reader'
                        ||$user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'conf:reader'
                        ||$user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'cred:reader') ||
                        ($user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'writer'
                        || $user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'conf:writer'
                        || $user->hasPermission($type . ':' . $name . ':' . $envList->Current()->data) == 'cred:writer')
                    ) $envs[] = $envList->Current()->data;
                }

                $envList->next();
            }
        }

        return $envs;
    }
}