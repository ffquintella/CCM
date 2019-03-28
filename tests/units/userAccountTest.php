<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 27/06/13
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace ccm\Tests;


use ccm\app;
use ccm\userAccount;
use ccm\userAccountManager;

require_once __DIR__."/../../app/vars.php";
require_once ROOT."/class/userAccountManager.class.php";
require_once ROOT."/class/userAccount.class.php";


define('UNIT_TESTING', true);

class userAccountTest extends \PHPUnit\Framework\TestCase {

    private $initialized=false;

    function setUp(): void {
        if(!$this->initialized){
            $this->initialized = true;

            if(!defined(UNIT_TESTING))define(UNIT_TESTING, true);

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe database... \n";
        }


    }



    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 100
     */
    function testUserAccount(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Criando usuário e definindo um tipo inválido :- \n";

        $u = new userAccount('teste1', 'teste123');
        $u->setUserAuthentication('teste');

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Definindo um tipo válido :- \n";
        $u->setUserAuthentication('ldap');
        $this->assertEquals('ldap',$u->getUserStorage());

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testPassword(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Criando usuário e definindo um password :- \n";

        $u = new userAccount('teste1', 'teste');

        $pwd = md5($u->getSalt().'teste');

        $upwd = $u->getPassword();

        $this->assertEquals($pwd, $upwd);

        $uam = userAccountManager::get_instance();

        $u2 = $uam->find("utestes");

        $pwd = md5($u2->getSalt().'teste');

        $upwd = $u2->getPassword();

        $this->assertEquals($pwd, $upwd);


        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testCreate(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Criando usuário :- \n";


        $uam = userAccountManager::get_instance();

        // Password too simple
        $ret = $uam->create('testeUAM', 'teste', null);
        $this->assertEquals(-2, $ret);

        // User already exists
        $ret = $uam->create('utestes', 'testE372863423466523', null);
        $this->assertEquals(-1, $ret);

        $ret = $uam->create('testeUAM', 'testE62367235523645', null);
        $this->assertEquals(1, $ret);

        $ret = $uam->create('testeUAM2', 'testE62367235523645', array('teste' => true));
        $this->assertEquals(1, $ret);

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testManager(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Testando userAccountManager :- \n";

        $uam = userAccountManager::get_instance();

        $ul = $uam->getList();

        // A lista não pode ser vazia
        $this->assertNotNull($ul);

        // Somente existem dois nós nos testes
        $this->assertEquals(4, $ul->totalNodes());

        //buscando usuario que nao existe
        $val = $uam->find("djakdfjsdgf");

        $this->assertNull($val);

        //buscando usuario que existe
        $val = $uam->find("utestes");

        $this->assertNotNull($val);

        $this->assertEquals("utestes", $val->getName());

        $this->tearDown();
    }


    /**
     * @group Unit
     *
     */
    function testPermissions(){

        $this->setUp();

        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Testando permissoes :- \n";

        $uam = userAccountManager::get_instance();


        //Getting a user that exists
        $val = $uam->find("utestes");

        $this->assertNotNull($val);

        $this->assertEquals("utestes", $val->getName());

        $this->assertTrue($val->hasPermission('admin'));

        //Getting a user that exists
        $val = $uam->find("utestes2");

        $this->assertNotNull($val);

        $this->assertEquals("utestes2", $val->getName());

        $this->assertFalse($val->hasPermission('admin'));
        $this->assertEquals('writer', $val->hasPermission('app:tapp'));

        $this->tearDown();
    }

    function tearDown(): void {
        // delete your instance
    }
}