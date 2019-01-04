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
require_once ROOT."/class/appsManager.class.php";


class appsManagerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var appsManager
     */
    private $appsm;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->appsm = \ccm\appsManager::get_instance();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de acesso a dados... \n";
        }
    }


    /**
     * @group Unity
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     */
    function testSave(){

        $this->setUp();

        $app = new app('teste','utestes');

        $this->appsm->save(null);

        $resp = $this->appsm->save($app);

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
        $this->appsm->find(null);


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        // Searching fake app
        $resp = $this->appsm->find('NoApp');

        $this->assertNull($resp);

        // Searching real app
        $resp = $this->appsm->find('Tapp');

        $this->assertNotNull($resp);

        $this->assertEquals('Tapp', $resp->getName());

        $r2 = $this->appsm->findByKey($resp->getKey());

        $this->assertEquals('Tapp', $r2->getName());

        $r2 = $this->appsm->find('Tapp2');

        $this->assertEquals('Tapp2', $r2->getName());

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        // Searching fake app
        $resp = $this->appsm->getList();

        $this->assertNotNull($resp);


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \Exception
     * @expectedExceptionCode 2
     */
    function testDelete(){

        $this->setUp();

        // Searching fake app
        $resp = $this->appsm->delete('NoApp');

        $this->assertEquals(-1, $resp);

        $resp = $this->appsm->delete('Tapp');

        $this->assertEquals(1, $resp);

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->appsm);
    }

}
