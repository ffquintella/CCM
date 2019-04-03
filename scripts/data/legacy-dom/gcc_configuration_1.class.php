<?php
/**
THIS SERIES OF CLASSES ARE ONLY TO BE USED IN DATA MIGRATION!!!
 */

namespace gcc;

class configuration implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $appName;

    /**
     * @var array
     */
    protected $displayEnvs;


    /**
     * @var bool
     */
    protected $replaceVars = false;

    /**
     * @param bool $replaceVars
     */
    public function setReplaceVars(bool $replaceVars)
    {
        $this->replaceVars = $replaceVars;
    }

    /**
     * @return bool
     */
    public function getReplaceVars(): bool
    {
        return $this->replaceVars;
    }

    /**
     * @var array
     */
    protected $values;

    /**
     * Credential constructor.
     * @param string $name
     * @param string $appName
     *
     * @throws wrongFunctionParameterEX 1- Type doesn't exists
     *                                  2- appName Can't be null or empty
     *                                  3- $name cannot be empty
     *
     * @throws corruptDataEX 1- App doesn't exists
     *
     */
    function __construct(string $name, string $appName)
    {

        if ($name == '') {
            throw new wrongFunctionParameterEX('$name cannot be empty', 3);
        }
        $this->name = $name;
        if ($appName == null || $appName == '') throw new wrongFunctionParameterEX('The appName must be first inicialized', 2);
        $this->setAppName($appName);

    }

    /**
     * @param array $displayEnvs
     */
    public function setDisplayEnvs(array $displayEnvs)
    {
        $this->displayEnvs = $displayEnvs;
    }

    /**
     * @return array|null
     */
    public function getDisplayEnvs(): ?array {
        return $this->displayEnvs;
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

        if (!array_key_exists($environment, $this->getValues())) throw new wrongFunctionParameterEX('Environment not found', 2);
        return $this->getValues()[$environment];

    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        $values = $this->values;

        if($this->replaceVars){
            foreach ($values as $key => $val){
                # Successful match
                if (preg_match_all('/(\${.{1,}?})/', $val, $matches)) {
                    $tmp[$key] = $val;
                    foreach ($matches[0] as $k => $match){
                        $credName = substr($match,2,strlen($match)-3);

                        $credM = credentialsManager::get_instance();

                        $cred = $credM->find($credName);
                        if($cred != null){
                           if($cred->getAppName() == $this->appName){
                               $tmp[$key] = str_replace($match,$cred->getValue($key),$tmp[$key]);
                           }
                        }
                    }

                } else {
                    $tmp[$key] = $val;
                }

            }
            $values = $tmp;
        }

        if ($this->displayEnvs == null) return $values;
        else {
            $resp = array();
            foreach ($values as $key => $value) {
                if (in_array($key, $this->displayEnvs)) {
                    $resp[$key] = $value;
                }
            }
            return $resp;
        }
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


        $appsM = appsManager::get_instance();

        if ($this->appName == null || $this->appName == '') throw new corruptDataEX('The appName must be first inicialized', 2);

        $app = $appsM->find(strtolower($this->appName));

        if ($app == null) throw new corruptDataEX('The app pointed by this object does not exists', 1);

        if (!$app->hasEnvironment($environment)) throw new wrongFunctionParameterEX('The environment pointed is not part of the app of this credential', 1);


        $this->values[$environment] = $value;

    }

    /***
     * Clear all values
     */
    public function clearValues(){
        $this->values = array();
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
            'values' => $this->getValues()
        ];

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

        $this->appName = $appName;
    }
} 