<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 11/01/17
 * Time: 10:49
 */

function appsEnvironmentsLister($command, $prevResults):array
{

    if(!array_key_exists('input::appName', $prevResults)) throw new \cmdEngine\exceptions\invalidFormException(FORM_INVALID);


    $html_resp = curlHelper::execute($command, 'apps/'.$prevResults['input::appName'].'?format=json',array(404,200));

    if($html_resp['code'] == 404) {
        throw new \cmdEngine\exceptions\invalidFormException(FORM_INVALID);
    }

    $objResp = json_decode($html_resp['response'], true);

    return $objResp['environments'];


}