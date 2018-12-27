<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 03/01/17
 * Time: 10:10
 */

function optionsValidator($console):array
{

    $html_resp = curlHelper::execute($console, 'lists/Environments?format=json',array(200));

    if($html_resp['code'] == 200) {

        return json_decode($html_resp['response']);

    }else return array();

}