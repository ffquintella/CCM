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
 * %system_name%
 * %system_password%
 */
$templ = new systemTemplate("1.01",
    "<?php
    namespace gubd;

    systemList::get_instance()->addSystem(new systemAccount(\"%system_name%\", \"%system_password%\"));


    ");


addSysT($templ);