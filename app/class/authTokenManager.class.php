<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 11:44
 */

namespace gcc;

use gcc\tools\strTools;

include_once ROOT . "/class/authToken.class.php";
require_once ROOT . '/class/userAccount.class.php';
include_once ROOT . "/class/userAccountManager.class.php";
include_once ROOT . "/class/sharedStorageFactory.class.php";
include_once ROOT . "/baseincludes.php";

/**
 * This class manages the authentication Tokens
 * @package gcc
 */
class authTokenManager extends singleton
{
    /**
     * @var Secure
     */
    private $sec;
    /**
     * @var isharedStorage
     */
    private $storage;


    private $sessionTime = SESSION_TIME;

    /**
     * Autenticates the  user and gets it's token
     *
     * @param $name - The system Name
     * @param $password - The System Password
     * @param string $ipaddress
     * @return string - SystemToken
     *              -1 - System not found
     *              -2 - Password invalid
     *              -3 - Error Connecting to LDAP Server
     */
    public function getUserToken($name, $password, $ipaddress = '127.0.0.1', $ldapName = null)
    {
        $this->init();

        $auth = false;
        $user = userAccountManager::get_instance()->find($name);
        if ($user == null) return "-1";
        else {

            if ($user->getAuthentication() == 'local') {
                $pass = $user->getPassword();
                if ($pass == md5($user->getSalt() . $password)) $auth = true;
            } else {
                // LDAP Authentication
                // Connecting to LDAP
                putenv('LDAPTLS_REQCERT=never');
                $ldapok = false;
                $ldapconn = ldap_connect(LDAPServer, LDAPPort);
                if (!$ldapconn) $ldapconn = ldap_connect(LDAPServer2, LDAPPort);
                if (!$ldapconn) $ldapconn = ldap_connect(LDAPServer3, LDAPPort);

                if ($ldapconn) $ldapok = true;

                if ($ldapok) {

                    // binding to ldap server
                    $ldapbind = ldap_bind($ldapconn, $ldapName . LDAPUserPrefix, $password);

                    // verify binding
                    if ($ldapbind) {
                        $auth = true;
                    } else {
                        $auth = false;
                    }

                } else {
                    return -3;
                }
            }
        }
        if ($auth == false) return '-2';

        if ($auth) {

            $token = new authToken($name, 'user', $ipaddress);

            if (isset($token)) {

                $tokens = (string)$token;

                // 10 minute session
                $this->storage->set("session:" . crc32($tokens), $tokens, $this->sessionTime);

                return $token;
            }

        }
    }

    /**
     * Inits the class variables
     */
    private function init()
    {
        if (!$this->sec) {
            $this->sec = new Secure();
            $this->storage = sharedStorageFactory::getSharedStorage();
            $this->storage->connect();
        }
    }

    /**
     * Autenticates the system user and gets it's token
     *
     * @param $name - The system Name
     * @param $password - The System Password
     * @param string $ipaddress
     * @return string - SystemToken
     *              -1 - System not found
     *              -2 - Password invalid
     */
    public function getSystemToken($name, $password, $ipaddress = '127.0.0.1')
    {
        $this->init();

        $auth = false;
        $system = systemManager::get_instance()->find($name);
        if ($system == null) return "-1";
        else {
            $pass = $system->getPassword();
            $salt = $system->getSalt();
            $passEnc = md5($salt . $password);
            if ($pass == $passEnc) $auth = true;
        }
        if ($auth == false) return '-2';

        if ($auth) {

            $token = new authToken($name, 'system', $ipaddress);

            if (isset($token)) {

                $tokens = (string)$token;
                // 10 minute session
                $this->storage->set("session:" . crc32($tokens), $tokens, $this->sessionTime);

                return $token;
            }

        }
    }


    /**
     * @param $token
     * @param string $ipaddress
     * @return bool
     */
    public function validateToken($token, $ipaddress = '127.0.0.1')
    {
        $this->init();

        $log = logFactory::getLogger();

        //$log->Debug("Searching for token value=" . substr($token, 0, round(strlen($token) / 2)) . "*** clientIP=" . $ipaddress);

        $log->Debug("Searching for token value", [ 'token' => substr($token, 0, round(strlen($token) / 2)) . '***', 'ip' => $ipaddress ]);

        if (strTools::startsWith($token, 'GCCAPPK:')) {
            $log->Trace("This is a app token", ['ip' => $ipaddress]);
            $key = (explode(':', $token))[1];

            $appM = appsManager::get_instance();
            $srvM = serversManager::get_instance();

            try {
                $app = $appM->findByKey($key);
                if ($app != null) {

                    $ipOK = false;
                    $srvs = $srvM->findByApp($app->getName());

                    foreach ($srvs as $name => $srv) {
                        if($srv == null)
                        $log->Error("ERROR Null registry found where it should not...", ['server' => $name]);

                        $log->Debug("Analizing Server", ['srv' => $srv->getName()]);
                        $aip = gethostbyname($srv->getFQDN());
                        if ($aip == $ipaddress) $ipOK = true;
                    }

                    if ($ipOK) {
                        $log->info('Login OK ', ['app' => $app->getName(), 'ip' => $ipaddress]);
                        return true;
                    } else {
                        $log->Warning('Tentative o login from unauthorized ip address', ['ip' => $ipaddress ]);
                    }
                }
            } catch (\Exception $ex) {
                $log->Warning('Error restoring app by key',  ['errorMessage', $ex->getMessage()]);
                return false;
            }

        }

        // Before anything else let's check if the token is registred
        $restoredTk = $this->storage->get("session:" . crc32($token));
        if ($restoredTk != null) {
            $aot = explode("#:#", $this->sec->decrypt($token));
            if (count($aot) != 4) return false;
            if (VERBOSELEVEL == \verbose::DEBUG) {
                $log = logFactory::getLogger();
                $log->Debug("Token values ip=" . $aot[1] . " ctype=" . $aot[2] . " sys=" . $aot[3]);
            }
            if ($aot[1] == $ipaddress && ($aot[2] == 'system' || $aot[2] == 'user')) {
                //doing the time refresh
                $this->storage->replace("session:" . crc32($token), $token, $this->sessionTime);
                return true;
            } else return false;
        } else return false;
    }

    public function rebuildToken($token, string $ip = '127.0.0.1')
    {
        $log = logFactory::getLogger();
        if (strTools::startsWith($token, 'GCCAPPK:')) {
            $log->Trace("Rebuilding an app token");

            $key = (explode(':', $token))[1];
            $appM = appsManager::get_instance();

            try {
                $app = $appM->findByKey($key);
                if ($app != null) {
                    $tk = new authToken($app->getName(), 'app', $ip);
                    return $tk;
                }
            } catch (\Exception $ex) {
                return 0;
            }
        }


        $aot = explode("#:#", $this->sec->decrypt($token));

        if (count($aot) == 4)
            $tk = new authToken($aot[3], $aot[2], $aot[1]);
        else return 0;

        return $tk;
    }


}