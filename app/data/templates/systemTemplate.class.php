<?php
/**
 * Created by Felipe F Quintella.
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */
namespace gcc;

class systemTemplate
{

    private $version, $basecode;


    function __construct($version, $baseCode)
    {

        $this->version = $version;
        $this->basecode = $baseCode;

    }

    function getFinalCode($system, $encrypt = false)
    {


        $result = str_replace("%system_name%", $system->getName(), $this->basecode);
        if ($encrypt == false) $result = str_replace("%system_password%", $system->getSalt() . '#:#' . $system->getPassword(), $result);
        else {
            throw new notImplementedException("Não foi implementado");

        }

        return $result;
    }

    public function getVersion()
    {
        return $this->version;
    }


}