<?php
/**
 * Created by felipe. for gubd
 * Date: 26/02/14
 * Time: 22:49
 *
 * @author felipe
 *
 * @version 1.0
 */

namespace ccm;


/**
 * Class database
 * @package gubd/classes/base
 */
class app implements \JsonSerializable
{ //extends XML\Element\Base  implements \JsonSerializable {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $owner;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $creationT;

    /**
     * @var linkedList
     */
    private $environments;

    /**
     * @var string
     */
    private $oldKey = '';
    private $avaliable_environments = null;

    /**
     * app constructor.
     */
    public function __construct($name, $creator)
    {
        $this->environments = new linkedList();

        $this->generateKey();
        $this->setName($name);
        $this->setOwner($creator);
        $this->setCreationT();


    }

    public function generateKey()
    {
        $this->key = get_random_string(APP_KEY_SIZE, 1);
    }

    /**
     * @return string
     */
    public function getOldKey(): string
    {
        return $this->oldKey;
    }

    /**
     * @param string $oldKey
     */
    public function setOldKey(string $oldKey)
    {
        $this->oldKey = $oldKey;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $env - A new valid environment (it's a list in the Environments list)
     *
     * @return bool  - true if adds ok
     *               - false data invalid
     *
     * @throws corruptDataEX
     */
    public function addEnvironment(string $env): bool
    {
        if ($this->avaliable_environments == null) {
            $this->reloadAvaliableEnvironments();
        }

        $found = false;
        $this->avaliable_environments->rewind();
        $cont = true;
        while ($cont) {
            if ($this->avaliable_environments->current()->next == null) $cont = false;

            if ($this->avaliable_environments->current()->data == $env) {
                $found = true;
                break;
            }
            $this->avaliable_environments->next();
        }
        //$this->avaliable_environments->current()->next != null

        if ($found) {
            $this->environments->insertLast($env);
            return true;
        } else {
            return false;
        }

    }

    public function cleanEnvironments()
    {
        unset($this->environments);
        $this->environments = new linkedList();
    }

    public function reloadAvaliableEnvironments()
    {

        $lm = listsManager::get_instance();
        $this->avaliable_environments = $lm->find('environments');

        if ($this->avaliable_environments == null) {
            throw new corruptDataEX('We can\'t find the Environments list');
        }

    }

    public function getEnvironments()
    {
        return $this->environments;
    }

    /***
     * @param string $env
     * @return bool
     */
    public function hasEnvironment(string $env): bool
    {
        if ($this->environments->find($env) != null) {
            return true;
        }
        return false;
    }

    public function readData()
    {
        return $this->jsonSerialize();
    }

    function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'owner' => $this->owner,
            'creationT' => $this->getCreationT(),
            'environments' => $this->environments->readList(),
            'key' => $this->getKey()
        ];
    }

    /**
     * @return int
     */
    public function getCreationT(): int
    {
        return $this->creationT;
    }

    /**
     * @param int $creationT
     */
    public function setCreationT(int $creationT = null)
    {
        if ($creationT == null) $creationT = time();
        $this->creationT = $creationT;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }
}