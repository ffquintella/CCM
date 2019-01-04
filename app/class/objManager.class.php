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
require_once "app.class.php";


/**
 * Class appsManager
 * It manages all the apps records
 *
 * @version 1.0
 *
 * @package gcc\class
 */
class objManager extends singleton
{


    /**
     * @var Secure
     */
    private $sec;

    /**
     * @var log
     */
    private $log;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Return the list of apps
     * @return linkedList
     *
     * @throws wrongFunctionParameterEX - 1 $itemType cannot be null or empty
     */
    public function getList(string $itemType): linkedList
    {

        if ($itemType == null || $itemType == '') {
            throw new wrongFunctionParameterEX('$itemType cannot be null or empty', 1);
        }

        $itemType = strtolower($itemType);

        $this->initialize();

        $la = new linkedList();

        $ss = sharedStorageFactory::getSharedStorage();

        $mlist = $ss->getSet('index:' . $itemType);

        foreach ($mlist as &$key) {
            $val = $ss->get($itemType . ':' . $key);
            $obj = unserialize($this->sec->decrypt($val));

            $la->insertLast($obj);
        }

        return $la;

    }

    private function initialize()
    {
        if (!$this->initialized) {
            $this->sec = new Secure();
            $this->log = logFactory::getLogger();
            $this->initialized = true;
        }
    }

    /**
     * @param  app $app - The application object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      1 - $itemType cannot be null or empty
     *                                      2 - null parameter
     *                                      3 - $name cannot be null or empty
     */
    public function save(string $itemType, string $name, $obj = null): int
    {

        if ($obj == null) {
            throw new wrongFunctionParameterEX('We can\'t save null objects', 2);
        }

        if ($name == null || $name == '') {
            throw new wrongFunctionParameterEX('$name cannot be null or empty', 3);
        }

        if ($itemType == null || $itemType == '') {
            throw new wrongFunctionParameterEX('$itemType cannot be null or empty', 1);
        }

        $itemType = strtolower($itemType);
        $name = strtolower($name);

        $this->initialize();

        $exists = false;

        $sobj = $this->find($itemType, $name);

        if ($sobj != null) $exists = true;

        $ss = sharedStorageFactory::getSharedStorage();

        if ($exists) {
            $this->log->Debug('Updating obj=' . $name);

        } else {
            $this->log->Debug('Creating obj=' . $name);
        }

        $ss->set($itemType . ':' . $name, $this->sec->encrypt(serialize($obj)));
        $ss->putSet("index:" . $itemType, $name);

        return true;

    }

    /**
     * Find one obj by it's name
     * @param $name - The name of the app
     * @return app - null in the case it doesn't exists
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  1 - $itemType cannot be null or empty
     *                                  2 - $name cannot be null or empty
     */
    public function find(string $itemType, string $name)
    {

        if ($name == null || $name == '') {
            throw new wrongFunctionParameterEX('$name cannot be null or empty', 2);
        }
        if ($itemType == null || $itemType == '') {
            throw new wrongFunctionParameterEX('$itemType cannot be null or empty', 1);
        }

        $this->initialize();

        $itemType = strtolower($itemType);
        $name = strtolower($name);

        $ss = sharedStorageFactory::getSharedStorage();

        $val = $ss->get($itemType . ':' . $name);

        if ($val == null) return null;
        else return unserialize($this->sec->decrypt($val));


    }

    /**
     * Deletes the Object on the sharedStorage
     *
     * @param  string $name - The name of the list object
     *
     * @return integer 1 - OK
     *                 -1 - List doesn't exists
     *
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function delete(string $itemType, string $name): int
    {

        if ($itemType == null || $itemType == '') {
            throw new wrongFunctionParameterEX('$itemType cannot be null or empty', 1);
        }

        if ($name == null) {
            throw new wrongFunctionParameterEX('All parameters are mandatory', 2);
        }

        $this->initialize();

        $exists = false;

        $itemType = strtolower($itemType);
        $name = strtolower($name);

        $sobj = $this->find($itemType, $name);

        if ($sobj != null) $exists = true;

        $ss = sharedStorageFactory::getSharedStorage();

        if ($exists) {
            $this->log->Debug('Deleting obj=' . $name);

            $ss->del($itemType . ':' . $name);
            $ss->delSet("index:" . $itemType, $name);
        } else {
            return -1; // list doesn'  exists
        }

        return 1;

    }

}