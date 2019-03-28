<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 10:42
 */

namespace ccm\Tests;

use ccm\linkedList;
use ccm\listsManager;

/**
 * Class listsManagerTest - Tests the class listsManager
 * @package gcc\Tests
 */
class listsManagerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var listsManager
     */
    private $listsm;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp(): void {
        if(!$this->initialized){
            $this->initialized = true;
            $this->listsm = \ccm\listsManager::get_instance();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe listsManager... \n";
        }
    }


    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        $resp = $this->listsm->getList();

        $this->assertNotNull($resp);

        $this->assertEquals(1, $resp->totalNodes());

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        $resp = $this->listsm->find('NotExistent');

        $this->assertNull($resp);

        $resp = $this->listsm->find('environments');

        $this->assertNotNull($resp);

        $this->assertEquals('Produção', $resp->current()->data);

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

        $lll = new linkedList();

        $resp = $this->listsm->save('Teste', $lll);

        $this->assertEquals(1, $resp);

        $this->listsm->save('' , $lll);

        $this->tearDown();
    }

    function tearDown(): void {
        // delete your instance
        unset($this->listsm);
    }

}