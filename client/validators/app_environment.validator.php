<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 03/01/17
 * Time: 10:10
 */

function app_environmentValidator($console):array
{

    $app = $GLOBALS['subparam'];

    $html_resp = curlHelper::execute($console, 'apps/'.$app.'?format=json',array(200));

    //var_dump($html_resp);

    if($html_resp['code'] == 200) {

        return json_decode($html_resp['response'], true)['environments'];

    }else return array();

}