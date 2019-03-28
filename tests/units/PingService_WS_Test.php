<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests;


use ccm\ws\pingService;



class PingService_WS_Test extends \PHPUnit\Framework\TestCase {

    /**
     * @var pingService
     */
    private $pss;

    /***
     * @var bool
     */
    private $initialized=false;

    /***
     * @var array
     */
    private $accept;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var \Logger
     */
    private $log;

    ####################################
    #  PingService.ws.php
    ####################################

    function setUp(): void {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->pss = new pingService($this->accept, 'json');
            $this->initialized = true;

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS PingService... \n";

            // First we need to autenticate

            $this->arguments =array();




        }
    }



    /**
     * @group Unity
     */
    function testBase(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/ping';

        $resp = $this->pss->performGet($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1,$resp['code']);



        $this->tearDown();
    }


    function tearDown(): void {
        // delete your instance
        unset($this->appss);
        unset($this->arguments);
        $this->initialized = false;
    }

}








