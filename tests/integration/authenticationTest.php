<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 27/06/13
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace gcc\Tests;


//require_once "../app/vars.php";
require_once ROOT."/class/authTokenManager.class.php";


class authenticationTest extends \PHPUnit_Framework_TestCase {

    private $atm, $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->atm = \gcc\authTokenManager::get_instance();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de autenticação... \n";
        }
    }

    /**
     * @group Unity
     */
    function testGetTokenSystem(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Buscando o token do Stests... \n";

        $token = $this->atm->getSystemToken('Stests', 'teste2', '127.0.0.1');
        $this->assertEquals('-1', $token);

        $token = $this->atm->getSystemToken('Stestes', 'teste1', '127.0.0.1');
        $this->assertEquals('-2', $token);

        $token = $this->atm->getSystemToken('Stestes', 'teste', '127.0.0.1');
        $this->assertNotEquals('-2', $token);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testGetTokenUser(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Buscando o token do utestes2... \n";

        $token = $this->atm->getUserToken('utestess', 'teste2', '127.0.0.1');
        $this->assertEquals('-1', $token);

        $token = $this->atm->getUserToken('utestes', 'teste1', '127.0.0.1');
        $this->assertEquals('-2', $token);

        $token = $this->atm->getUserToken('utestes', 'teste', '127.0.0.1');
        $this->assertNotEquals('-2', $token);

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testValidateToken(){

        $this->setUp();

        $token1 = $this->atm->getSystemToken('Stestes', 'teste', '127.0.0.1');

        $this->assertTrue($this->atm->validateToken($token1, '127.0.0.1'));

        $this->assertFalse($this->atm->validateToken($token1."SDSDd", '127.0.0.1'));

        $this->assertFalse($this->atm->validateToken($token1, '192.168.1.1'));

        $token = $this->atm->getUserToken('utestes', 'teste', '127.0.0.1');

        $this->assertTrue($this->atm->validateToken($token, '127.0.0.1'));

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testRebuildToken(){

        $this->setUp();

        $token1 = $this->atm->getSystemToken('Stestes', 'teste', '127.0.0.1');

        $token2 = $this->atm->rebuildToken((string)$token1);

        $this->assertEquals($token1->getUserName(), $token2->getUserName());
        $this->assertEquals($token1->getIpAddress(), $token2->getIpAddress());
        $this->assertEquals($token1->getTokenType(), $token2->getTokenType());

        $token3 = $this->atm->getUserToken('utestes', 'teste', '127.0.0.1');

        $token4 = $this->atm->rebuildToken((string)$token3);

        $this->assertEquals($token3->getUserName(), $token4->getUserName());
        $this->assertEquals($token3->getIpAddress(), $token4->getIpAddress());
        $this->assertEquals($token3->getTokenType(), $token4->getTokenType());


        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->atm);
    }

}
