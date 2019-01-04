<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 18/12/2016
 * Time: 21:25
 */

namespace ccm\ws;


class api_response_code
{
    public static $cod_resp = array(
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
        12 => array('HTTP Response' => 501, 'Message' => 'Not Implemented: when a client makes a request using an unknown HTTP verb')
    );
}