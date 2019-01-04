<?php
/**
 * Created by Felipe Quintella
 * User: Felipe
 * Date: 05/03/14
 * Time: 23:58
 */
namespace ccm\ws;

use ccm\authTokenManager;
use ccm\logFactory;
use ccm\tools\HTTP_Accept;
use ccm\tools\strTools;
use Sabre\XML\Writer;

/**
 * Class RestService
 * @package gubd\ws
 *
 * http return codes implemented
 * 200 OK: successful request when data is returned
 * 201 Created: Successful request when something is created at another URL (specified by the value returned in the Location header)
 * 204 No Content: Successful request when no data is returned
 * 400 Bad Request: Incorrect parameters specified on request
 * 401 Authentication Required
 * 403 HTTPS Required
 * 404 Not Found: No resource at the specified URL
 * 405 Method Not Allowed: when a client makes a request using an HTTP verb not supported at the requested URL (supported verbs are returned in the Allow header)
 * 406 Not Acceptable: Requested data format not supported
 * 407 Format Invalid
 * 500 Internal Server Error: An unexpected error occurred
 * 501 Not Implemented: when a client makes a request using an unknown HTTP verb
 * 550 Permission denied
 *
 */
class RestService
{

    public $api_response_code = array(
        0 => array('HTTP Response' => 400, 'Message' => 'Bad Request'),
        1 => array('HTTP Response' => 200, 'Message' => 'Success'),
        2 => array('HTTP Response' => 201, 'Message' => 'Created'),
        3 => array('HTTP Response' => 204, 'Message' => 'No Content: Successful request when no data is returned'),
        4 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
        5 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
        6 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
        7 => array('HTTP Response' => 404, 'Message' => 'Not Found: No resource at the specified URL'),
        8 => array('HTTP Response' => 405, 'Message' => 'Not Allowed: This url doesn\'t implement this resource'),
        9 => array('HTTP Response' => 406, 'Message' => 'Not Acceptable: Requested data format not supported'),
        10 => array('HTTP Response' => 407, 'Message' => 'Invalid Response Format'),
        11 => array('HTTP Response' => 500, 'Message' => 'Internal Server Error: An unexpected error occurred'),
        12 => array('HTTP Response' => 501, 'Message' => 'Not Implemented: when a client makes a request using an unknown HTTP verb'),
        13 => array('HTTP Response' => 503, 'Message' => 'Service Unavailable: The requested service is down.'),
        14 => array('HTTP Response' => 550, 'Message' => 'Permission denied: The user doesn\'t have permission to do the action requested.'),
        15 => array('HTTP Response' => 409, 'Message' => 'Conflict: The data received is in conflict with our rules'),
    );
    protected $supportedMethods;
    protected $log;
    protected $orc;
    protected $atm; // Authentication Token Manager
    protected $test = false;

    // Define whether an HTTPS connection is required
    protected $HTTPS_required;

    // Define whether user authentication is required
    protected $authentication_required;


    // Defined response method
    protected $response_format;

    // The place the request came from
    protected $location;
    private $hta;

    public function __construct($supportedMethods)
    {

        $this->atm = authTokenManager::get_instance();
        $this->test = false;
        $this->log = logFactory::getLogger();
        $this->supportedMethods = $supportedMethods;
        $this->hta = new HTTP_Accept();
        foreach ($supportedMethods as $value) {
            $this->hta->addType($value);
        }

    }

    public function permissionValidate($url, $method, $arguments)
    {
        return false;
    }

