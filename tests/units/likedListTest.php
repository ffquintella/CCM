<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 13:42
 */

namespace gcc\Tests;

use gcc\linkedList;

/**
 * Class likedListTest - Tests the linkedList Class
 * @package gcc\Tests
 */
class likedListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var linkedList
     */
    private $ll;
    /**
     * @var bool
     */
    private $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->ll = new linkedList();
    
            $this->ll->insertLast('t1');
            $this->ll->insertLast('t2');
            $this->ll->insertLast('t3');


            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe listsManager... \n";
        }
    }

    /**
     * @group Unity
     *
     */
    function testAdd(){

        $this->setUp();

        $this->assertEquals(3, $this->ll->totalNodes());
        
        $this->ll->insertLast('t4');

        $this->assertEquals(4, $this->ll->totalNodes());

        $this->tearDown();
    }

    /**
 * @group Unity
 *
 */
    function testDelete(){

        $this->setUp();

        $this->assertEquals(3, $this->ll->totalNodes());

        $this->ll->deleteLastNode();

        $this->assertEquals(2, $this->ll->totalNodes());

        $this->ll->deleteNode('t1');

        $this->assertEquals(1, $this->ll->totalNodes());

        $resp = $this->ll->deleteNode('t1');

        $this->assertNull($resp);

        $this->tearDown();

        $this->setUp();

        $this->ll->deleteFirstNode();

        $resp = $this->ll->current();
        $this->assertEquals('t2', $resp->data);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testFind(){

        $this->setUp();

        $this->assertEquals(3, $this->ll->totalNodes());

        $resp = $this->ll->find('t2');

        $this->assertEquals('t2', $resp->data);

        $resp = $this->ll->readNode(2);

        $this->assertEquals('t2', $resp);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testNavigation(){

        $this->setUp();

        $this->assertEquals(3, $this->ll->totalNodes());

        $resp = $this->ll->current();
        $this->assertEquals('t1', $resp->data);

        $this->ll->next();

        $resp = $this->ll->current();

        $this->assertEquals('t2', $resp->data);

        $this->ll->next();
        $resp = $this->ll->current();

        $this->assertEquals('t3', $resp->data);

        $this->ll->rewind();
        $resp = $this->ll->current();

        $this->assertEquals('t1', $resp->data);

        $this->tearDown();
    }

    /**
     * @group Unity
     *
     */
    function testMisc(){

        $this->setUp();

        $resp = $this->ll->isEmpty();

        $this->assertFalse($resp);

        $ll2 = new linkedList();

        $resp = $ll2->isEmpty();

        $this->assertTrue($resp);

        $this->tearDown();
    }

    function tearDown() {
        // delete your instance
        unset($this->ll);
        $this->initialized = false;
    }
}