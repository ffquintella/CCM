<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 03/01/17
 * Time: 10:10
 */

function appsValidator($console):array
{

    $html_resp = curlHelper::execute($console, 'apps?format=json',array(200));

    if($html_resp['code'] == 200) {

        $resp =  json_decode($html_resp['response'], true);

        $respArr = array();
        if(is_array($resp)) {
            foreach ($resp as $key => $value) {
                $respArr[] = $value;
            }
        }
        return $respArr;

    }else return array();

}