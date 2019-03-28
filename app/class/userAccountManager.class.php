<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 23:22
 */

namespace ccm;

//include_once ROOT."/data/userAccount.list.php";
include_once ROOT . "/class/singleton.class.php";

class userAccountManager extends singleton
{

    //protected $lua;

    /**
     * @var Secure
     */
    protected $sec;

    /**
     * Return the list of systems
     * @return linkedList
     */
    public function getList()
    {

        if ($this->sec == null) $this->sec = new Secure();

        // if( $this->lua == null) $this->lua = getUserAccountList();
        // return $this->lua;

        $lua = new linkedList();

        $ss = sharedStorageFactory::getSharedStorage();


        $mlist = $ss->getSet('index:user');


        foreach ($mlist as &$key) {
            $val = $ss->get("user:" . $key);
            $obj = unserialize($this->sec->decrypt($val));

            $lua->insertLast($obj);
        }

        return $lua;

    }

    /**
     * @param $name - The account name
     * @param $password - The account password
     * @param $permissions - The account permissions
     * @param $authentication - The local that the user authentication should be donne
     * @return integer 1 - OK
     *                 -1 - Account already exists
     *                 -2 - Password invalid
     */
    public function create($name, $password, $permissions, $authentication = 'local')
    {

        $ru = $this->find($name);

        if ($ru != null) {
            return -1; // Account already exists
        } else {
            if (!account::validadePasswordComplexity($password)) {
                return -2; // password invalid
            } else {

                $ua = new \ccm\userAccount($name, $password, $authentication);

                if ($permissions != null) {
                    foreach ($permissions as $key => $val) {
                        $ua->addPermission(array($key => $val));
                    }
                }

                $ss = sharedStorageFactory::getSharedStorage();

                $ss->set("user:" . $ua->getName(), $this->sec->encrypt(serialize($ua)));

                $ss->putSet("index:user", $ua->getName());

                return 1;
            }
        }

    }

    /**
     * Find one user by it's name
     * @param $name - The name of the system
     * @return userAccount
     */
    public function find($name)
    {

        if ($this->sec == null) $this->sec = new Secure();

        $ss = sharedStorageFactory::getSharedStorage();

        $val = $ss->get('user:' . $name);

        if ($val == null) return null;
        else return unserialize($this->sec->decrypt($val));

    }

    /**
     * @param $name - The account name
     * @param $password - The account password
     * @param $permissions - The account permissions
     * @param $authentication - The local that the user authentication should be donne
     * @return integer 1 - OK
     *                 -1 - Account doesn't exists
     *                 -2 - Password invalid
     */
    public function update($name, $password = null, $permissions = null, $authentication = 'local')
    {

        $ru = $this->find($name);

        if ($ru == null) {
            return -1; // Account doesn't exists
        } else {
            if ($password != null && !account::validadePasswordComplexity($password)) {
                return -2; // password invalid
            } else {

                if ($password != null) $ru->setPassword($password);

                $ru->setUserAuthentication($authentication);

                if ($permissions != null) {
                    $ru->cleanPermissions();
                    foreach ($permissions as $key => $val) {
                        $ru->addPermission(array($key => $val));
                    }
                }

                $ss = sharedStorageFactory::getSharedStorage();

                $ss->replace("user:" . $ru->getName(), $this->sec->encrypt(serialize($ru)));

                //$ss->putSet("index:user",$ru->getName());

                return 1;
            }
        }

    }


    /**
     * @param $name
     * @return int
     *              1 - OK
     *             -1 - User doesn't exists
     */
    public function delete($name)
    {

        $ru = $this->find($name);

        if ($ru == null) {
            return -1; // Account doesn'  exists
        } else {

            $ss = sharedStorageFactory::getSharedStorage();

            $ss->del("user:" . $name);

            $ss->delSet("index:user", $name);

            return 1;

        }

    }
} 