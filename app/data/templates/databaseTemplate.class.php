<?php
/**
 * Created by Felipe F Quintella.
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */
namespace gcc;

class databaseTemplate
{

    private $version, $basecode;


    function __construct($version, $baseCode)
    {

        $this->version = $version;
        $this->basecode = $baseCode;

    }

    function getFinalCode($database, $encrypt = false)
    {

        $result = str_replace("%database_name%", $database->getName(), $this->basecode);
        $result = str_replace("%user_name%", $database->getUser(apsTypes::Dev)->getLogin(), $result);
        $result = str_replace("%user_password_dev%", $database->getUser(apsTypes::Dev)->getPassword(), $result);
        $result = str_replace("%user_password_homolog%", $database->getUser(apsTypes::Homolog)->getPassword(), $result);
        $result = str_replace("%user_password_prod%", $database->getUser(apsTypes::Prod)->getPassword(), $result);
        $result = str_replace("%schema%", $database->getSchema(), $result);
        $result = str_replace("%database_type%", $database->getType(), $result);
        $result = str_replace("%system_name%", $database->getSystem(), $result);
        $result = str_replace("%database_server%", $database->getServer(), $result);
        $result = str_replace("%database_port%", $database->getPort(), $result);


        if (count($database->getApplicationServerGroup()->getServers()) <= 0) throw new Exception("There must be at least one application Server");

        $applicationServerList = $database->getApplicationServerGroup()->getServers();

        $result = str_replace("%aplication_server_groupname%", $database->getApplicationServerGroup()->getName(), $result);


        $appServerString = "";

        $first = true;
        foreach ($applicationServerList as $key => $value) {
            if ($first) $first = false;
            else $appServerString .= ",";

            $appServerString .= "new server( \"" . $value->getName() . "\", \"" . $value->getType() . "\") \n";

        }

        $result = str_replace("%array_server_list%", $appServerString, $result);

        return $result;
    }

    public function getVersion()
    {
        return $this->version;
    }


}