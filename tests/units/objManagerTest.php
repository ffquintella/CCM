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
require_once ROOT."/class/objManager.class.php";


class objManagerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var appsManager
     */
    private $objm;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->objm = \ccm\objManager::get_instance();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes de acesso a dados... \n";
        }
    }

    /**
     * @group Regression
     */
    function testSearchAll(){

        $this->setUp();



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

        $app = new app('teste','utestes');

        $resp = $this->objm->save('app',$app->getName(), $app);

        $this->assertEquals(1, $resp);

        $this->objm->save('app',$app->getName(), null);

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
        $this->objm->find('app', null);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \TypeError
     */
    function testFindNull2(){

        $this->setUp();

        // Searching null app
        $this->objm->find(null, null);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 2
     */
    function testFindNull3(){

        $this->setUp();

        // Searching null app
        $this->objm->find('', '');

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        // Searching fake app
        $resp = $this->objm->find('app','NoApp');

        $this->assertNull($resp);

        // Searching real app
        $resp = $this->objm->find('app','Tapp');

        $this->assertNotNull($resp);

        $this->assertEquals('Tapp', $resp->getName());

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testDelete(){

        $this->setUp();

        // Searching fake app
        $resp = $this->objm->delete('app','NoApp');

        $this->assertEquals(-1, $resp);

        $resp = $this->objm->delete('app','Tapp');

        $this->assertEquals(1, $resp);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        // Searching fake app
        $resp = $this->objm->getList('app');

        $this->assertNotNull($resp);


        $this->tearDown();
    }


    function tearDown() {
        // delete your instance
        unset($this->appsm);
    }

}
