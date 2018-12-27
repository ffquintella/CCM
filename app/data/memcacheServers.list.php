<?php
/**
 * User: felipe.quintella
 * Date: 17/06/13
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */

namespace gcc;

require_once ROOT . "/class/linkedList.class.php";

function getMemcacheServersList()
{

    $sList = new linkedList();

    // Groups
    // -----------------        Server       , Port
    //$sList->insertLast(array( " "  =>  "" );
    //$sList->insertLast(array( 'ip' => "127.0.0.1", 'port' =>  "29645" ));
    $sList->insertLast(array('ip' => "10.251.1.240", 'port' => "11211"));
    $sList->insertLast(array('ip' => "10.251.1.170", 'port' => "11211"));
    return $sList;

}