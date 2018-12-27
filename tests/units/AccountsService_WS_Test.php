<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace gcc\Tests;

use gcc\userAccount;
use gcc\ws\accountInfo;
use gcc\ws\accountsService;
use gcc\ws\authenticationLoginService;


require_once "../app/vars.php";
require_once ROOT."/class/ws/GuRouter.ws.php";
require_once ROOT."/class/logFactory.class.php";

class AccountsService_WS_Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var accountsService
     */
    private $acs;

    /***
     * @var bool
     */
    private $initialized=false;

    /***
     * @var array
     */
    private $accept;

    ####################################
    #  AccountsService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $this->accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->acs = new accountsService($this->accept, 'json');
            $this->initialized = true;
            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários do WS AccountsService... \n";



        }
    }

    /**
     * @group Unity
     */
    function testListing(){
        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes em AccountsService... \n";

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

        $url = 'http://dummy.com/api/accounts';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $data = $resp['data'];

        //$this->assertArrayHasKey('User-1',$data);
        //$this->assertArrayHasKey('User-2',$data);

        $expected = array();

        $ainfo = new accountInfo();
        $ainfo->login = "utestes";

        $expected[] = $ainfo;

        $ainfo = new accountInfo();
        $ainfo->login = "utestes2";

        $expected[] = $ainfo;


        $this->assertArraySubset($expected, $data);

        //$this->assertEquals('utestes', $data['User-1']);
        //$this->assertEquals('utestes2', $data['User-2']);


        // Now Let' test with a user that isn't admin
        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes2', 'password' => 'teste123');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/accounts';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(14,$resp['code']);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testIndividualAccess()
    {
        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO) echo "Executando testes em AccountsService - Acesso individual... \n";


        // First we need to autenticate

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        $arguments['resource1'] = 'utestes';

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/accounts/utestes';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $user = $resp['data'];

        $this->assertEquals('utestes', $user['name']);

        $url = 'http://dummy.com/api/accounts/utestes';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $user = $resp['data'];

        $this->assertEquals('utestes', $user['name']);

        $url = 'http://dummy.com/api/accounts/utestes2';
        $arguments['resource1'] = 'utestes2';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $user = $resp['data'];

        $this->assertEquals('utestes2', $user['name']);

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes2', 'password' => 'teste123');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        $url = 'http://dummy.com/api/accounts/utestes2';
        $arguments['resource1'] = 'utestes2';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('data',$resp);

        $user = $resp['data'];

        $this->assertEquals('utestes2', $user['name']);

        $url = 'http://dummy.com/api/accounts/utestes';
        $arguments['resource1'] = 'utestes';

        $resp = $this->acs->performGet($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(14, $resp['code']);

        $this->tearDown();

    }


    /**
     * @group Unity
     */
    function testCreate()
    {
        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO) echo "Executando testes em AccountsService - Acesso individual... \n";


        // First we need to autenticate

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        $arguments['resource1'] = 'Teste123';

        $arguments['body'] = '{
  "name": "Teste123",
  "password": "Teste3217777777777",
  "permissions": {
    "teste": true
  }
}';

        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/accounts/Teste123';

        $resp = $this->acs->performPut($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(2, $resp['code']);

        // password too simple
        $arguments['body'] = '{
  "name": "Teste123",
  "password": "teste",
  "permissions": {
    "teste": true
  }
}';

        $resp = $this->acs->performPut($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(0, $resp['code']);

        // User already exists
        $arguments['body'] = '{
  "name": "Utestes",
  "password": "testE8236235357734",
  "permissions": {
    "teste": true
  }
}';

        $resp = $this->acs->performPut($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(2, $resp['code']);

        $this->tearDown();
    }


    /**
     * @group Unity
     */
    function testDelete()
    {
        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO) echo "Executando testes em AccountsService - Apagar... \n";


        // First we need to autenticate

        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes', 'password' => 'teste');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;


        $arguments['resource1'] = 'Teste123';


        // Now Let's use our authentication into the tests

        $url = 'http://dummy.com/api/accounts/Teste123';

        $resp = $this->acs->performDelete($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(3, $resp['code']);



        $arguments['resource1'] = 'utestes';

        // Now Let's try to delete our own user

        $url = 'http://dummy.com/api/accounts/utestes';

        $resp = $this->acs->performDelete($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(15, $resp['code']);

        // Now Let's try a valid deletion

        $url = 'http://dummy.com/api/accounts/utestes2';
        $arguments['resource1'] = 'utestes2';

        $resp = $this->acs->performDelete($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(1, $resp['code']);

        //Now Let's login with a non admin user and try to delete


        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1', 'username' => 'utestes2', 'password' => 'teste123');

        $aws =  new authenticationLoginService($this->accept, 'json');

        $resp = $aws->performGet( $url, $arguments, $this->accept );

        $this->assertEquals(1,  $resp['code']);

        $td = $resp['data'];
        $token = (string)$td;

        $arguments['token'] = $token;

        $url = 'http://dummy.com/api/accounts/utestes';
        $arguments['resource1'] = 'utestes';

        $resp = $this->acs->performDelete($url, $arguments, $this->accept );

        $this->assertArrayHasKey('code',$resp);

        $this->assertEquals(14, $resp['code']);

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->guws);
        $this->gu_initialized = false;
    }

}








