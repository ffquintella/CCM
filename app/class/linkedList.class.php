<?php

namespace gcc;

require_once "listNode.php";

/**
 * Title: Single linked list
 * Description: Implementation of a single linked list in PHP
 * @author Sameer Borate | codediesel.com - Revision Felipe Quintella | ---
 * @version 1.1.1 Updated: 29th december 2016
 */
class linkedList implements \Iterator
{
    /* Link to the first node in the list */
    private $firstNode;

    /* Link to the last node in the list */
    private $lastNode;

    /* Link to the curent node in the list */
    private $currentNode;

    /* Total nodes in the list */
    private $count;


    /* List constructor */
    function __construct()
    {
        $this->firstNode = NULL;
        $this->lastNode = NULL;
        $this->count = 0;
    }

    public function isEmpty()
    {
        return ($this->firstNode == NULL);
    }

    /**
     * @param $data - Data to be inserted
     *
     * This function doesn't alter the current node unless first is empty
     */
    public function insertLast($data)
    {
        if ($this->firstNode != NULL) {
            $link = new ListNode($data);
            $this->lastNode->next = $link;
            $link->next = NULL;
            $this->lastNode = &$link;
            $this->count++;
        } else {
            $this->insertFirst($data);
        }
    }

    /**
     * @param $data - Data to be inserted
     *
     * This function doesn't alter the current node unless last is empty
     */
    public function insertFirst($data)
    {
        $link = new ListNode($data);
        $link->next = $this->firstNode;
        $this->firstNode = &$link;

        /* If this is the first node inserted in the list
           then set the lastNode pointer to it.
        */
        if ($this->lastNode == NULL) {
            $this->lastNode = &$link;
            $this->currentNode = $link;
        }

        $this->count++;
    }

    /**
     * This function doesn't alter the current node the list ends empty or the current is the first
     */
    public function deleteFirstNode()
    {
        $temp = $this->firstNode;

        //if($this->firstNode->next == $this->currentNode) $this->currentNode = $this->firstNode->next;

        if ($this->firstNode == $this->currentNode) $this->currentNode = $this->firstNode->next;

        $this->firstNode = $this->firstNode->next;
        if ($this->firstNode != NULL)
            $this->count--;

        if ($this->firstNode->next == null) $this->currentNode == null;

        return $temp;
    }

    /**
     * This function doesn't alter the current node the list ends empty or the current is the last
     */
    public function deleteLastNode()
    {
        if ($this->firstNode != NULL) {
            if ($this->firstNode->next == NULL) {
                $this->firstNode = NULL;
                $this->currentNode == null;
                $this->count--;
            } else {
                $previousNode = $this->firstNode;
                $currentNode = $this->firstNode->next;

                while ($currentNode->next != NULL) {
                    $previousNode = $currentNode;
                    $currentNode = $currentNode->next;
                }

                $this->currentNode = $currentNode;

                $previousNode->next = NULL;
                $this->count--;
            }
        }
    }

    /**
     * This function doesn't alter the current node unless it' the one being deleted
     */
    public function deleteNode($key)
    {
        $current = $this->firstNode;
        $previous = $this->firstNode;

        while ($current->data != $key) {
            if ($current->next == NULL)
                return NULL;
            else {
                $previous = $current;
                $current = $current->next;
            }
        }

        if ($current == $this->firstNode) {
            if ($this->count == 1) {
                $this->lastNode = $this->firstNode;
            }
            $this->firstNode = $this->firstNode->next;
        } else {
            if ($this->lastNode == $current) {
                $this->lastNode = $previous;
            }
            $previous->next = $current->next;
        }


        if ($this->currentNode->data == $key) {
            if ($this->currentNode->next == null) $this->currentNode = $this->lastNode;
            else $this->currentNode == $this->currentNode->next;
        }

        $this->count--;
        return $this->count;
    }

    public function find($key):?listNode
    {
        $current = $this->firstNode;
        if ($current == null) return null;
        while ($current->data != $key) {
            if ($current->next == NULL)
                return null;
            else
                $current = $current->next;
        }
        $this->currentNode = $current;
        return $current;
    }

    public function readNode(int $nodePos)
    {
        if ($nodePos <= $this->count) {
            $current = $this->firstNode;
            $pos = 1;
            while ($pos != $nodePos) {
                if ($current->next == NULL)
                    return null;
                else
                    $current = $current->next;

                $pos++;
            }
            $this->currentNode = $current;
            return $current->data;
        } else
            return NULL;
    }

    public function totalNodes()
    {
        return $this->count;
    }

    public function readList(): array
    {
        $listData = array();
        $current = $this->firstNode;

        while ($current != NULL) {
            array_push($listData, $current->readNode());
            $current = $current->next;
        }
        return $listData;
    }

    public function reverseList()
    {
        if ($this->firstNode != NULL) {
            if ($this->firstNode->next != NULL) {
                $current = $this->firstNode;
                $new = NULL;

                while ($current != NULL) {
                    $temp = $current->next;
                    $current->next = $new;
                    $new = $current;
                    $current = $temp;
                }
                $this->firstNode = $new;
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->currentNode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->currentNode = $this->currentNode->next;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->currentNode->data;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->currentNode != null) return true;
        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->currentNode = $this->firstNode;
    }
}

?>