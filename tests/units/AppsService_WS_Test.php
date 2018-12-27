<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace gcc\Tests;


use gcc\ws\appsService;
use gcc\ws\authenticationLoginService;


require_once ROOT."/class/userAccountManager.class.php";

require_once ROOT . "/class/ws/authenticationLoginService.ws.php";

class AppsService_WS_Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var appsService
     */
    private $appss;

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
    #  AppsService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->appss = new appsService($this->accept, 'json');
            $this->initialized = true;

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS AppsService... \n";

            // First we need to autenticate

            $this->arguments =array('format' => 'json', 'resource1'  => 'tapp', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

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
    function testListingWithLimitations(){
        $this->setUp();


        // First we need to autenticate

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes3', 'password' => 'teste123');


        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps';

        $resp = $this->appss->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertArrayHasKey('App-1',$data);




        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testListing(){
        $this->setUp();


        // First we need to autenticate

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');


        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps';

        $resp = $this->appss->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertArrayHasKey('App-1',$data);


        $url = 'http://dummy.com/api/v1.1/apps';

        $appss2 = new \gcc\ws\appsService_1_1($this->accept, 'json');

        $resp = $appss2->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertArrayHasKey(0, $data);

        // Let' repeat the same test with a user that don't have permissions

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes4', 'password' => 'teste123');


        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/accounts';

        $resp = $this->appss->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);


        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testReadOne(){
        $this->setUp();


        $url = 'http://dummy.com/api/apps/testesfsdg22';
        $this->arguments['resource1'] = 'testesfsdg22';

        $resp = $this->appss->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(7, $resp['code']);


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps/tapp';
        $this->arguments['resource1'] = 'tapp';

        $resp = $this->appss->performGet($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertEquals('Tapp',$data['name']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes4';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $resp = $this->appss->performGet($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testCreate(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps/Tapp';

        $this->arguments['body'] = '{"environments":["Produção","Desenvolvimento"]}';


        $resp = $this->appss->performPut($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15,$resp['code']);


        $url = 'http://dummy.com/api/apps/teste22';
        $this->arguments['resource1'] = 'teste22';

        $resp = $this->appss->performPut($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(2,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes2';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/apps/teste23';
        $this->arguments['resource1'] = 'teste23';

        $resp = $this->appss->performPut($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testUpdate(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps/trasdflsdf';
        $this->arguments['resource1'] = 'trasdflsdf';
        $this->arguments['body'] = '{"environments":["Produção","Desenvolvimento"]}';


        $resp = $this->appss->performPost($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15,$resp['code']);


        $url = 'http://dummy.com/api/apps/tapp2';
        $this->arguments['resource1'] = 'tapp2';

        $resp = $this->appss->performPost($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes3';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/apps/teste23';
        $this->arguments['resource1'] = 'teste23';

        $resp = $this->appss->performPost($url, $this->arguments, $this->accept );

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testDelete(){
        $this->setUp();


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/apps/trasdflsdf';
        $this->arguments['resource1'] = 'trasdflsdf';
        $this->arguments['body'] = '{"environments":["Produção","Desenvolvimento"]}';


        $resp = $this->appss->performDelete($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(7,$resp['code']);


        $url = 'http://dummy.com/api/apps/tapp2';
        $this->arguments['resource1'] = 'tapp2';

        $resp = $this->appss->performDelete($url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1,$resp['code']);

        // Let' try again without permissions

        $this->arguments['username'] = 'utestes3';
        $this->arguments['password'] = 'teste123';

        $this->autenticate();

        $url = 'http://dummy.com/api/apps/tapp2';
        $this->arguments['resource1'] = 'tapp2';

        $resp = $this->appss->performDelete($url, $this->arguments, $this->accept );

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








