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
use gcc\tools\permissionTools;
use gcc\userAccount;
use gcc\wrongFunctionParameterEX;

require_once ROOT."/class/tools/permissionTools.php";
require_once ROOT."/class/userAccount.class.php";
require_once ROOT."/class/wrongFunctionParameterEX.php";

class toolsTest extends \PHPUnit_Framework_TestCase {

    private $initialized=false;


    function setUp() {
        if(!$this->initialized){
            $this->initialized = true;



            if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Inicio dos testes da classe tools... \n";
        }


    }

    /**
     * @group Unit
     *
     * @expectedException TypeError
     */
    function testPermissionP1(){
        $this->setUp();
        $resp = permissionTools::validate(null, null);

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     * @expectedException TypeError
     */
    function testPermissionP2(){
        $this->setUp();
        $resp = permissionTools::validate(array('teste'), null);

        $this->tearDown();
    }


    /**
     * @group Unit
     *
     * @expectedException \gcc\wrongFunctionParameterEX
     * @expectedExceptionCode 1
     */
    function testPermissionP3(){
        $this->setUp();

        $user = new userAccount('teste','testE923812376gsd23','local');

        $resp = permissionTools::validate(array(), $user);

        $this->tearDown();
    }

    /**
     * @group Unit
     *
     */
    function testPermission(){
        $this->setUp();

        $user = new userAccount('teste','testE923812376gsd23','local');

        $resp = permissionTools::validate(array('admin' => true), $user);
        $this->assertFalse($resp);

        $user->addPermission(array('admin' => true));

        $resp = permissionTools::validate(array('admin' => true), $user);
        $this->assertTrue($resp);

        $user = new userAccount('teste','testE923812376gsd23','local');
        $user->addPermission(array('app1' => 'read'));

        $resp = permissionTools::validate(array('admin' => true), $user);
        $this->assertFalse($resp);

        $resp = permissionTools::validate(array('app1' => 'read'), $user);
        $this->assertTrue($resp);

        $user = new userAccount('teste','testE923812376gsd23','local');
        $resp = permissionTools::validate(array('admin' => true, 'app1' => 'read'), $user);
        $this->assertFalse($resp);

        $user->addPermission(array('app1' => 'write'));
        $resp = permissionTools::validate(array('admin' => true, 'app1' => 'read'), $user);
        $this->assertFalse($resp);

        $user->addPermission(array('app1' => 'read'));
        $resp = permissionTools::validate(array('admin' => true, 'app1' => 'read'), $user);
        $this->assertTrue($resp);

        $this->tearDown();
    }

    function testGetAppPermission(){
        $user = new userAccount('teste', 'easasdfDwfsd7fsdf$#sdfsd');
        $user->addPermission(array('app:Tapp' => 'write', 'app:Tapp2' => 'read'));

        $perms = permissionTools::getAppsWithPermissions($user, array('read'));
        $this->assertArrayHasKey('Tapp2', $perms);
        $this->assertEquals('read', $perms['Tapp2']);
    }

    function tearDown() {
        // delete your instance
    }
}