<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 5/01/17
 * Time: 17:25
 */

namespace gcc;

require_once ROOT . '/data/credentialType.list.php';
require_once ROOT . '/class/corruptDataEX.php';
require_once ROOT . '/class/vaultFactory.class.php';

class credential implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var array
     */
    protected $vaultIds;

    /**
     * @var array
     */
    protected $displayEnvs;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var bool
     */
    protected $displayVaultValues = false;

    /**
     * @param bool $displayVaultValues
     */
    public function setDisplayVaultValues(bool $displayVaultValues)
    {
        $this->displayVaultValues = $displayVaultValues;
    }

    /**
     * Credential constructor.
     * @param string $name
     * @param string $type
     * @param string $appName
     *
     * @throws wrongFunctionParameterEX 1- Type doesn't exists
     *                                  2- appName Can't be null or empty
     *                                  3- $name cannot be empty
     *
     * @throws corruptDataEX 1- App doesn't exists
     *
     */
    function __construct(string $name, string $appName, string $type = 'local')
    {

        if ($name == '') {
            throw new wrongFunctionParameterEX('$name cannot be empty', 3);
        }
        $this->name = $name;


        $typeList = getCredentialTypeList();

        $resp = $typeList->find($type);
        if (!$resp) {
            throw new wrongFunctionParameterEX('Type is not in the allowed list', 1);
        }

        if ($appName == null || $appName == '') throw new wrongFunctionParameterEX('The appName must be first inicialized', 2);


        $this->setAppName($appName);

        $this->type = $type;

    }

    /**
     * @param string $password - The password bein set
     * @return bool - true if ok - false if not validated
     */
    public static function validadePasswordComplexity(string $password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);

        if (!$uppercase || !$lowercase || !$number || strlen($password) < PASS_SIZE) {
            return false;
        } else return true;
    }

    /**
     * @param array $displayEnvs
     */
    public function setDisplayEnvs(array $displayEnvs)
    {
        $this->displayEnvs = $displayEnvs;
    }

    /**
     * @return string
     *
     * @throws wrongFunctionParameterEX - 1- This environment do not exists
     */
    public function getVaultId(string $environment): string
    {
        if (!array_key_exists($environment, $this->vaultIds)) throw new wrongFunctionParameterEX('This environment do not exists', 1);
        return $this->vaultIds[$environment];
    }

    /**
     * @param string $vaultId
     *
     * @throws wrongFunctionParameterEX - 1- Environment can not be empty
     *                                    2- App does not have environment
     *
     * @throws corruptDataEX - 1- App does not exists
     *                         2- App name must be first inicialized
     *
     *
     * @throws invalidOperationEX - 1- Cannot set VaultID on local type credentials
     *
     */
    public function setVaultId(string $environment, string $vaultId)
    {
        if ($this->getType() == 'local') throw new invalidOperationEX('Cannot set value on local type credentials', 1);
        if ($vaultId == '') throw new wrongFunctionParameterEX('Environment can not be empty', 1);

        $appsM = appsManager::get_instance();

        if ($this->appName == null) throw new corruptDataEX('The appName must be first inicialized', 2);

        $app = $appsM->find($this->appName);

        if ($app == null) throw new corruptDataEX('The app pointed by this object does not exists', 1);

        if (!$app->hasEnvironment($environment)) throw new wrongFunctionParameterEX('The environment pointed is not part of the app of this credential', 2);

        $this->vaultIds[$environment] = $vaultId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     *
     * @throws wrongFunctionParameterEX - 1- The environment cannot be null
     *                                    2- Environment not found
     */
    public function getValue(string $environment): ?string
    {
        if ($environment == '') throw new wrongFunctionParameterEX('The environment cannot be null', 1);

        if ($this->displayEnvs != null) {
            if (!in_array($environment, $this->displayEnvs)) return null;
        }

        if ($this->getType() == 'vault') {
            if (!array_key_exists($environment, $this->vaultIds)) throw new wrongFunctionParameterEX('Environment not found', 2);

            $vault = vaultFactory::getVault();

            //$vault = new pmpVault();

            $val = $vault->getPassword($this->vaultIds[$environment]);

            return $val;


        } else {
            if (!array_key_exists($environment, $this->getValues())) throw new wrongFunctionParameterEX('Environment not found', 2);
            return $this->getValues()[$environment];
        }


    }

    /**
     * @return array
     */
    public function getValues(): array
    {

        if ($this->displayEnvs == null) {
            if($this->values != null) return $this->values;
            else return array();
        }
        else {
            $resp = array();
            foreach ($this->values as $key => $value) {
                if (in_array($key, $this->displayEnvs)) {
                    $resp[$key] = $value;
                }
            }
            return $resp;
        }
    }

    /***
     * Clear all values
     */
    public function clearValues(){
        $this->values = array();
    }

    /**
     * @param string $environment
     * @param string $value
     *
     * @throws corruptDataEX - 1- App does not exists
     *                         2- App name must be first inicialized
     *
     * @throws wrongFunctionParameterEX - 1- App does not have environment
     *
     * @throws invalidOperationEX - 1- Cannot set value on vault type credentials
     *
     */
    public function setValue(string $environment, string $value)
    {

        if ($this->getType() == 'vault') throw new invalidOperationEX('Cannot set value on vault type credentials', 1);

        $appsM = appsManager::get_instance();

        if ($this->appName == null || $this->appName == '') throw new corruptDataEX('The appName must be first inicialized', 2);

        $app = $appsM->find($this->appName);

        if ($app == null) throw new corruptDataEX('The app pointed by this object does not exists', 1);

        if (!$app->hasEnvironment($environment)) throw new wrongFunctionParameterEX('The environment pointed is not part of the app of this credential', 1);


        $this->values[$environment] = $value;

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function readData()
    {
        return $this->jsonSerialize();
    }

    function jsonSerialize()
    {
        $ret = [
            'name' => $this->name,
            'app' => $this->getAppName(),
            'type' => $this->type
        ];

        if ($this->type == 'local') {
            $ret['values'] = $this->getValues();
        }
        if ($this->type == 'vault') {
            if($this->displayVaultValues){
                $vvalues = array();
                foreach ($this->vaultIds as $key => $id){
                    if(in_array($key, $this->displayEnvs)){
                        $vvalues[$key] = $this->getValue($key);
                    }
                }
                $ret['values'] = $vvalues;
            }
            $ret['vaultIds'] = $this->vaultIds;
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     *
     * @throws corruptDataEX 1- App doesn't exists
     */
    public function setAppName(string $appName)
    {
        $appsM = appsManager::get_instance();
        $app = $appsM->find(strtolower($appName));

        if ($app == null) throw new corruptDataEX('The app pointed by this object does not exists', 1);

        $this->appName = strtolower($appName);
    }
} 