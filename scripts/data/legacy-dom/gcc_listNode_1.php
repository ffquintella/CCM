<?php

/**
THIS SERIES OF CLASSES ARE ONLY TO BE USED IN DATA MIGRATION!!!
 */

namespace gcc;


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