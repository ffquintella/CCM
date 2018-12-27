<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 31/12/2016
 * Time: 12:15
 */

function appsNameValidator($console, $name):bool
{

    $html_resp = curlHelper::execute($console, 'apps/'.$name.'?format=json',array(404,200));

    if($html_resp['code'] == 200) {
        return true;
    }else return false;

}