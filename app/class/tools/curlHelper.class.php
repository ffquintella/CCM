<?php


namespace gcc;

require_once "curlErrorEX.php";

/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 17:14
 */

class curlHelper
{

    /***
     * @param string $url - The specific URL to be used
     * @param string $method - the method to be used
     * @param string $body - the body
     * @param $acceptedReturns - Array containing the accepted return codes
     * @param $http_timeout - Timeout for http requisition
     *
     * @return array response array containing: code
     *                                          response
     */
    static function execute(string $url, array $acceptedReturns = array(200, 201), string $method = 'GET', string $body = '', int $http_timeout = HTTP_REQ_TIMEOUT): array
    {
        // Timeout do script
        set_time_limit($http_timeout + PHP_TIMEOUT);

        $result = array();

        // Get cURL resource
        $ch = curl_init();

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers

        // Timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $http_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $http_timeout);


        // IGNORE SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        /*curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
            ]
        );*/

        if ($method == 'POST' || $method == 'PUT') {
            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            //var_dump($body);
        }

        // Send the request & save response to $resp
        $resp = curl_exec($ch);

        $result['response'] = $resp;
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $logger = logFactory::getLogger();

        if (!$resp) {
            $logger->Error("Curl Error:". curl_error($ch) .'" - Code: ' . curl_errno($ch));
            //die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            throw new tools\curlErrorEX(curl_error($ch));
        } else {

        }

        // Close request to clear up some resources
        curl_close($ch);

        return $result;

    }

}