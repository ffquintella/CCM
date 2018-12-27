<?php
/**
 * Created by Felipe Quintella.
 * User: felipe.quintella
 * Date: 6/01/17
 * Time: 14:06
 *
 * @author Felipe F Quintella <felipe.quintella@fgv.br>
 *
 * @since 0.1
 *
 */


namespace gcc;


include_once ROOT . "/baseincludes.php";
require_once "secure.class.php";
require_once "singleton.class.php";
require_once "credential.class.php";


/**
 * Class credentialsManager
 * It manages all the credentials records
 *
 * @version 1.0
 *
 * @package gcc\class
 */
class credentialsManager extends singleton
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
     * @return credentialsManager
     */
    public static function getInstance(): credentialsManager
    {
        return parent::get_instance();
    }

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
            $this->objType = 'credential';
            $this->log = logFactory::getLogger();
            $this->objManager = objManager::get_instance();
            $this->initialized = true;
        }
    }

    /**
     *
     * @param string $appName
     * @return array|null
     * @throws wrongFunctionParameterEX 1- appName is empty
     */
    public function findAllByApp(string $appName): array
    {

        if ($appName == '') {
            throw new wrongFunctionParameterEX('We can\'t search empty names', 1);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();

        $resp = $ss->getSet('ref:app-credential:' . strtolower($appName));
        $resp2 = null;
        foreach ($resp as $key => $value) {
            $resp2[] = $this->find($value);
        }

        if ($resp2 == null) return array();
        else return $resp2;

    }

    /**
     * Find one server by it's name
     * @param $name - The name of the server
     * @return credential - null in the case it doesn't exists
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  2 - null parameter
     */
    public function find(string $name): ?credential
    {

        if ($name == '') {
            throw new wrongFunctionParameterEX('We can\'t save empty names', 2);
        }

        $this->initialize();

        return $this->objManager->find($this->objType, $name);

    }

    /**
     * @param  server $server - The server object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function save(?credential $credential = null): int
    {

        if ($credential == null) {
            throw new wrongFunctionParameterEX('We can\'t save null ' . $this->objType, 2);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();

        $ss->putSet('ref:app-credential:' . $credential->getAppName(), $credential->getName());


        return $this->objManager->save($this->objType, $credential->getName(), $credential);
    }

    /**
     * @param string $name
     * @return int
     *
     * @throws corruptDataEX 1- Credential not found
     *
     * @throws wrongFunctionParameterEX 1- Name empty
     */
    public function delete(string $name): int
    {

        $this->initialize();

        if ($name == '') {
            throw  new wrongFunctionParameterEX('Name cannot be empty', 1);
        }

        $cred = $this->find($name);

        if ($cred == null) {
            throw  new corruptDataEX('Can\'t delete a non existing app', 1);
        }

        $ss = sharedStorageFactory::getSharedStorage();
        $ss->delSet('ref:app-credential:' . $cred->getAppName(), $name);

        return $this->objManager->delete($this->objType, $name);

    }

}