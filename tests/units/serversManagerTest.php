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
use ccm\appsManager;
use ccm\linkedList;

require_once "../app/vars.php";
require_once ROOT."/class/serversManager.class.php";


class serversManagerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var appsManager
     */
    private $serverm;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->serverm = \ccm\serversManager::get_instance();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de acesso a dados... \n";
        }
    }

    /**
     * @group Unity
     *
     * @expectedException \ArgumentCountError
     */
    function testWrongParameters(){
        $this->setUp();

        $server = new \ccm\server('TS');

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     */
    function testSave(){

        $this->setUp();

        $server = new \ccm\server('TS','ts.com');

        $this->serverm->save(null);

        $resp = $this->serverm->save($server);

        $this->assertTrue($resp);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \TypeError
     */
    function testFindNull(){

        $this->setUp();

        // Searching null app
        $this->serverm->find(null);


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFindByApp(){

        $this->setUp();

        // Searching fake app
        $resp = $this->serverm->findByApp('NoSrv');

        $this->assertNull($resp);

        $resp = $this->serverm->findByApp('Tapp2');
        $this->assertEquals(2, count($resp));

        $this->tearDown();
    }

    /**
     * @group Unity
     */
    function testFindByAppNIP(){

        $this->setUp();

        $resp = $this->serverm->findByAppNIP('NoApp', '127.0.0.1');

        $this->assertEquals(0, count($resp));

        $resp = $this->serverm->findByAppNIP('Tapp2', '127.0.0.2');

        $this->assertEquals(0, count($resp));

        $resp = $this->serverm->findByAppNIP('Tapp2', '127.0.0.1');

        $this->assertEquals(1, count($resp));

        $this->tearDown();
    }

    /**
     * @group Unity
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testFindByAppNIPE1(){

        $this->setUp();

        // Searching fake app
        $resp = $this->serverm->findByAppNIP('', '');

        $this->tearDown();
    }

    /**
     * @group Unity
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     */
    function testFindByAppNIPE2(){

        $this->setUp();

        // Searching fake app
        $resp = $this->serverm->findByAppNIP('', '127.0.0.1');

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        // Searching fake server
        $resp = $this->serverm->find('NoSrv');

        $this->assertNull($resp);

        // Searching real server
        $resp = $this->serverm->find('Tserver');

        $this->assertNotNull($resp);

        $this->assertEquals('Tserver', $resp->getName());

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        // Searching fake app
        $resp = $this->serverm->getList();

        $this->assertNotNull($resp);


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testDelete(){

        $this->setUp();

        // Searching fake app
        $resp = $this->serverm->delete('NoApp');

        $this->assertEquals(-1, $resp);

        $resp = $this->serverm->delete('Tserver');

        $this->assertEquals(1, $resp);

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->serverm);
    }

}
