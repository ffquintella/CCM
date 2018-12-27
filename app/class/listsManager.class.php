<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 10:13
 */

namespace gcc;

include_once ROOT . "/baseincludes.php";
//require_once "secure.class.php";
require_once "singleton.class.php";

class listsManager extends singleton
{

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var Secure
     */
    private $sec;

    /**
     * @var log
     */
    private $log;

    /**
     * Return the list of apps
     * @return linkedList
     */
    public function getList(): linkedList
    {

        $this->initialize();

        $list = new linkedList();

        $ss = sharedStorageFactory::getSharedStorage();

        $mlist = $ss->getSet('index:list');

        foreach ($mlist as &$key) {
            $val = $ss->get("list:" . $key);
            $obj = unserialize($this->sec->decrypt($val));

            $list->insertLast(array('name' => $key, 'list' => $obj));
        }

        return $list;

    }

    private function initialize(): void
    {
        if (!$this->initialized) {
            $this->sec = new Secure();
            $this->log = logFactory::getLogger();
            $this->initialized = true;
        }
    }

    /**
     * Saves the list to the sharedStorage
     *
     * @param  string $name - The name of the list object
     * @param  linkedList $list - The list object
     *
     * @return integer 1 - OK
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function save(string $name, linkedList $list): int
    {

        if ($name == null) {
            throw new wrongFunctionParameterEX('All parameters are mandatory', 2);
        }

        $this->initialize();

        $exists = false;

        $slist = $this->find($name);

        if ($slist != null) $exists = true;

        $ss = sharedStorageFactory::getSharedStorage();

        if ($exists) {
            $this->log->Info('Updating list=' . $name);
        } else {
            $this->log->Info('Creating list=' . $name);
        }

        $ss->set("list:" . $name, $this->sec->encrypt(serialize($list)));
        $ss->putSet("index:list", $name);

        return true;

    }

    /**
     * Gets the linked list referenced by the name
     *
     * @param $name - The name of the list being searched
     * @return linkedList|null
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  2 - null parameter
     */
    public function find($name): ?linkedList
    {
        $this->initialize();
        if ($name == null) {
            throw new wrongFunctionParameterEX('We can\'t find using null names', 2);
        }

        $this->log->Debug('Searching list=' . $name);


        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();

        $val = $ss->get('list:' . $name);

        if ($val == null) return null;
        else return unserialize($this->sec->decrypt($val));
    }

    /**
     * Deletes the list on the sharedStorage
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
    public function delete(string $name): int
    {

        if ($name == null) {
            throw new wrongFunctionParameterEX('All parameters are mandatory', 2);
        }

        $this->initialize();

        $exists = false;

        $slist = $this->find($name);

        if ($slist != null) $exists = true;

        $ss = sharedStorageFactory::getSharedStorage();

        if ($exists) {
            $this->log->Info('Deleting list=' . $name);

            $ss->del($name);
            $ss->delSet("index:list", $name);
        } else {
            return -1; // list doesn'  exists
        }

        return true;

    }

}