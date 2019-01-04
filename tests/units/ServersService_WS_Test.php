<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests;


use ccm\ws\serversService;
use ccm\ws\authenticationLoginService;


require_once ROOT."/class/userAccountManager.class.php";

require_once ROOT . "/class/ws/authenticationLoginService.ws.php";

class ServersService_WS_Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var serversService
     */
    private $serverss;

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
    #  ServersService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->serverss = new serversService($this->accept, 'json');
            $this->initialized = true;

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS AppsService... \n";

            // First we need to autenticate

            $this->arguments =array('format' => 'json', 'resource1'  => 'Tserver2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

            $this->autenticate();


        }
    }

    private function autenticate(){
        $aws =  new authenticationLoginService($this->accept, 'json');

        $url = 'http://dummy.com/api/authenticationLogin';
        $resp = $aws->performGet( $url, $this->arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $this->arguments['token'] = $token;
    }

    /**
     * @group Unity
     */
    function testListing(){

        $this->setUp();

        $url = 'http://dummy.com/api/servers';

        unset($this->arguments['resource1']);

        $resp = $this->serverss->performGet($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        //$this->assertArrayHasKey('name',$data);
        $this->assertEquals('Tserver', $data[0]->name);

        // Let' repeat the same test with a user that don't have permissions


        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();


        $resp = $this->serverss->performGet($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);


        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testReadOne(){
        $this->setUp();


        $url = 'http://dummy.com/api/servers/testesfsdg22';
        $this->arguments['resource1'] = 'testesfsdg22';

        $resp = $this->serverss->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(7, $resp['code']);


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps/Tserver';
        $this->arguments['resource1'] = 'Tserver';

        $resp = $this->serverss->performGet($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertEquals('Tserver',$data->getName());
        $this->assertEquals('tserver.ip.com',$data->getFQDN());

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $resp = $this->serverss->performGet($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testCreate(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/servers/Tserver';

        $this->arguments['body'] = '{"assignments":{"Tapp2":["Produção"]}}';

        $resp = $this->serverss->performPut($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15,$resp['code']);


        $url = 'http://dummy.com/api/servers/teste22';
        $this->arguments['resource1'] = 'teste22';

        $resp = $this->serverss->performPut($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        // We should get an error here because it's mandatory to pass the fqdn as a put
        $this->assertEquals(15,$resp['code']);

        $this->arguments['body'] = '{"assignments":{"Tapp2":["Produção"]},"fqdn":"tserver.com"}';

        $resp = $this->serverss->performPut($url, $this->arguments, $this->accept );

        $this->assertEquals(2,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/servers/teste23';
        $this->arguments['resource1'] = 'teste23';

        $resp = $this->serverss->performPut($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testUpdate(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/servers/trasdflsdf';
        $this->arguments['resource1'] = 'trasdflsdf';
        $this->arguments['body'] = '{"assignments":{"Tapp2":["Produção","Desenvolvimento"]}}';


        $resp = $this->serverss->performPost($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15,$resp['code']);


        $url = 'http://dummy.com/api/servers/Tserver';
        $this->arguments['resource1'] = 'Tserver';

        $resp = $this->serverss->performPost($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1,$resp['code']);

        $this->arguments['body'] = '{"assignments":{"Tapp2":["Produção","Desenvolvimento"]},"fqdn":"teste.com"}';

        $resp = $this->serverss->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(1,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/apps/teste23';
        $this->arguments['resource1'] = 'teste23';

        $resp = $this->serverss->performPost($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testDelete(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/servers/trasdflsdf';
        $this->arguments['resource1'] = 'trasdflsdf';
        $this->arguments['body'] = '{}';


        $resp = $this->serverss->performDelete($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(7,$resp['code']);


        $url = 'http://dummy.com/api/servers/Tserver';
        $this->arguments['resource1'] = 'Tserver';

        $resp = $this->serverss->performDelete($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/servers/Tserver2';
        $this->arguments['resource1'] = 'Tserver2';

        $resp = $this->serverss->performDelete($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }


    function tearDown() {
        // delete your instance
        unset($this->appss);
        unset($this->arguments);
        $this->initialized = false;
    }

}








