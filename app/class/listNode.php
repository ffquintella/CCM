<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 18/12/2016
 * Time: 21:32
 */

namespace ccm;


class listNode
{
    /* Data to hold */
    public $data;

    /* Link to next node */
    public $next;


    /* Node constructor */
    function __construct($data)
    {
        $this->data = $data;
        $this->next = NULL;
    }

    function readNode()
    {
        return $this->data;
    }
}