<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests;

use ccm\ws\GuRouter;
use ccm\ws\RestService;

require_once "../app/vars.php";
require_once ROOT."/class/ws/wsincludes.php";
require_once ROOT."/class/logFactory.class.php";

class BasicWsTest extends \PHPUnit_Framework_TestCase {

    ##########################
    #  RestService.ws.php
    #########################

    private $restws, $rest_initialized=false;
    private $guws, $gu_initialized=false;

    function setUpRest() {
        if(!$this->rest_initialized){

            $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->restws = new RestService($accept);
            $this->rest_initialized = true;
           if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de webservices... \n";
        }
    }

    /**
     * @group Unity
     */
    function testGetFullUrl(){
        $this->setUpRest();
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes das urls... \n";
        $dummy_server = array('HTTPS' => true,
                        'REQUEST_URI'  => '/api/databases.json',
                        'QUERY_STRING' => 'format=json',
                        'HTTP_HOST'    => 'dummy.com');
        $this->assertEquals('https://dummy.com/api/databases.json', $this->restws->getFullUrl($dummy_server));
        $dummy_server = array('HTTPS' => false,
            'REQUEST_URI'  => '/api/databases/item',
            'QUERY_STRING' => 'format=xml',
            'HTTP_HOST'    => 'dummy.com');
        $this->assertEquals('https://dummy.com/api/databases/item', $this->restws->getFullUrl($dummy_server));
        $this->tearDownRest();
    }


    /**
     * @group Unity
     */
    function testHandleRawRequest(){
        $this->setUpRest();
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes das urls... \n";
        $dummy_server = array('HTTPS' => true,
            'REQUEST_URI'  => '/api/accounts',
            'QUERY_STRING' => 'format=json',
            'HTTP_HOST'    => 'dummy.com',
            'HTTP_X_FORWARDED_FOR'  => '127.0.0.1',
            'REQUEST_METHOD'  => 'GET',
            'TEST_SCRIPT'  => true,
            'HTTP_ACCEPT' => $accept = array( 'application/json', 'text/html'));



        $dummy_get = array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '');
        $dummy_post = array('p1' => '1', 'p2'  => '2','p3'  => '3', 'cipaddr' => '');

        $result = $this->restws->handleRawRequest($dummy_server, $dummy_get, $dummy_post );
        $this->assertEquals('https://dummy.com/api/accounts', $result[0]);
        $this->assertEquals('GET', $result[1]);
        $this->assertEquals(array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1'), $result[2]);
        $this->assertEquals($accept, $result[3]);


        $dummy_server = array('HTTPS' => true,
            'REQUEST_URI'  => '/api/accounts',
            'QUERY_STRING' => 'format=',
            'HTTP_HOST'    => 'dummy.com',
            'REQUEST_METHOD'  => 'POST',
            'HTTP_X_FORWARDED_FOR'  => '127.0.0.1',
            'TEST_SCRIPT'  => true,
            'HTTP_ACCEPT' => $accept = array( 'application/json', 'text/html'));


        $result = $this->restws->handleRawRequest($dummy_server, $dummy_get, $dummy_post );
        $this->assertEquals('https://dummy.com/api/accounts', $result[0]);
        $this->assertEquals('POST', $result[1]);
        $this->assertEquals(array('format' => 'json','g1' => '1','g2' => '2','p1' => '1', 'p2'  => '2','p3'  => '3', 'cipaddr' => '127.0.0.1', 'body' => ''), $result[2]);
        $this->assertEquals($accept, $result[3]);

        $this->tearDownRest();
    }


    /**
     * @group Unity
     */
    function testSecurity(){
        $this->setUpRest();
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes das urls... \n";
        $dummy_server = array('HTTP' => true,
            'REQUEST_URI'  => '/api/databases.json',
            'QUERY_STRING' => 'format=json',
            'HTTP_HOST'    => 'dummy.com',
            'REQUEST_METHOD'  => 'GET',
            'HTTP_X_FORWARDED_FOR'  => '127.0.0.1',
             'HTTP_ACCEPT' => $accept = array( 'application/json', 'text/html'));

        $dummy_get = array('format' => 'json', 'g1'  => '1','g2'  => '2', 'TEST_SECURITY'  => true);
        $dummy_post = array('p1' => '1', 'p2'  => '2','p3'  => '3', 'TEST_SECURITY'  => true);

        $this->assertEquals(-1,  $this->restws->handleRawRequest($dummy_server, $dummy_get, $dummy_post ));

        $dummy_server = array('HTTPS' => true,
            'REQUEST_URI'  => '/api/databases.json',
            'QUERY_STRING' => 'format=json',
            'HTTP_HOST'    => 'dummy.com',
            'REQUEST_METHOD'  => 'GET',
            'HTTP_X_FORWARDED_FOR'  => '127.0.0.1',
            'HTTP_ACCEPT' => $accept = array( 'application/json', 'text/html'));

        $this->assertEquals(-2,  $this->restws->handleRawRequest($dummy_server, $dummy_get, $dummy_post ));


        $this->tearDownRest();
    }


    function tearDownRest() {
        // delete your instance
        unset($this->restws);
        $this->rest_initialized = false;
    }


    ##########################
    #  GuRouter.ws.php
    #########################

    function setUpGu() {
        if(!$this->gu_initialized){

            $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->guws = new GuRouter($accept);
            $this->gu_initialized = true;
            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de direcionamento... \n";
        }
    }

    /**
     * @group Unity
     */
    function testRouting(){
        $this->setUpGu();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes de encaminhamento... \n";


        $url = 'https://dummy.com/api/index';
        $method = 'GET';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'TEST_SCRIPT' => true);
        $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');

        $this->assertEquals("ERROR",  $this->guws->route( $url,$method, $arguments, $accept ));

        $this->tearDownGu();
    }


    function tearDownGu() {
        // delete your instance
        unset($this->guws);
        $this->gu_initialized = false;
    }

}








