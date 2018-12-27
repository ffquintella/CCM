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


namespace gcc;


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
class appsManager extends singleton
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

    public static function getInstance(): appsManager
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
        return $this->objManager->getList('app');

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
     * Finds one app by it's key
     * @param string $key
     * @return app|null
     * @throws wrongFunctionParameterEX
     */
    public function findByKey(string $key): ?app
    {

        if ($key == '') {
            throw new wrongFunctionParameterEX('We can\'t search empty names', 2);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();
        $name = $ss->get('ref:key-app:' . md5($key));

        if ($name == null) return null;

        return $this->find($name);

    }

    /**
     * Find one app by it's name
     * @param $name - The name of the app
     * @return app - null in the case it doesn't exists
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                  2 - null parameter
     */
    public function find(string $name): ?app
    {

        if ($name == '') {
            throw new wrongFunctionParameterEX('We can\'t search empty names', 2);
        }

        $this->initialize();

        return $this->objManager->find('app', strtolower($name));

    }

    /**
     * @param  app $app - The application object
     * @return integer 1 - OK
     *                 -1 - Tring to change owner
     *
     * @throws wrongFunctionParameterEX - In the case we used an invalid parameter
     *                                      2 - null parameter
     */
    public function save(?app $app = null): int
    {

        if ($app == null) {
            throw new wrongFunctionParameterEX('We can\'t save null apps', 2);
        }

        $this->initialize();

        $ss = sharedStorageFactory::getSharedStorage();

        $key = $app->getKey();
        $oldKey = $app->getOldKey();

        if ($oldKey != '' && $key != $oldKey) {
            $kf = $ss->get('ref:key-app:' . md5($oldKey));
            if ($kf != null && $kf != '') $ss->del('ref:key-app:' . md5($oldKey));
        }

        $app->setOldKey($key);

        $ss = sharedStorageFactory::getSharedStorage();
        $ss->set('ref:key-app:' . md5($key), $app->getName());


        return $this->objManager->save('app', $app->getName(), $app);


    }

    /**
     * @param string $name
     * @return int
     */
    public function delete(string $name): int
    {


        $this->initialize();

        $app = $this->find($name);

        if ($app == null) {
            throw  new \Exception('Can\'t delete a non existing app', 2);
        }

        $ss = sharedStorageFactory::getSharedStorage();
        $ss->del('ref:key-app:' . md5($app->getKey()));

        $ss->del('ref:app-credential:' . $name);

        return $this->objManager->delete('app', $name);


    }

}