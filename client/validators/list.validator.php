<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 31/12/2016
 * Time: 12:15
 */

function listNameValidator($console, $name):bool
{

    $html_resp = curlHelper::execute($console, 'lists/'.$name.'?format=json',array(404,200));

    if($html_resp['code'] != 404) {
        return false;
    }else return true;

}