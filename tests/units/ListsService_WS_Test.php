<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests;


use ccm\ws\appsService;
use ccm\ws\authenticationLoginService;
use ccm\ws\listsService;


require_once ROOT."/class/userAccountManager.class.php";

require_once ROOT . "/class/ws/listsService.ws.php";

class ListsService_WS_Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var listsService
     */
    private $listss;

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
     * @var string
     */
    private $url;

    ####################################
    #  AppsService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->listss = new listsService($this->accept, 'json');
            $this->initialized = true;

            $this->url = 'http://dummy.com/api/lists';
            $this->arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS ListService... \n";

            $this->doLogin();

        }
    }

    private function doLogin(){
        // First we need to autenticate

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $this->url, $this->arguments, $this->accept );

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

        // Now Let's use our authentication into the tests

        $resp = $this->listss->performGet($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertArrayHasKey('List-1',$data);


        $this->tearDown();
    }


    /**
     * @group Unity
     */
    function testIndividual(){
        $this->setUp();

        // Now Let's use our authentication into the tests

        $this->url = 'http://dummy.com/api/lists/environments';
        $this->arguments['resource1']='environments';


        $resp = $this->listss->performGet($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertEquals('Produção', $data[0]);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testDelete(){
        $this->setUp();

        // Now Let's use our authentication into the tests

        $this->url = 'http://dummy.com/api/lists/environments';
        $this->arguments['resource1']='environments';


        $resp = $this->listss->performDelete($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertEquals('Deleted', $data);

        // Let's do the same with a user that doesn't have permission

        $this->arguments['username']='utestes2';
        $this->arguments['password']='teste123';

        $this->doLogin();

        $resp = $this->listss->performDelete($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }


    /**
     * @group Unity
     */
    function testCreate(){
        $this->setUp();

        // Now Let's use our authentication into the tests

        $this->url = 'http://dummy.com/api/lists/Teste';
        $this->arguments['resource1']='Teste';
        $this->arguments['body']='["val1","val2"]';


        $resp = $this->listss->performPut($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        $this->assertEquals('Created', $data);

        // Now let's try with a resource that already exists
        $this->url = 'http://dummy.com/api/lists/environments';
        $this->arguments['resource1']='environments';

        $resp = $this->listss->performPut($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15, $resp['code']);


        // Let's do the same with a user that doesn't have permission

        $this->url = 'http://dummy.com/api/lists/Teste2';
        $this->arguments['resource1']='Teste2';
        $this->arguments['body']='["val1","val2"]';

        $this->arguments['username']='utestes2';
        $this->arguments['password']='teste123';

        $this->doLogin();

        $resp = $this->listss->performDelete($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testUpdate(){
        $this->setUp();

        // Now Let's use our authentication into the tests

        $this->url = 'http://dummy.com/api/lists/environments';
        $this->arguments['resource1']='environments';
        $this->arguments['body']='["val1","val2"]';


        $resp = $this->listss->performPost($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(1, $resp['code']);

        $this->url = 'http://dummy.com/api/lists/Teste';
        $this->arguments['resource1']='Teste';

        $resp = $this->listss->performPost($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(15, $resp['code']);

        // Let's do the same with a user that doesn't have permission

        $this->url = 'http://dummy.com/api/lists/environments';
        $this->arguments['resource1']='environments';
        $this->arguments['body']='["val1","val2"]';

        $this->arguments['username']='utestes2';
        $this->arguments['password']='teste123';

        $this->doLogin();

        $resp = $this->listss->performDelete($this->url, $this->arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $this->assertEquals(14, $resp['code']);


        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->listss);
        unset($this->arguments);
        unset($this->url);
        $this->initialized = false;
    }

}








