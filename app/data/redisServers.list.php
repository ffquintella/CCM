<?php
/**
 * User: felipe.quintella
 * Date: 17/06/13
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */

namespace gcc;

require_once ROOT . "/class/linkedList.class.php";

function getRedisServersList()
{

    $sList = new linkedList();

    // Groups
    // -----------------        Server       , Port
    //$sList->insertLast(array( " "  =>  "" );
    $sList->insertLast(array('host' => '10.25.11.13', 'port' => 6379, 'database' => 1));
    //$sList->insertLast(array('host' => '127.0.0.1', 'port' => 6379, 'database' => 1));
    //$sList->insertLast(array( 'host' => '192.168.75.200', 'port' =>  6379, 'database' => 1 ));

    return $sList;

}