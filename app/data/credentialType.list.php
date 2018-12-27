<?php
/**
 * User: felipe.quintella
 * Date: 17/06/13
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */

namespace gcc;

require_once ROOT . "/class/linkedList.class.php";

function getCredentialTypeList()
{

    $sList = new linkedList();

    $sList->insertLast('local');
    $sList->insertLast('vault');

    return $sList;

}