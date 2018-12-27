<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace gcc\Tests;

use gcc\ws\authenticationLoginService;


require_once "../app/vars.php";
require_once ROOT."/class/ws/GuRouter.ws.php";
require_once ROOT."/class/logFactory.class.php";

class AuthenticationLoginTest extends \PHPUnit_Framework_TestCase {


    private $aws, $initialized=false;


    ####################################
    #  AutenticationLoginService.ws.php
    ####################################

    function setUp() {
        if(!$this->initialized){

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');
            $this->aws = new authenticationLoginService($accept, 'json');
            $this->aws->setTestStatus(true);
            $this->initialized = true;
            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes unitários de autenticação... \n";
        }
    }

    /**
     * @group Unity
     */
    function testAuthentication(){
        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes de autenticação... \n";

        $url = 'http://dummy.com/api/index';

        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'TEST_SCRIPT' => true);
        $accept = array('application/x-msgpack', 'application/xml', 'application/json', 'text/html');

        $this->aws->performGet( $url, $arguments, $accept );
        $return = $this->aws->getOperationReturnCode();

        $this->assertEquals(5,  $return['code']);


        $url = 'http://dummy.com/api/authenticationLogin';
        $arguments =array('format' => 'json', 'g1'  => '1','g2'  => '2', 'cipaddr' => '127.0.0.1','TEST_SCRIPT' => true, 'username' => 'utestes', 'password' => 'teste');

        $this->aws->performGet( $url, $arguments, $accept );
        $return = $this->aws->getOperationReturnCode();
        $this->assertEquals(1,  $return['code']);

        $this->tearDown();
    }


    function tearDown() {
        // delete your instance
        unset($this->guws);
        $this->gu_initialized = false;
    }

}








