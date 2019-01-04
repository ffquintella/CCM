<?php
/**
 * Created by Felipe Quintella.
 * User: felipe.quintella
 * Date: 26/02/14
 * Time: 21:38
 * This class is the class that manages all the systems records
 *
 * @author Felipe F Quintella <felipe.quintella@fgv.br>
 *
 * @since 0.1
 *
 * @copyright All rights reserved Fundação Getulio Vargas
 *
 */


namespace ccm;


include_once ROOT . "/baseincludes.php";
require_once "secure.class.php";
require_once "singleton.class.php";
require_once "server.class.php";


/**
 * Class appsManager
 * It manages all the apps records
 *
 * @version 1.0
 *
 * @package gcc\class
 */
class serversManager extends singleton
{


    /**
     * @var log
     */
    private $log;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var objManager
     */
    private $objManager;


    /**
     * @var string
     */
    private $objType;

    /**
     * Return the list of apps
     * @return linkedList
     */
    public function getList(): linkedList
    {

        $this->initialize();
        return $this->objManager->getList($this->objType);

    }

    private function initialize()
    {
        if (!$this->initialized) {
            $this->objType = 'server';
            $this->log = logFactory::getLogger();
            $this->objManager = objManager::get_instance();
            $this->initialized = true;
        }
    }

    /**
     * Finds servers by it's apps and IPs
     * @param string $appName
     * @param string $ip
     * @return array|null
     * @throws wrongFunctionParameterEX - 1- ip is empty
     *                                2- appName is empty
     */
    public function findByAppNIP(string $appName, string $ip): array
    {

        if ($ip == '') throw new wrongFunctionParameterEX('We can\'t find empty ips', 1);
        if ($appName == '') throw new wrongFunctionParameterEX('We can\'t find empty names', 2);

        $this->initialize();

        $serversByApp = $this->findByApp(strtolower($appName));

        $resp = array();

        if ($serversByApp != null) {
            $cm = new cacheManager(CACHE_DNS_TIMEOUT);
            foreach ($serversByApp as $key => $value) {

                $fqdn = $value->getFQDN();

                $lip = $cm->getCachedValue('dns:' . $fqdn);

                if ($lip == null) {
                    $lip = gethostbyname($fqdn);
                    $cm->setCachedValue('dns:' . $fqdn, $lip);
                }

                if ($lip == $ip) $resp[] = $value;
            }
        }

        return $resp;

    }

    /**
     * Finds one server by it's apps
     * @param string $appName
     * @return array|null
     * @throws wrongFunctionParameterEX
     *              2- appName is empty
     */
    public function findByApp(string $appName): ?array
    {

        if ($appName == '') {
            throw new wrongFunctionParameterEX('We can\'t find empty names', 2);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();
        $srvNames = $ss->getSet('ref:app-server:' . strtolower($appName));

        if ($srvNames != null) {
            $srvs = array();
            foreach ($srvNames as $key => $srvEnv) {
                $srvName = explode(':', $srvEnv)[0];
                $srv = $this->find(strtolower($srvName));
                if($srv == null){
                    $this->log->Warning("Found incosistent app<->server reference ... Deleting it.", ["server" => $srvName, "app" => $appName]);
                    $ss->delSet('ref:app-server:' . $appName, $srvName);
                }else {
                    $srvs[$srvName] = $srv;
                }
            }
            return $srvs;
        }

        return null;

    }

    /**
     * Find one server by it's name
     * @param $name - The name of the server
     * @return server - null in the case it doesn't exists
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  2 - null parameter
     */
    public function find(string $name): ?server
    {

        if ($name == '') {
            throw new wrongFunctionParameterEX('We can\'t save empty names', 2);
        }

        $this->initialize();

        return $this->objManager->find($this->objType, strtolower($name));

    }

    /**
     * @param  server $server - The server object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function save(?server $server = null): int
    {

        if ($server == null) {
            throw new wrongFunctionParameterEX('We can\'t save null ' . $this->objType, 2);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();

        $appsm = appsManager::get_instance();
        $alist = $appsm->getList();

        while ($alist->current() != null) {
            $app = $alist->current()->data;

            $toDelete = $ss->searchSet('ref:app-server:' . $app->getName(), $server->getName() . ':*');

            if ($toDelete != null) {
                foreach ($toDelete as $key => $srv) {
                    $ss->delSet('ref:app-server:' . $app->getName(), $srv);
                }
            }

            $envs = $server->getAssignedEnv($app->getName());

            if ($envs != null) {

                foreach ($envs as $key => $env) {
                    $ss->putSet('ref:app-server:' . $app->getName(), $server->getName() . ':' . $env);
                }
            }
            $alist->next();
        }
        return $this->objManager->save($this->objType, $server->getName(), $server);
    }

    /**
     * @param string $name
     * @return int
     */
    public function delete(string $name): int
    {


        $this->initialize();

        return $this->objManager->delete($this->objType, $name);


    }

}