<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace gcc\Tests;



use gcc\ws\authenticationLoginService;
use gcc\ws\configurationsService;
use gcc\ws\CredentialsService;


require_once ROOT."/class/credentialsManager.class.php";
require_once ROOT.'/class/ws/configurationsService.ws.php';


class ConfigurationsService_WS_Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var configurationsService
     */
    private $confws;

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
    #  ConfigurationsService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->confws = new \gcc\ws\configurationsService($this->accept, 'json');
            $this->initialized = true;

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS ConfigurationsService... \n";

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

        $url = 'http://dummy.com/api/configurations';

        $resp = $this->confws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testReadOne(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/configurations';

        $this->arguments['resource1']= 'tconf1';


        $resp = $this->confws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->arguments['resource1']= 'tconf4';

        $resp = $this->confws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(7,  $resp['code']);


        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $this->arguments['resource1']= 'tconf2';

        $resp = $this->confws->performGet($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);



        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testCreate(){
        $this->setUp();


        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/configurations';

        $this->arguments['resource1']= 'tconf10';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->confws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(2,  $resp['code']);


        $this->arguments['resource1']= 'tconf1';
        $this->arguments['body']= '{"app":"Tapp2","values":"test"}';


        $resp = $this->confws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tconf1';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->confws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $resp = $this->confws->performPut($url, $this->arguments, $this->accept );
        $this->assertEquals(5,  $resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testUpdate(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/configurations';

        $this->arguments['resource1']= 'tco1';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->confws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tconf1';
        $this->arguments['body']= '{"app":"tapp2","values":"test"}';


        $resp = $this->confws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        $this->arguments['resource1']= 'tconf1';
        $this->arguments['body']= '{"app":"Tapp2","values":{"Produção":"ProdVal","Desenvolvimento":"DesVal"}}';


        $resp = $this->confws->performPost($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testDelete(){
        $this->setUp();

        // Now Let's use our authentication into the tests
        $url = 'http://dummy.com/api/configurations';

        $this->arguments['resource1']= 'tconf1';

        $resp = $this->confws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(1,  $resp['code']);

        $this->arguments['resource1']= 'tconf20';

        $resp = $this->confws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(15,  $resp['code']);

        //APP Access
        $this->arguments['token']= 'GCCAPPK:YRtFGyNOlGh1ohOkvE1SdIUMPX8K5fBR';

        $resp = $this->confws->performDelete($url, $this->arguments, $this->accept );
        $this->assertEquals(5,  $resp['code']);

        $this->tearDown();
    }


    function tearDown() {
        // delete your instance
        unset($this->credws);
        unset($this->arguments);
        unset($this->accept);
        $this->initialized = false;
    }

}








