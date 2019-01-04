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
    $sList->insertLast(array('host' => '1.1.1.1', 'port' => 6379, 'database' => 1));


    return $sList;

}