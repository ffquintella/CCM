<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 10/03/14
 * Time: 00:52
 */

namespace ccm\ws;

use ccm\app;
use ccm\connectionStringBuilder;
use ccm\tools\permissionTools;
use ccm\tools\strTools;
use ccm\userAccountManager;

require_once ROOT . "/class/tools/environment.class.php";
require_once ROOT . "/data/redisServers.list.php";
require_once ROOT . "/vendor/predis/predis/autoload.php";
require_once ROOT . "/class/tools/curlHelper.class.php";

use Predis;

class pingService_1_1 extends RestService
{

    public function autenticationValidate($url, $method, &$arguments)
    {
        return true;
    }

    // If we get to theses methods the class is already authenticated
    public function performGet($url, $arguments, $accept)
    {

        $resp = '{"status":"OK","version":"1.1"';

        $dash = false;

        // Checking the redis servers
        if(array_key_exists('redis', $arguments) && $arguments['redis'] == "true"){
            $resp .=  ',"redis":{';

            $list = \ccm\getRedisServersList();

            for ($i = 1; $i <= $list->totalNodes(); $i++) {
                $val = $list->readNode($i);

                if($i > 1) $resp .= ',';

                $resp .= '"host":"'.md5($val['host']).'","port":"'.$val['port'].'","status":';

                $client = new Predis\Client([
                    'scheme' => 'tcp',
                    'host'   => $val['host'],
                    'port'   => $val['port'] + 0,
                ]);

                $client->connect();

                if($client->isConnected()){
                    $resp .= '"OK"';
                }else{
                    $resp .= '"Error"';
                }

            }

            $resp .= '}';
            $dash = true;
        }else{
            $dash = false;
        }


        //Checking the vault servers
        if(array_key_exists('vault', $arguments) && $arguments['vault'] == "true") {
            if ($dash) $resp .= ",";

            $resp .=  '"vault":{';

            $resp .= '"server":"cofre1","status":';

            $url = VAULT_SERVER1_URL ;

            try {
                $http_resp = \ccm\curlHelper::execute($url);
                $error = false;
            }catch (\ccm\tools\curlErrorEX $ex){
                $error = true;
            }

            if ($error || $http_resp['code'] != 200) {
                $resp .= '"Error"';

            }else{
                $resp .= '"OK"';
            }

            //$resp .= '}';

            $resp .= ',"server":"cofre2","status":';

            $url = VAULT_SERVER2_URL ;

            try {
                $http_resp = \ccm\curlHelper::execute($url);
                $error = false;
            }catch (\ccm\tools\curlErrorEX $ex){
                $error = true;
            }

            if ($error || $http_resp['code'] != 200) {
                $resp .= '"Error"';

            }else{
                $resp .= '"OK"';
            }

            $resp .= '}';
        }

        $resp .=  '}';

        $response = $this->quickResponse(1, $resp); // OK

        if (!defined('UNIT_TESTING')) $this->deliver_response($this->response_format, $response);
        else return $response;



        $this->methodNotAllowedResponse();
    }

    public function performHead($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    public function performPost($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }

    public function performPut($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }

    public function performDelete($url, $arguments, $accept)
    {

        $this->methodNotAllowedResponse();
    }


} 