<?php

/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 17:14
 */
class curlHelper
{

    /***
     * @param string $specificUrl - The specific URL to be used
     * @param string $method - the method to be used
     * @param string $body - the body
     * @param $acceptedReturns - Array containing the accepted return codes
     * @param $console
     *
     * @return array responsa array containing: code
     *                                          response
     */
    static function execute(ConsoleKit\Command $console, string $specificUrl, array $acceptedReturns = array(200,201), string $method = 'GET', string $body = '' ) :array
    {
        $result = array();

        // Get cURL resource
        $ch = curl_init();

        $url = urlManager::getbaseURL() . $specificUrl;


        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        if(INSECURE_SSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if(USE_DEBUG_PROXY){

            $proxy = DEBUG_PROXY.':'.DEBUG_PROXY_PORT;
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
            ]
        );

        if($method == 'POST' || $method == 'PUT'){
            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            //var_dump($body);
        }

        // Send the request & save response to $resp
        $resp = curl_exec($ch);


        $result['response'] = $resp;
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //var_dump($result);

        if ($result['code'] == 200 && !$resp) {
            $console->writeln("Erro on communication layer detected!");
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch) . "\n");
        } else {


            if ($result['code'] == 550) {
                $console->writeln("Permissão negada !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $console->writeln("Seu usuário não tem permissão para executar este comando.");
                // Close request to clear up some resources
                curl_close($ch);
                return $result;

            }
            if ( in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), $acceptedReturns) ) {
                // OK
                curl_close($ch);
                return $result;
            }

            $console->writeln("Erro no pedido!", \ConsoleKit\Colors::RED);
            $console->writeln('----');
            $console->writeln("Código : " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
            $console->writeln("Resposta : " . $resp);
            $console->writeln('_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-');
        }

        // Close request to clear up some resources
        curl_close($ch);

        return $result;

    }

}