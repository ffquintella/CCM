<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 04/03/14
 * Time: 14:34
 */

include_once "vars.php";

include_once "vendor/autoload.php";

spl_autoload_register(function ($class) {
    $file = ROOT . '/vendor/monolog/monolog/src/' . strtr($class, '\\', '/') . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
});


/* #############################################
 *  Autoloads for what ever is missing
 *  For it to work this should be the first one
 * ############################################## */
//require_once ROOT."/class/autoloader.class.php";
$include_path = array("/libs/");
//$autoloader = new gubd\autoloader($include_path);

//END Autloads

/* ######################
 *   LOG Session
 * ##################### */

require_once ROOT . "/interfaces/log.interface.php";
require_once ROOT . "/class/logFactory.class.php";


if (LOGPROVIDER == 'monolog') {
    require_once ROOT . "/interfaces/psr.interface.php";
    //require_once ROOT."/libs/Monolog/Logger.php";
    //require_once ROOT."/libs/Monolog/Handler/AbstractProcessingHandler.php";
    //require_once ROOT."/libs/Monolog/Handler/AbstractHandler.php";
    //require_once ROOT."/libs/Monolog/Handler/StreamHandler.php";
}

//END LOG Session

/* #######################
 *  External Libs
 * ####################### */
require_once ROOT . "/libs/litememcache/LiteMemcache.class.php";
require_once ROOT . "/libs/flexihash/flexihash-0.1.9.php";

/* #######################
 *  Encryption Session
 * ####################### */

require_once ROOT . "/class/masterKeyManager.class.php";
require_once ROOT . "/class/sec/EncoderProtected.php";
require_once ROOT . "/class/secure.class.php";

//END Encryption Session


/* #######################
 *  Data Manager Session
 * #######################*/

require_once ROOT . "/class/appsManager.class.php";
require_once ROOT . "/class/listsManager.class.php";
require_once ROOT . "/class/objManager.class.php";
require_once ROOT . "/class/serversManager.class.php";


/* #######################
 *  Tools
 * #######################*/

require_once ROOT . "/class/tools/strTools.class.php";
require_once ROOT . "/class/tools/permissionTools.php";
require_once ROOT . "/headers.php";


//END Data Manager Session



