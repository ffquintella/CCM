<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 06/01/17
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace ccm\Tests;

use ccm\configuration;
use ccm\configurationsManager;

require_once __DIR__."/../../app/vars.php";
require_once ROOT."/class/configurationsManager.class.php";


class configuarionsManagerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var configurationsManager
     */
    private $confM;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp(): void {
        if(!$this->initialized){
            $this->initialized = true;
            $this->confM = configurationsManager::get_instance();

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

        $conf = new configuration('teste', 'Tapp');

        $this->confM->save(null);

        $resp = $this->confM->save($conf);

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
        $this->confM->find(null);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        // Searching fake cred
        $resp = $this->confM->find('NoCred');

        $this->assertNull($resp);

        // Searching real cred
        $resp = $this->confM->find('tconf1');

        $this->assertNotNull($resp);

        $this->assertEquals('tconf1', $resp->getName());

        $this->tearDown();
    }

    /**
     * @group Unity
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testFindByApp(){

        $this->setUp();

        $resp = $this->confM->findAllByApp('Tapp');
        $this->assertEquals(1, count($resp));
        $resp = $this->confM->findAllByApp('Tapp2');
        $this->assertEquals(2, count($resp));
        // Searching fake cred
        $this->confM->findAllByApp('');

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        $resp = $this->confM->getList();
        $this->assertNotNull($resp);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \ccm\corruptDataEX
     * @expectedExceptionCode 1
     */
    function testDelete(){

        $this->setUp();

        // Deleting fake credential
        $resp = $this->confM->delete('NoApp');

        $this->assertEquals(-1, $resp);

        $resp = $this->confM->delete('tconf1');

        $this->assertEquals(1, $resp);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testDeleteE2()
    {
        $this->setUp();
        $this->confM->delete('');
        $this->tearDown();
    }

    function tearDown(): void {
        // delete your instance
        unset($this->credM);
    }

}
