<?php

/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 27/12/16
 * Time: 12:07
 */
class validators
{
    /**
     * @param $password - The password bein set
     * @return bool - true if ok - false if not validated
     */
    public static function passwordComplexity($password){
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);

        if(!$uppercase || !$lowercase || !$number || strlen($password) < USER_PASS_SIZE) {
            return false;
        }else return true;
    }

}