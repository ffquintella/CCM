<?php
/**
 * Created by felipe. for gubd
 * Date: 07/03/14
 * Time: 23:18
 *
 * @author felipe
 *
 * @version 1.0
 */

namespace ccm\ws;

use ccm\logFactory;
use ccm\tools\strTools;

require_once "wsincludes.php";

require_once "pingService.ws.php";


class GuRouter extends RestService
{

    protected $log;

    function __construct($param = "")
    {
        $this->HTTPS_required = HTTPSRequired;
        $this->authentication_required = AUTENTICATIONRequired;

        $this->log = logFactory::getLogger();

        $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
        parent::__construct($accept);
    }


    public function autenticationValidate($url, $method, &$arguments)
    {
        return true;
    }

    /**
     * This function routes the requests to the appropriate class to deal with them
     *
     * @param $url
     * @param $method
     * @param $arguments
     * @param $accept
     * @return string|void - Only used for testing
     */
    public function route($url, $method, $arguments, $accept)
    {

        $routeFields = $this->getRouteFields($url);


        if(count($routeFields) == 1) return;

        if ($url == "" || strTools::endsWith($url, "index")
            || strTools::endsWith($url, "index.php")
            || strTools::endsWith($url, "index.html")
            || strTools::endsWith($url, "index." . $this->response_format)
            || strTools::endsWith($url, "api/")
            || strTools::endsWith($url, "api")
        ) {

            $response['code'] = 8;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (array_key_exists('TEST_SCRIPT', $arguments)) {
                return "ERROR";
            }
            $this->deliver_response($this->response_format, $response);
            exit();
        }

        //Defines witch class will handle each url
        $group = "";

        if (array_key_exists('group', $routeFields)) $group = $routeFields['group'];

        if (array_key_exists('resource1', $routeFields)) {
            $arguments = array_merge($arguments, $routeFields);
        }

        // Dynamic Calling
        $serviceFuncion = strtolower($group);

        $serviceFuncion = 'gcc\ws\\'. $serviceFuncion .'Service' ;
        
        $versioned = false;
        if(array_key_exists('version', $routeFields)) $versioned = true;

        if($versioned){
            if($routeFields['version'] != '1.0') {

                $version = floatval($routeFields['version']);

                while($version > 1.0) {

                    $version_number = str_replace('.', '_', $routeFields['version']);
                    $newServiceFuncion = strtolower($group). 'Service' . '_' . $version_number;

                    $fileCheck = ROOT.'/class/ws/'.$newServiceFuncion . ".ws.php";
                    if (file_exists($fileCheck)) {
                        $serviceFuncion = 'gcc\ws\\'. $newServiceFuncion;
                        break;
                    }
                    $version = $version - 0.1;
                }


            }
        }

        $this->log->Debug("Routing to: ". $serviceFuncion);

        $serviceClass = new $serviceFuncion($this->supportedMethods, $this->response_format);

        $serviceClass->handleRequest($url, $method, $arguments, $accept);


        $response['code'] = 7;
        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
        $response['data'] = $this->api_response_code[$response['code']]['Message'];

        $this->deliver_response($this->response_format, $response);

        //$this->handleRequest($url, $method, $arguments, $accept);
    }

    /**
     * This function opens the $url into more usefull fields
     * @param $url
     * @return  null if invalid
     *          array if ok
     */
    public function getRouteFields($url)
    {
        $response = array();
        if ($url == null) return null;


        $mid = explode('?', $url);
        if (count($mid) > 2) return null; // The url can only have 1 ?

        if (count($mid) == 2) $response['params'] = $mid[1];
        $response['params'] = '';

        $mid = explode('/', explode("//", $mid[0])[1]);

        // Let's check if it's versioned
        $versioned = false;
        if(count($mid) >= 3) {
            if (strTools::startsWith(strtolower($mid[2]), "v")) {
                $cchar = $mid[2][1];
                if (is_numeric($cchar)) {
                    $versioned = true;
                }
            }
        }
        if(!$versioned) {
            if (count($mid) >= 3) {
                $response['group'] = $mid[2];

                for ($i = 3; $i < count($mid); $i++) {
                    $response['resource' . ($i - 2)] = $mid[$i];
                }
            }
        }else{
            if (count($mid) >= 3) {
                $response['version'] = substr($mid[2],1);
                $response['group'] = $mid[3];

                for ($i = 4; $i < count($mid); $i++) {
                    $response['resource' . ($i - 3)] = $mid[$i];
                }
            }
        }

        return $response;

    }


}