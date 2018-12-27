<?php
/**
 * Created by Felipe F Quintella
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 18:57
 * To change this template use File | Settings | File Templates.
 */

namespace gcc;

require_once ROOT . "/data/templates/databaseFileTemplate.list.php";
require_once ROOT . "/data/templates/databaseTemplate.class.php";


/**
 * Parameters valid for replace on this template
 * %database_name%
 * %user_name%
 * %user_password_dev%
 * %user_password_homolog%
 * %user_password_prod%
 * %schema%
 * %database_type%
 * %system_name%
 * %database_server%
 * %database_port%
 * %aplication_server_groupname%
 * %array_server_list%
 */
$templ = new databaseTemplate("1.02",
    "<?php
namespace gubd;

databaseList::get_instance()->addDatabase(
    new database(
        //Database Name
        \"%database_name%\",
        //Connection user
        array(      \"desenv\"       => new user (\"%user_name%\",\"%user_password_dev%\"),
                    \"homolog\"   => new user (\"%user_name%\",\"%user_password_homolog%\"),
                    \"prod\"      => new user (\"%user_name%\",\"%user_password_prod%\")),
        //Schema
        \"%schema%\",
        //Database type
        \"%database_type%\",
        //System Name
        \"%system_name%\",
        //Database Server
        \"%database_server%\",
        //Database port
        %database_port%,
        //Aplication Server Group
        new applicationServerGroup( \"%aplication_server_groupname%\"    , array(

                %array_server_list%

            ))
    ));


");


/*
 *             new server( \"bog003\", \"prod\")
            ,new server( \"bog004\", \"prod\")
            ,new server( \"bog005\", \"dev\")
            ,new server( \"itg004\", \"prod\")
            ,new server( \"itg005\", \"homolog\")
 */

addDBT($templ);