<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 14:46
 */
namespace gcc\Tests;


//require_once "../../app/vars.php";
//require_once "../../app/vars.php";
require_once ROOT."/class/sharedStorageFactory.class.php";
require_once ROOT.'/data/memcacheServers.list.php';


class sharedStorageTest extends \PHPUnit_Framework_TestCase {

    private $sharedStorage, $initialized=false;

    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;
            $this->sharedStorage = \gcc\sharedStorageFactory::get_instance()->getSharedStorage();

            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Beggining data access tests... \n";
        }
    }


    function testGetList(){
        $list = \gcc\getMemcacheServersList();

        $this->assertNotNull($list);
        $this->assertGreaterThan(0,$list->totalNodes());
    }

    function testConnection(){
        $this->setUp();
        $this->sharedStorage->connect();
        $this->assertNotNull($this->sharedStorage->getStatus());
        $this->tearDown();
    }

    function testReadWrite(){
        $this->setUp();

        $this->sharedStorage->connect();

        $this->sharedStorage->set('teste1', 'a1');
        $this->sharedStorage->set('teste2', 'a2');

        $this->assertEquals('a1', $this->sharedStorage->get('teste1'));
        $this->assertEquals('a2', $this->sharedStorage->get('teste2'));

        //$this->assertGreaterThan(0,$list->totalNodes());
        $this->tearDown();
    }

    /**
     * @large
     */
    function testExpiration(){
        $this->setUp();

        $this->sharedStorage->connect();

        $this->sharedStorage->set('teste1', 'a1', 1);
        $this->sharedStorage->set('teste2', 'a2', 10);

        sleep(2);

        $this->assertNotEquals('a1', $this->sharedStorage->get('teste1'));
        $this->assertEquals('a2', $this->sharedStorage->get('teste2'));

        $this->tearDown();
    }


    function tearDown() {
        // delete your instance
        unset($this->sharedStorage);
    }
}