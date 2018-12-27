<?php
/**
 * User: felipe.quintella
 * Date: 17/06/13
 * Time: 17:28
 * To change this template use File | Settings | File Templates.
 *
 * @author Felipe F Quintella <felipe.quintella@fgv.br>
 */
namespace gcc;

require_once ROOT . "/class/linkedList.class.php";

/**
 * Class systemGroup
 * This class defines a group of systems
 *
 * @package gubd/classes/lists
 */
class applicationServerGroup
{

    private $name, $servers;

    public function __construct($name, $servers)
    {
        $this->name = $name;
        $this->servers = $servers;
    }

    /**
     * @param mixed $servers
     */
    public function setServers($servers)
    {
        $this->servers = $servers;
    }

    /**
     * @return string - name of the systemGroup
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return linkedList - List of all the systems registred
     */
    public function getServers()
    {
        return $this->servers;
    }

}