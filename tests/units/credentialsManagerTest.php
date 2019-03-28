<?php
/**
 * Created by JetBrains PhpStorm.
 * User: felipe.quintella
 * Date: 06/01/17
 * Time: 11:31
 * To change this template use File | Settings | File Templates.
 */

namespace ccm\Tests;

use ccm\credential;
use ccm\credentialsManager;

require_once __DIR__."/../../app/vars.php";
require_once ROOT."/class/credentialsManager.class.php";


class credentialsManagerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var appsManager
     */
    private $credM;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp(): void {
        if(!$this->initialized){
            $this->initialized = true;
            $this->credM = credentialsManager::get_instance();

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

        $cred = new credential('teste', 'Tapp');

        $this->credM->save(null);

        $resp = $this->credM->save($cred);

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
        $this->credM->find(null);


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        // Searching fake cred
        $resp = $this->credM->find('NoCred');

        $this->assertNull($resp);

        // Searching real cred
        $resp = $this->credM->find('tc1');

        $this->assertNotNull($resp);

        $this->assertEquals('tc1', $resp->getName());

        $this->tearDown();
    }

    /**
     * @group Unity
     * @expectedException \ccm\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testFindByApp(){

        $this->setUp();

        $resp = $this->credM->findAllByApp('Tapp');

        $this->assertEquals(1, count($resp));

        $resp = $this->credM->findAllByApp('Tapp2');

        $this->assertEquals(2, count($resp));

        // Searching fake cred
        $this->credM->findAllByApp('');


        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testGetAll(){

        $this->setUp();

        $resp = $this->credM->getList();
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
        $resp = $this->credM->delete('NoApp');

        $this->assertEquals(-1, $resp);

        $resp = $this->credM->delete('tc1');

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
        $resp = $this->credM->delete('');
        $this->tearDown();
    }

    function tearDown(): void {
        // delete your instance
        unset($this->credM);
    }

}
