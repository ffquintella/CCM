<?php
/**
     THIS SERIES OF CLASSES ARE ONLY TO BE USED IN DATA MIGRATION!!!
 */

namespace gcc;


/**
 * Class userAccount
 * @package gcc
 */
class userAccount extends account
{

    protected $permArray = array();

    protected $authentication = 'local';


    public function setUserAuthentication(string $authentication)
    {

        $this->authentication = $authentication;
    }


    public function addPermission($permArray)
    {
        $this->permArray = array_merge($this->permArray, $permArray);
        return $this;
    }

    /**
     * Gets all the permissions this user has
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permArray;
    }

    /***
     * @param string $permission
     * @param bool $ignore_env
     * @return bool|mixed
     */
    public function hasPermission(string $permission, bool $ignore_env = false)
    {
        $perm = strtolower($permission);
        if($ignore_env){
            if(tools\arrayTools::array_key_startsWith(array_change_key_case($this->permArray, CASE_LOWER), $perm)){

                $parr = explode(":", $perm);

                if(sizeof($parr) < 2) throw new wrongFunctionParameterEX('The permission cannot be null', 1);

                $results = array();

                foreach ($this->permArray as $permKey => $permVal){
                    if(tools\strTools::startsWith(strtolower($permKey), $perm)){
                        $results[] = $permVal;
                    }
                }


                return $results;
            }

        }else{
            if (array_key_exists($perm, array_change_key_case($this->permArray, CASE_LOWER))) {
                return $this->permArray[$perm];
            } else return false;
        }

    }


    public function cleanPermissions()
    {

        $this->permArray = array();

    }

    public function readData()
    {
        return array('name' => $this->name, 'permissions' => $this->permArray, 'authentication' => $this->getAuthentication());
    }

    /**
     * @return string
     */
    public function getAuthentication(): string
    {
        return $this->authentication;
    }
} 