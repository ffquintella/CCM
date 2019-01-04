<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 27/06/13
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace ccm\Tests;

use ccm\configuration;


require_once "../app/vars.php";
require_once ROOT."/class/configuration.class.php";

class configurationTest extends \PHPUnit_Framework_TestCase {

    private $initialized=false;
    /**
     * @var configuration
     */
    private $config;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->config = new configuration('c1', 'Tapp2');
            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe configuration... \n";
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

        new configuration('', '');

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

        new configuration('sddfdsf', '');

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

        new configuration('sddfdsf', 'NoApp');

        $this->tearDown();
    }

    /**
     * @group Unit - Basic class tests
     */
    function testBasics(){
        $this->setUp();

        $this->assertEquals('c1', $this->config->getName());

        $this->config->setName('test');

        $this->assertEquals('test', $this->config->getName());


        $this->tearDown();
    }

    /**
     * @group Unit
     */
    function testSetVaultID(){
        $this->setUp();

        $conf = new configuration('Lcred','Tapp2');
        $conf->setValue('Desenvolvimento', 't1');
        $this->assertEquals('t1', $conf->getValue('Desenvolvimento'));

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

        $conf = new configuration('Lcred','Tapp2');
        $conf->setValue('NoEnv', 't1');

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

        $conf = new configuration('Lcred','Tapp5');
        $conf->setValue('Desenvolvimento', 't1');

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testGetValue(){
        $this->setUp();

        $conf = new configuration('Lcred','Tapp2');
        $conf->setValue('Desenvolvimento', 't1');
        $this->assertEquals('t1', $conf->getValue('Desenvolvimento'));

        $conf = new configuration('Lcred','Tapp2');
        $conf->setValue('Desenvolvimento', 't1');
        $conf->setValue('Produção', 't2');
        $conf->setDisplayEnvs(array('Desenvolvimento'));
        $this->assertEquals('t1', $conf->getValue('Desenvolvimento'));
        $this->assertEquals(1, count($conf->getValues()));
        $this->assertNull($conf->getValue('Produção'));

        $conf = new configuration('TCVal','Tapp2');
        $conf->setValue('Produção', 'f1=${tc1}');
        $this->assertEquals('f1=${tc1}', $conf->getValue('Produção'));
        $conf->setReplaceVars(true);
        $this->assertEquals('f1=valp', $conf->getValue('Produção'));

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

        $conf = new configuration('Lcred','Tapp2');
        $conf->setValue('Desenvolvimento', 't1');
        $conf->getValue('Produção');

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

        $conf = new configuration('Lcred','Tapp2' );
        $conf->setValue('Desenvolvimento', 't1');
        $conf->getValue('');

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

        $this->config->setValue('testE', 'testV');

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

        $this->config->setAppName('NoApp');
        $this->config->setValue('testE', 'testV');

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

        $this->config->setAppName('Tapp');
        $this->config->setValue('testE', 'testV');

        $this->tearDown();
    }

    /**
     * @group Unit
     */
    function testSetValue(){
        $this->setUp();

        $this->config->setAppName('Tapp2');

        $this->config->setValue('Desenvolvimento', 'testV');
        $this->assertArrayHasKey('Desenvolvimento', $this->config->getValues());
        $this->assertEquals('testV', $this->config->getValue('Desenvolvimento'));

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->app);
    }
}