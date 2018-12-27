<?php

/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 18:08
 */
class urlManager
{
    public static function getbaseURL(){

        $url = '';

        if(USE_SRVC){

            $result = dns_get_record(SRVC_DNS, DNS_SRV);

            //var_dump($result);

            foreach ($result as $key => $host){


                if(SSL) $iurl = "https://";
                else $iurl = "http://";

                $iurl .= $host['target'];

                $iurl .= ':'. $host['port'];

                $burl = $iurl . '/api/ping';

                //var_dump($burl);

                $resp = urlManager::verify($burl);

                if($resp['code'] == 200 && $resp['response'] == "OK") {
                    $url = $iurl.'/api/';
                    break;
                }

            }

            if($url == ''){
                echo 'ERROR CONNECTING TO SERVER';
                exit();
            }

        }else {
            //'http://localhost:8033/api/authenticationLogin?format=json'
            if (SSL) $schema = 'https';
            else $schema = 'http';

            $url = $schema . "://" . SERVER_ADDR . ':' . SERVER_PORT . '/api/';
        }
        return $url;
    }

    public static function verify(string $url){
        // Get cURL resource
        $ch = curl_init();

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        // Send the request & save response to $resp
        $resp = curl_exec($ch);

        $result['response'] = $resp;
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close request to clear up some resources
        curl_close($ch);

        return $result;
    }
}