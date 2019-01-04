<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 27/06/13
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace ccm\Tests;

use ccm\credential;

require_once "../app/vars.php";
require_once ROOT."/class/credential.class.php";

class credentialTest extends \PHPUnit_Framework_TestCase {

    private $initialized=false;
    /**
     * @var credential
     */
    private $credential;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;

            $this->credential = new credential('c1', 'Tapp2');


            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe credential... \n";
        }


    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 3
     *
     */
    function testConstructE1(){
        $this->setUp();

        $cred = new credential('', '');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     *
     */
    function testConstructE2(){
        $this->setUp();

        $cred = new credential('sddfdsf', '');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\corruptDataEX
     * @expectedExceptionCode 1
     *
     */
    function testConstructE3(){
        $this->setUp();

        $cred = new credential('sddfdsf', 'NoApp');

        $this->tearDown();
    }

    /**
     * @group Unit - Basic class tests
     */
    function testBasics(){
        $this->setUp();

        $this->assertEquals('c1', $this->credential->getName());

        $this->credential->setName('test');

        $this->assertEquals('test', $this->credential->getName());

        $this->assertEquals('local', $this->credential->getType());


        $this->tearDown();
    }

    /**
     * @group Unit
     */
    function testSetVaultID(){
        $this->setUp();

        $this->assertEquals('local', $this->credential->getType());

        $cred = new credential('Lcred','Tapp2', 'local');

        $cred->setValue('Desenvolvimento', 't1');

        $this->assertEquals('t1', $cred->getValue('Desenvolvimento'));


        $this->tearDown();
    }


    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testSetVaultIDE1(){
        $this->setUp();


        $cred = new credential('Lcred','Tapp2', 'local');

        $cred->setValue('NoEnv', 't1');


        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\corruptDataEX
     * @expectedExceptionCode 1
     */
    function testSetVaultIDE2(){
        $this->setUp();


        $cred = new credential('Lcred','Tapp5', 'local');

        $cred->setValue('Desenvolvimento', 't1');


        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testGetValue(){
        $this->setUp();

        $cred = new credential('Lcred','Tapp2', 'local');
        $cred->setValue('Desenvolvimento', 't1');

        $this->assertEquals('t1', $cred->getValue('Desenvolvimento'));

        $cred = new credential('Lcred2', 'Tapp2', 'vault');
        $cred->setVaultId('Desenvolvimento','6044:6390');

        $this->assertEquals('Test', $cred->getValue('Desenvolvimento'));

        $cred = new credential('Lcred','Tapp2', 'local');
        $cred->setValue('Desenvolvimento', 't1');
        $cred->setValue('Produção', 't2');
        $cred->setDisplayEnvs(array('Desenvolvimento'));

        $this->assertEquals('t1', $cred->getValue('Desenvolvimento'));

        $this->assertEquals(1, count($cred->getValues()));

        $this->assertNull($cred->getValue('Produção'));

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     *
     */
    function testGetValueE1(){
        $this->setUp();

        $cred = new credential('Lcred','Tapp2', 'local');
        $cred->setValue('Desenvolvimento', 't1');

        $cred->getValue('Produção');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     *
     */
    function testGetValueE2(){
        $this->setUp();

        $cred = new credential('Lcred','Tapp2', 'local');
        $cred->setValue('Desenvolvimento', 't1');

        $cred->getValue('');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     */
    function testSetVaultIDE3(){
        $this->setUp();


        $cred = new credential('Lcred','', 'local');

        $cred->setValue('Desenvolvimento', 't1');


        $this->tearDown();
    }
    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     *
     */
    function testSetTypeE1(){
        $this->setUp();

        $cred = new credential('teste', 'Tapp','NoType');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     *
     */
    function testSetValueE1(){
        $this->setUp();

        $this->credential->setValue('testE', 'testV');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\corruptDataEX
     * @expectedExceptionCode 1
     *
     */
    function testSetValueE2(){
        $this->setUp();

        $this->credential->setAppName('NoApp');

        $this->credential->setValue('testE', 'testV');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     *
     */
    function testSetValueE3(){
        $this->setUp();

        $this->credential->setAppName('Tapp');

        $this->credential->setValue('testE', 'testV');

        $this->tearDown();
    }

    /**
     * @group Unit
     */
    function testSetValue(){
        $this->setUp();

        $this->credential->setAppName('Tapp2');

        $this->credential->setValue('Desenvolvimento', 'testV');

        $this->assertArrayHasKey('Desenvolvimento', $this->credential->getValues());

        $this->assertEquals('testV', $this->credential->getValue('Desenvolvimento'));

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->app);
    }
}