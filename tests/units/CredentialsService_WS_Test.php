<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests;



use ccm\ws\authenticationLoginService;
use ccm\ws\CredentialsService;


require_once ROOT."/class/credentialsManager.class.php";
require_once ROOT.'/class/ws/credentialsService.ws.php';


class CredentialsService_WS_Test extends \PHPUnit\Framework\TestCase {

    /**
     * @var CredentialsService
     */
    private $credws;

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


    ####################################
    #  CredentialsService.ws.php
    ####################################

    function setUp(): void
    {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->credws = new \ccm\ws\CredentialsService($this->accept, 'json');
            $this->initialized = true;

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS AppsService... \n";

            // First we need to autenticate

            $this->arguments =array('format' => 'json', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

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

        $this->autenticate();

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/credentials';

        $resp = $this->credws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testReadOne(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/credentials';

        $this->arguments['resource1']= 'tc1';


        $resp = $this->credws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->arguments['resource1']= 'tc4';

        $resp = $this->credws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(7,  $resp['code']);


        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $this->arguments['resource1']= 'tc2';

        $resp = $this->credws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testCreate(){
        $this->setUp();


        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/credentials';

        $this->arguments['resource1']= 'tc10';
        $this->arguments['body']= '{"app":"Tapp2","type":"local","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->credws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(2,  $resp['code']);


        $this->arguments['resource1']= 'tc1';
        $this->arguments['body']= '{"app":"Tapp2","values":"test"}';


        $resp = $this->credws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tc1';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->credws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $resp = $this->credws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(5,  $resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testUpdate(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/credentials';

        $this->arguments['resource1']= 'tc1';
        $this->arguments['body']= '{"app":"Tapp2","type":"local","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->credws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tc1';
        $this->arguments['body']= '{"app":"Tapp2","values":"test"}';


        $resp = $this->credws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tc1';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->credws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testDelete(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/credentials';

        $this->arguments['resource1']= 'tc1';

        $resp = $this->credws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->arguments['resource1']= 'tc20';

        $resp = $this->credws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $resp = $this->credws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(5,  $resp['code']);

        $this->tearDown();
    }


    function tearDown(): void
    {
        // delete your instance
        unset($this->credws);
        unset($this->arguments);
        unset($this->accept);
        $this->initialized = false;
    }

}








