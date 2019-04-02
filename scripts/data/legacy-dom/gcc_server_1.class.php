<?php
/**
THIS SERIES OF CLASSES ARE ONLY TO BE USED IN DATA MIGRATION!!!
 */

namespace gcc;

/**
 * Class server
 * @package gubd/classes/base
 */
class server implements \JsonSerializable
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fqdn;

    /**
     * @var array
     */
    private $assignments = array();
    private $type;

    function __construct(string $name, string $fqdn)
    {
        $this->name = $name;
        $this->fqdn = $fqdn;
    }

    /**
     * @return array
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    /**
     * Verifies if the app is assigned to this server
     *
     * @param string $appName
     * @return bool
     */
    public function isAssigned(string $appName): bool
    {
        foreach ($this->assignments as $app => $environtments) {
            if ($appName == $app) return true;
        }

        return false;
    }

    /***
     * Gets the assigned environemtns array
     * @param string $appName
     * @return array|null
     */
    public function getAssignedEnv(string $appName): ?array
    {
        foreach ($this->assignments as $app => $environtments) {
            if ($appName == $app) return $environtments;
        }

        return null;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    function getName()
    {
        return $this->name;
    }

    function setName(string $name)
    {
        $this->name = $name;
    }

    function getFQDN()
    {
        return $this->fqdn;
    }

    function setFQDN(string $fqdn)
    {
        $this->fqdn = $fqdn;
    }

    function assign(string $appName, string $environment)
    {

        $listm = listsManager::get_instance();
        $list = $listm->find('environments');
        if ($list == null) throw new \Exception('Internal error, List Environments must exist');

        if ($list->find($environment) == null) throw new wrongFunctionParameterEX('This environment doesn\'t exist.');

        $appm = appsManager::get_instance();
        $app = $appm->find($appName);

        if ($app == null) throw new wrongFunctionParameterEX('This app doesn\'t exist.');

        if (!$app->hasEnvironment($environment)) throw new wrongFunctionParameterEX('This app doesn\'t have this environment.');

        $this->assignments[$appName][] = $environment;

    }
    function unassign (string $appName){
        unset($this->assignments[$appName]);
    }

    function unassignEnv (string $appName, string $environment){
        unset($this->assignments[$appName][$environment]);
    }

    function cleanAssignments()
    {
        unset($this->assignments);
        $this->assignments = array();
    }

    public function readData()
    {
        return $this->jsonSerialize();
    }

    function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'fqdn' => $this->fqdn,
            'assignments' => $this->assignments
        ];
    }

} 