    public function handleRawRequest($server, $get, $post)
    {

        $url = $this->getFullUrl($server);
        $method = $server['REQUEST_METHOD'];
        $arguments = array();
        switch ($method) {
            case 'GET':
            case 'HEAD':
                $arguments = $get;
                break;
            case 'POST':
                //$arguments = $post;
                //parse_str(file_get_contents("php://input"),$post_body);
                //if(count($post_body) > 0)$arguments['body'] = array_keys($post_body)[0];
                $post_body = file_get_contents("php://input");
                $arguments['body'] = $post_body;
                $arguments = array_merge($arguments, $get);
                $arguments = array_merge($arguments, $post);
                break;
            case 'PUT':
                //parse_str(file_get_contents("php://input"),$put_body);
                //if(count($put_body) > 0)$arguments['body'] = array_keys($put_body)[0];
                $put_body = file_get_contents("php://input");
                $arguments['body'] = $put_body;
                $arguments = array_merge($arguments, $get);
                break;
            case 'DELETE':
                parse_str(file_get_contents("php://input"), $delete_body);
                if (count($delete_body) > 0) $arguments['body'] = array_keys($delete_body)[0];
                else $arguments['body'] = "";
                $arguments = array_merge($arguments, $get);
                break;
        }


        if (array_key_exists('HTTP_ACCEPT', $server)) {
            $accept = $server['HTTP_ACCEPT'];
        } else {
            $accept = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        }


        // Workaround to allow basic authentication work with php-fpm
        if (!array_key_exists('PHP_AUTH_USER', $server) && array_key_exists('HTTP_AUTHORIZATION', $server)) {
            if (trim($server['HTTP_AUTHORIZATION']) != '') {
                $arguments['token'] = $server['HTTP_AUTHORIZATION'];
            }


        }

        if (isset($_COOKIE['gccAuthToken'])) {
            $arguments['token'] = $_COOKIE['gccAuthToken'];
        }

        //Get the forwarded IP if it exists
        if (array_key_exists('X-Forwarded-For', $server) && filter_var($server['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {

            $the_ip = $server['X-Forwarded-For'];

        } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $server) && filter_var($server['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ) {

            $the_ip = $server['HTTP_X_FORWARDED_FOR'];

        } else {
            if ($_SERVER['REMOTE_ADDR'] == '::1') $the_ip = '127.0.0.1';
            else $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        $arguments['cipaddr'] = $the_ip;

        if (array_key_exists('PHP_AUTH_USER', $server)) {

            $arguments['username'] = $server['PHP_AUTH_USER'];
            $arguments['password'] = $server['PHP_AUTH_PW'];
        }

        //Instrument to enable unity testing
        if (array_key_exists('TEST_SCRIPT', $server)) {
            return array($url, $method, $arguments, $accept);
        }
        if (array_key_exists('TEST_SECURITY', $arguments)) {
            return $this->securityCheck($url, $method, $arguments, $accept);
        }

        $arg2 = $arguments;
        array_pop($arg2);
        $log = logFactory::getLogger();
        //$log->Info("Login attempt: ". implode("-",$arg2)."-XXXX");

        $log->Trace("Entering security check", ['url' => $url, 'method' => $method]);

        $this->securityCheck($url, $method, $arguments, $accept);
        $this->route($url, $method, $arguments, $accept);
    }

    /**
     * This function is responsible for getting the server url
     * @param $server
     * @return string
     */
    public function getFullUrl($server)
    {
        if (array_key_exists('HTTPS', $server)) $protocol = 'https'; else $protocol = 'http';
        $location = $server['REQUEST_URI'];
        if (array_key_exists('QUERY_STRING', $server) && strrpos($location, $server['QUERY_STRING'])) {
            $location = substr($location, 0, strrpos($location, $server['QUERY_STRING']) - 1);
        }
        $this->location = $location;
        return $protocol . '://' . $server['HTTP_HOST'] . $location;
    }

    private function securityCheck($url, $method, $arguments, $accept)
    {

        $this->setResponseFormat($arguments, $accept);

        if (array_key_exists('TEST_SECURITY', $arguments)) {
            $this->authentication_required = true;
            $this->HTTPS_required = true;
        }

        $this->log->Debug("Verifying protocol", ['url' => $url]);

        if ($this->HTTPS_required && !strTools::startsWith($url, "https")) {
            $response['code'] = 4;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            // To enable unity Testing
            if (array_key_exists('TEST_SECURITY', $arguments)) {
                return -1;
            }

            $this->deliver_response($this->response_format, $response);
            exit();
        }
        $this->log->Debug("Verifying authentication", ['ip' => $arguments['cipaddr']]);
        //var_dump($this->authentication_required); exit;
        if ($this->authentication_required && !$this->autenticationValidate($url, $method, $arguments)) {
            $this->log->Trace("Authentication required");
            $response['code'] = 5;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            // To enable unity Testing
            if (array_key_exists('TEST_SECURITY', $arguments)) {
                return -2;
            }

            $this->deliver_response($this->response_format, $response);
            exit();
        }

        if (!strTools::startsWith($url, 'http://localhost') && (!$this->authentication_required || !$this->HTTPS_required)) {
            if (array_key_exists('TEST_SECURITY', $arguments)) {
                return -2;
            }
            $this->log->Error("In production it's mandatory to have https and authentication.");

            $response['code'] = 11;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            if (array_key_exists('TEST_SECURITY', $arguments)) {
                return -3;
            }

            $this->deliver_response($this->response_format, $response);
            exit();

        }

        if (array_key_exists('TEST_SECURITY', $arguments)) {
            return 1;
        }


    }

    /**
     * This function is responsible for defining what response format will be used
     * @param $arguments
     * @param $accept
     */
    private function setResponseFormat($arguments, $accept)
    {
        if (array_key_exists('format', $arguments)) $this->response_format = $arguments['format'];
        if (!$this->response_format) {

            $accept = new HTTP_Accept($accept);

            $old = "";
            $winner = "";

            if ($accept == "*/*") {
                $winner = 'json';
            } else {
                foreach ($this->hta->getTypes() as $value) {
                    if ($old != "") {
                        if ($accept->getQuality($value) > $accept->getQuality($old)) $winner = $value;
                    }
                    $old = $value;
                }

                if ($winner == "") {
                    foreach ($this->hta->getTypes() as $value) {
                        if ($accept->isMatchExact($value)) $winner = $value;
                    }
                }

                if ($winner == "") {
                    $response['code'] = 4;
                    $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
                    $response['data'] = $this->api_response_code[$response['code']]['Message'];

                    $this->deliver_response($this->response_format, $response);
                    exit();
                }
            }

            $this->response_format = $winner;
        }

    }

    /***
     * Delivers the formated response to the browser
     *
     * @param $format
     * @param $api_response
     */
    function deliver_response($format, $api_response)
    {

        // Define HTTP responses
        $http_response_code = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            201 => 'Created',
            204 => 'No Content',
            405 => 'Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Invalid Format',
            409 => 'Conflict',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            550 => 'Permission denied'

        );


        // Set HTTP Response
        header('HTTP/1.1 ' . $api_response['status'] . ' ' . $http_response_code[$api_response['status']]);

        // Process different content types
        if (strcasecmp($format, 'json') == 0 || strcasecmp($format, 'application/json') == 0) {

            // Set HTTP Response Content Type
            header('Content-Type: application/json; charset=utf-8');

            // Format data into a JSON response

            $obj = $api_response['data'];
            //if(is_string($obj)) $obj = array($obj);
            $json_response = json_encode($obj);

            $err = json_last_error_msg();
            //echo $err;

            //var_dump($api_response['data']);

            // Deliver formatted data
            echo $json_response;

        } elseif (strcasecmp($format, 'xml') == 0 || strcasecmp($format, 'application/xml') == 0) {

            // Set HTTP Response Content Type
            header('Content-Type: application/xml; charset=utf-8');

                /*$writer = new Writer();
                $writer->openMemory();
                $writer->setIndent(true); // for pretty indentation
                $writer->startDocument();

                $writer->write($api_response['data']);


            $xml_response = $writer->outputMemory();*/

            $xml_response = '<?xml version="1.0" encoding="utf-8"?>
<errors xmlns="http://schemas.google.com/g/2005">
  <error>
    <reason>discontinued</reason>
    <internalReason>xml output is no longer supported...</internalReason>
    <domain>ccm</domain>
  </error>
  <code>500</code> 
</errors>';

            // Deliver formatted data
            echo $xml_response;
        } elseif (strcasecmp($format, 'msgpack') == 0) {
            // Set HTTP Response Content Type
            header('Content-Type: application/x-msgpack; charset=utf-8');

            // Format data into a JSON response
            $msgpack_response = msgpack_pack($api_response['data']);

            // Deliver formatted data
            echo $msgpack_response;

        } else {

            // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
            header('Content-Type: text/html; charset=utf-8');

            // Deliver formatted data
            echo $api_response['data'];

        }

        // End script process
        exit;

    }

    /**
     * Validates if the token in the header is true
     * @param $url
     * @param $method
     * @param $arguments
     * @return bool
     */
    public function autenticationValidate($url, $method, &$arguments)
    {
        return false;
    }

    public function route($url, $method, $arguments, $accept)
    {
        $this->handleRequest($url, $method, $arguments, $accept);
    }

    public function handleRequest($url, $method, $arguments, $accept)
    {

        if (!$this->autenticationValidate($url, $method, $arguments)) {
            $response['code'] = 5;
            $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
            $response['data'] = $this->api_response_code[$response['code']]['Message'];

            $this->deliver_response($this->response_format, $response);
        }

        switch ($method) {
            case 'GET':
                $this->performGet($url, $arguments, $accept);
                break;
            case 'HEAD':
                $this->performHead($url, $arguments, $accept);
                break;
            case 'POST':
                $this->performPost($url, $arguments, $accept);
                break;
            case 'PUT':
                $this->performPut($url, $arguments, $accept);
                break;
            case 'DELETE':
                $this->performDelete($url, $arguments, $accept);
                break;
            default:
                /* 501 (Not Implemented) for any unknown methods */
                header('Allow: ' . $this->supportedMethods, true, 501);
        }
    }

    public function performGet($url, $arguments, $accept)
    {
        $this->methodNotAllowedResponse();
    }

    protected function methodNotAllowedResponse()
    {
        /* 405 (Method Not Allowed) */

        //header('Allow: ' . $this->supportedMethods, true, 405);
        header('Allow: ' . $this->hta, true, 405);
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

    /***
     * Returns the result code of the last executed operation
     * @return mixed
     */
    public function getOperationReturnCode()
    {
        return $this->orc;
    }

    /***
     * Defines the class test status
     * @param $test
     */
    public function setTestStatus($test)
    {
        $this->test = $test;
    }

    /**
     * Helps creating the response object
     *
     * @param int $code
     * @param  $data
     * @return array
     */
    public function quickResponse(int $code, $data = null): array
    {
        $response['code'] = $code;
        $response['status'] = $this->api_response_code[$response['code']]['HTTP Response'];
        if ($data != null) $response['data'] = $data;
        else $response['data'] = $this->api_response_code[$response['code']]['Message'];

        return $response;
    }

}