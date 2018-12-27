<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 27/06/13
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace gcc\Tests;


use gcc\app;

require_once "../app/vars.php";
require_once ROOT."/class/appsManager.class.php";
require_once ROOT."/class/app.class.php";

class appsTest extends \PHPUnit_Framework_TestCase {

    private $initialized=false;
    /**
     * @var app
     */
    private $app;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;

            $this->app = new app('TestApp1', 'TestU');


            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe apps... \n";
        }


    }

    /**
     * @group Unit - Basic class tests
     */
    function testBasics(){
        $this->setUp();
        $this->assertEquals('TestApp1', $this->app->getName());
        $this->assertEquals('TestU', $this->app->getOwner());

        $this->assertNotNull($this->app->getCreationT());
        $this->tearDown();
    }

    /**
     * @group Unit - Environemtns
     */
    function testenvironments(){

        $this->setUp();

        $resp = $this->app->addEnvironment('Teste');

        $this->assertFalse($resp);

        $resp = $this->app->addEnvironment('Produção');

        $this->assertTrue($resp);

        $this->app->cleanenvironments();

        $resp = $this->app->hasEnvironment('Produção');

        $this->assertFalse($resp);

        $this->tearDown();

    }

    /**
     * @group Unit - Environemtns
     */
    function testKey(){
        $this->setUp();

        $this->assertNotNull($this->app->getKey());

        $this->tearDown();

    }


    function tearDown() {
        // delete your instance
        unset($this->app);
    }
}