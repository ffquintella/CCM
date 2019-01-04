<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 20/03/2018
 * Time: 15:42
 */

namespace ccm;

use ccm\dom\pmpResource;
use ccm\vaultObject;

include_once ROOT . "/baseincludes.php";
require_once "secure.class.php";
require_once "singleton.class.php";
require_once "vaultObject.class.php";

class pmpDataManager extends singleton
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

    private $resourceObjType = "pmpResource";
    private $vaultObjType = "vaultObject";

    /**
     * @return pmpDataManager
     */
    public static function getInstance(): pmpDataManager
    {
        return parent::get_instance();
    }


    private function initialize()
    {
        if (!$this->initialized) {
            $this->log = logFactory::getLogger();
            $this->objManager = objManager::get_instance();
            $this->initialized = true;
        }
    }

    /**
     * Find one server by it's name
     * @param $name - The name of the server
     * @return pmpResource - null in the case it doesn't exists
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  2 - null parameter
     */
    public function findResource(string $name): ?pmpResource
    {
        $this->initialize();

        if ($name == '') {
            $this->log->Error("Tring to call function with empty parameter |pmpDataManager->findResource|");
            throw new wrongFunctionParameterEX('We can\'t find with empty names', 2);
        }


        return $this->objManager->find($this->resourceObjType, $name);

    }

    /**
     * @param  pmpResource $resource - The resource object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function saveResource(?pmpResource $resource = null): int
    {
        $this->initialize();
        if ($resource == null) {
            $this->log->Error("Tring to call function with null parameter |pmpDataManager->saveResource|");
            throw new wrongFunctionParameterEX('We can\'t save null ' . $this->resourceObjType, 2);
        }


        return $this->objManager->save($this->resourceObjType, $resource->name, $resource);
    }

    /**
     * @param  vaultObject $vo - Vault Object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function saveVault(?vaultObject $vo = null): int
    {
        $this->initialize();
        if ($vo == null) {
            $this->log->Error("Tring to call function with null parameter |pmpDataManager->saveVault|");
            throw new wrongFunctionParameterEX('We can\'t save null ' . $this->resourceObjType, 2);
        }


        return $this->objManager->save($this->vaultObjType, $vo->resource, $vo);
    }

    /**
     * Return the list of apps
     * @return linkedList
     */
    public function getVaultList(): linkedList
    {

        $this->initialize();
        return $this->objManager->getList($this->vaultObjType);

    }

}